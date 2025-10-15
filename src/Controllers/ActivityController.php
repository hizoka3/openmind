<?php // src/Controllers/ActivityController.php
namespace Openmind\Controllers;

class ActivityController {

    public static function init(): void {
        // Psicólogo - Asignaciones
        add_action('wp_ajax_openmind_assign_activity', [self::class, 'assignActivity']);
        add_action('wp_ajax_openmind_update_assignment', [self::class, 'updateAssignment']);

        // Paciente - Completar
        add_action('wp_ajax_openmind_complete_activity', [self::class, 'completeActivity']);
        add_action('wp_ajax_openmind_add_activity_response', [self::class, 'addResponse']);
    }

    // ==========================================
    // PSICÓLOGO - ASIGNACIONES
    // ==========================================

    public static function assignActivity(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $library_id = absint($_POST['library_activity_id'] ?? 0);
        $patient_id = absint($_POST['patient_id'] ?? 0);
        $custom_title = sanitize_text_field($_POST['custom_title'] ?? '');
        $custom_description = wp_kses_post($_POST['custom_description'] ?? '');
        $due_date = sanitize_text_field($_POST['due_date'] ?? '');

        if (!$library_id || !$patient_id) {
            wp_send_json_error(['message' => 'Datos inválidos'], 400);
        }

        $library_activity = get_post($library_id);
        if (!$library_activity || $library_activity->post_type !== 'activity') {
            wp_send_json_error(['message' => 'Actividad de biblioteca no encontrada'], 404);
        }

        $patient = get_userdata($patient_id);
        if (!$patient || !in_array('patient', $patient->roles)) {
            wp_send_json_error(['message' => 'Paciente no válido'], 400);
        }

        $assignment_id = wp_insert_post([
            'post_type' => 'activity_assignment',
            'post_parent' => $library_id,
            'post_title' => $custom_title ?: $library_activity->post_title,
            'post_content' => $custom_description,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ]);

        if ($assignment_id) {
            update_post_meta($assignment_id, 'patient_id', $patient_id);
            update_post_meta($assignment_id, 'psychologist_id', get_current_user_id());
            update_post_meta($assignment_id, 'status', 'pending');
            update_post_meta($assignment_id, 'response_count', 0);

            if ($due_date) {
                update_post_meta($assignment_id, 'due_date', $due_date);
            }

            // Email notificación
            wp_mail(
                $patient->user_email,
                'Nueva actividad asignada - OpenMind',
                "Hola {$patient->display_name},\n\nSe te ha asignado una nueva actividad.\n\nInicia sesión para verla."
            );

            wp_send_json_success([
                'message' => 'Actividad asignada',
                'assignment_id' => $assignment_id
            ]);
        }

        wp_send_json_error(['message' => 'Error al asignar'], 500);
    }

    public static function updateAssignment(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $assignment_id = absint($_POST['assignment_id'] ?? 0);
        $custom_title = sanitize_text_field($_POST['custom_title'] ?? '');
        $custom_description = wp_kses_post($_POST['custom_description'] ?? '');
        $due_date = sanitize_text_field($_POST['due_date'] ?? '');

        if (!$assignment_id) {
            wp_send_json_error(['message' => 'ID inválido'], 400);
        }

        wp_update_post([
            'ID' => $assignment_id,
            'post_title' => $custom_title,
            'post_content' => $custom_description
        ]);

        if ($due_date) {
            update_post_meta($assignment_id, 'due_date', $due_date);
        } else {
            delete_post_meta($assignment_id, 'due_date');
        }

        wp_send_json_success(['message' => 'Asignación actualizada']);
    }

    // ==========================================
    // PACIENTE - COMPLETAR Y RESPONDER
    // ==========================================

    public static function completeActivity(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('view_activities')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $assignment_id = absint($_POST['assignment_id'] ?? 0);

        if (!$assignment_id) {
            wp_send_json_error(['message' => 'ID inválido'], 400);
        }

        $assignment = get_post($assignment_id);
        if (!$assignment || $assignment->post_type !== 'activity_assignment') {
            wp_send_json_error(['message' => 'Asignación no encontrada'], 404);
        }

        $patient_id = get_post_meta($assignment_id, 'patient_id', true);
        if ($patient_id != get_current_user_id()) {
            wp_send_json_error(['message' => 'No autorizado'], 403);
        }

        update_post_meta($assignment_id, 'status', 'completed');
        update_post_meta($assignment_id, 'completed_at', current_time('mysql'));

        wp_send_json_success(['message' => '¡Actividad completada! 🎉']);
    }

    public static function addResponse(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('view_activities')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $assignment_id = absint($_POST['assignment_id'] ?? 0);
        $response_content = wp_kses_post($_POST['response_content'] ?? '');

        if (!$assignment_id || empty($response_content)) {
            wp_send_json_error(['message' => 'Datos inválidos'], 400);
        }

        $assignment = get_post($assignment_id);
        if (!$assignment || $assignment->post_type !== 'activity_assignment') {
            wp_send_json_error(['message' => 'Asignación no encontrada'], 404);
        }

        $patient_id = get_post_meta($assignment_id, 'patient_id', true);
        if ($patient_id != get_current_user_id()) {
            wp_send_json_error(['message' => 'No autorizado'], 403);
        }

        $comment_id = wp_insert_comment([
            'comment_post_ID' => $assignment_id,
            'comment_content' => $response_content,
            'comment_type' => 'activity_response',
            'user_id' => get_current_user_id(),
            'comment_approved' => 1
        ]);

        if ($comment_id) {
            $count = (int) get_post_meta($assignment_id, 'response_count', true);
            update_post_meta($assignment_id, 'response_count', $count + 1);

            wp_send_json_success([
                'message' => 'Respuesta agregada',
                'comment_id' => $comment_id
            ]);
        }

        wp_send_json_error(['message' => 'Error al guardar respuesta'], 500);
    }

    // ==========================================
    // HELPERS PÚBLICOS
    // ==========================================

    public static function getLibraryActivities(): array {
        return get_posts([
            'post_type' => 'activity',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
    }

    public static function getPatientAssignments(int $patient_id): array {
        return get_posts([
            'post_type' => 'activity_assignment',
            'meta_query' => [
                ['key' => 'patient_id', 'value' => $patient_id]
            ],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
    }

    public static function getAssignmentResponses(int $assignment_id): array {
        return get_comments([
            'post_id' => $assignment_id,
            'type' => 'activity_response',
            'status' => 'approve',
            'orderby' => 'comment_date',
            'order' => 'ASC'
        ]);
    }

    /**
     * ✅ ACTUALIZADO - Retorna array de recursos
     */
    public static function getActivityData(int $assignment_id): ?array {
        $assignment = get_post($assignment_id);
        if (!$assignment || $assignment->post_type !== 'activity_assignment') {
            return null;
        }

        $library_id = $assignment->post_parent;
        $library = get_post($library_id);

        // ✅ NUEVO - Obtener múltiples recursos
        $resources = get_post_meta($library_id, '_activity_resources', true) ?: [];

        // Fallback para actividades antiguas no migradas
        if (empty($resources)) {
            $old_type = get_post_meta($library_id, '_activity_type', true);
            if ($old_type) {
                $resources = [[
                    'type' => $old_type,
                    'file_id' => get_post_meta($library_id, '_activity_file', true) ?: '',
                    'url' => get_post_meta($library_id, '_activity_url', true) ?: '',
                    'title' => '',
                    'order' => 0
                ]];
            }
        }

        return [
            'assignment' => $assignment,
            'library' => $library,
            'resources' => $resources, // ✅ NUEVO - Array de recursos
            'patient_id' => get_post_meta($assignment_id, 'patient_id', true),
            'psychologist_id' => get_post_meta($assignment_id, 'psychologist_id', true),
            'status' => get_post_meta($assignment_id, 'status', true),
            'due_date' => get_post_meta($assignment_id, 'due_date', true),
            'completed_at' => get_post_meta($assignment_id, 'completed_at', true),
            'response_count' => get_post_meta($assignment_id, 'response_count', true)
        ];
    }

    /**
     * Enviar/actualizar respuesta de actividad
     */
    public static function submitResponse() {
        check_ajax_referer('submit_activity_response', 'response_nonce');

        $user_id = get_current_user_id();
        if (!current_user_can('patient')) {
            wp_send_json_error(['message' => 'No autorizado']);
        }

        $assignment_id = absint($_POST['assignment_id']);
        $response_id = absint($_POST['response_id']);
        $content = wp_kses_post($_POST['response_content']);

        $patient_id = get_post_meta($assignment_id, 'patient_id', true);
        if ($patient_id != $user_id) {
            wp_send_json_error(['message' => 'Actividad no pertenece al usuario']);
        }

        if ($response_id > 0) {
            $comment = get_comment($response_id);
            if (!$comment || $comment->user_id != $user_id) {
                wp_send_json_error(['message' => 'Respuesta no encontrada']);
            }

            wp_update_comment([
                'comment_ID' => $response_id,
                'comment_content' => $content
            ]);

            $final_response_id = $response_id;
            $action_type = 'updated';
        } else {
            $final_response_id = wp_insert_comment([
                'comment_post_ID' => $assignment_id,
                'comment_type' => 'activity_response',
                'comment_content' => $content,
                'user_id' => $user_id,
                'comment_approved' => 1
            ]);

            if (!$final_response_id) {
                wp_send_json_error(['message' => 'Error al guardar respuesta']);
            }

            $action_type = 'created';

            $response_count = get_comments([
                'post_id' => $assignment_id,
                'type' => 'activity_response',
                'count' => true
            ]);

            if ($response_count == 1) {
                update_post_meta($assignment_id, 'status', 'completed');
                update_post_meta($assignment_id, 'completed_at', current_time('mysql'));
            }
        }

        // Procesar archivos
        $uploaded_files = [];
        if (!empty($_FILES['response_files']['name'][0])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $files_count = count($_FILES['response_files']['name']);
            $files_count = min($files_count, 5);

            for ($i = 0; $i < $files_count; $i++) {
                if ($_FILES['response_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['response_files']['name'][$i],
                        'type' => $_FILES['response_files']['type'][$i],
                        'tmp_name' => $_FILES['response_files']['tmp_name'][$i],
                        'error' => $_FILES['response_files']['error'][$i],
                        'size' => $_FILES['response_files']['size'][$i]
                    ];

                    $upload = wp_handle_upload($file, ['test_form' => false]);

                    if (!isset($upload['error'])) {
                        $attachment_id = wp_insert_attachment([
                            'post_mime_type' => $upload['type'],
                            'post_title' => sanitize_file_name($file['name']),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        ], $upload['file']);

                        wp_update_attachment_metadata(
                            $attachment_id,
                            wp_generate_attachment_metadata($attachment_id, $upload['file'])
                        );

                        $uploaded_files[] = $attachment_id;
                    }
                }
            }

            if (!empty($uploaded_files)) {
                if ($response_id > 0) {
                    $existing_files = get_comment_meta($response_id, '_response_files', true) ?: [];
                    $uploaded_files = array_merge($existing_files, $uploaded_files);
                }
                update_comment_meta($final_response_id, '_response_files', $uploaded_files);
            }
        }

        wp_send_json_success([
            'message' => $action_type === 'created' ? 'Respuesta enviada' : 'Respuesta actualizada',
            'response_id' => $final_response_id,
            'action' => $action_type
        ]);
    }

    /**
     * Eliminar respuesta
     */
    public static function deleteResponse() {
        check_ajax_referer('delete_activity_response', 'nonce');

        $user_id = get_current_user_id();
        $response_id = absint($_POST['response_id']);

        $comment = get_comment($response_id);
        if (!$comment || $comment->user_id != $user_id) {
            wp_send_json_error(['message' => 'Respuesta no encontrada']);
        }

        $files = get_comment_meta($response_id, '_response_files', true);
        if ($files && is_array($files)) {
            foreach ($files as $file_id) {
                wp_delete_attachment($file_id, true);
            }
        }

        wp_delete_comment($response_id, true);

        wp_send_json_success(['message' => 'Respuesta eliminada']);
    }

    /**
     * Respuesta del psicólogo a una actividad
     */
    public static function psychologistResponse() {
        global $wpdb;

        check_ajax_referer('submit_activity_response', 'response_nonce');

        $user_id = get_current_user_id();
        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'No autorizado']);
        }

        $assignment_id = isset($_POST['assignment_id']) ? absint($_POST['assignment_id']) : 0;
        $patient_id = isset($_POST['patient_id']) ? absint($_POST['patient_id']) : 0;
        $content = isset($_POST['psychologist_response']) ? wp_kses_post($_POST['psychologist_response']) : '';

        if (!$assignment_id || !$patient_id) {
            wp_send_json_error('Datos incompletos');
        }

        if (empty(trim(strip_tags($content)))) {
            wp_send_json_error('El comentario no puede estar vacío');
        }

        $psychologist_id = get_post_meta($assignment_id, 'psychologist_id', true);
        if ($psychologist_id != $user_id) {
            wp_send_json_error('No autorizado');
        }

        $user = get_userdata($user_id);

        $comment_id = wp_insert_comment([
            'comment_post_ID' => $assignment_id,
            'comment_type' => 'psy_response',
            'comment_content' => $content,
            'user_id' => $user_id,
            'comment_author' => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_author_url' => '',
            'comment_approved' => 1,
            'comment_date' => current_time('mysql'),
            'comment_date_gmt' => current_time('mysql', 1)
        ]);

        if (!$comment_id) {
            error_log('ERROR wp_insert_comment - Last DB Error: ' . $wpdb->last_error);
            wp_send_json_error('Error al guardar comentario');
        }

        if (!empty($_FILES['response_files']['name'][0])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $uploaded_files = [];
            $files = $_FILES['response_files'];

            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = [
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    ];
                    $_FILES = ['upload_file' => $file];
                    $attachment_id = media_handle_upload('upload_file', 0);

                    if (!is_wp_error($attachment_id)) {
                        $uploaded_files[] = $attachment_id;
                    }
                }
            }

            if (!empty($uploaded_files)) {
                update_comment_meta($comment_id, '_response_files', $uploaded_files);
            }
        }

        wp_send_json_success([
            'message' => 'Comentario enviado correctamente',
            'comment_id' => $comment_id
        ]);
    }
}