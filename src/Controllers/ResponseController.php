<?php // src/Controllers/ResponseController.php

namespace Openmind\Controllers;

if (!defined('ABSPATH')) exit;

class ResponseController {

    /**
     * Crear o editar respuesta del paciente (unificado)
     */
    public static function submitResponse(): void {
        check_ajax_referer('submit_activity_response', 'response_nonce');

        $user_id = get_current_user_id();
        if (!current_user_can('patient')) {
            wp_send_json_error('No autorizado');
        }

        $assignment_id = isset($_POST['assignment_id']) ? absint($_POST['assignment_id']) : 0;
        $response_id = isset($_POST['response_id']) ? absint($_POST['response_id']) : 0;
        $content = isset($_POST['response_content']) ? wp_kses_post($_POST['response_content']) : '';

        if (!$assignment_id || !$content) {
            wp_send_json_error('Datos incompletos');
        }

        // Verificar ownership de la actividad
        $patient_id = get_post_meta($assignment_id, 'patient_id', true);
        if ($patient_id != $user_id) {
            wp_send_json_error('Actividad no pertenece al usuario');
        }

        // ==========================================
        // EDITAR respuesta existente
        // ==========================================
        if ($response_id > 0) {
            $comment = get_comment($response_id);
            if (!$comment || $comment->user_id != $user_id) {
                wp_send_json_error('Respuesta no encontrada');
            }

            // Guardar original si es primera edición
            $original = get_comment_meta($response_id, '_original_content', true);
            if (!$original) {
                update_comment_meta($response_id, '_original_content', $comment->comment_content);
            }

            // Guardar historial
            $edit_history = get_comment_meta($response_id, '_edit_history', true) ?: [];
            $edit_history[] = [
                'content' => $comment->comment_content,
                'edited_at' => current_time('mysql'),
                'edited_by' => $user_id
            ];
            update_comment_meta($response_id, '_edit_history', $edit_history);

            // Actualizar contenido
            wp_update_comment([
                'comment_ID' => $response_id,
                'comment_content' => $content
            ]);

            $final_response_id = $response_id;
            $message = 'Respuesta actualizada correctamente';
        }
        // ==========================================
        // CREAR nueva respuesta
        // ==========================================
        else {
            $final_response_id = wp_insert_comment([
                'comment_post_ID' => $assignment_id,
                'comment_type' => 'activity_response',
                'comment_content' => $content,
                'user_id' => $user_id,
                'comment_approved' => 1
            ]);

            if (!$final_response_id) {
                wp_send_json_error('Error al guardar respuesta');
            }

            // Actualizar status en primera respuesta
            $response_count = get_comments([
                'post_id' => $assignment_id,
                'type' => 'activity_response',
                'count' => true
            ]);

            if ($response_count == 1) {
                update_post_meta($assignment_id, 'status', 'completed');
                update_post_meta($assignment_id, 'completed_at', current_time('mysql'));
            }

            $message = 'Respuesta enviada correctamente';
        }

        // ==========================================
        // Procesar archivos adjuntos
        // ==========================================
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
                $existing_files = get_comment_meta($final_response_id, '_response_files', true) ?: [];
                $all_files = array_merge($existing_files, $uploaded_files);
                update_comment_meta($final_response_id, '_response_files', $all_files);
            }
        }

        wp_send_json_success(['message' => $message]);
    }

    /**
     * Ocultar respuesta (soft delete)
     */
    public static function hideResponse(): void {
        check_ajax_referer('submit_activity_response', 'response_nonce');

        $response_id = isset($_POST['response_id']) ? absint($_POST['response_id']) : 0;
        $user_id = get_current_user_id();

        if (!$response_id) {
            wp_send_json_error('ID de respuesta inválido');
        }

        $comment = get_comment($response_id);

        if (!$comment) {
            wp_send_json_error('Respuesta no encontrada');
        }

        // Verificar ownership
        if ($comment->user_id != $user_id) {
            wp_send_json_error('No tienes permisos para ocultar esta respuesta');
        }

        // Actualizar estado a 'hidden'
        $updated = wp_update_comment([
            'comment_ID' => $response_id,
            'comment_approved' => 'hidden'
        ]);

        if (!$updated) {
            wp_send_json_error('Error al ocultar respuesta');
        }

        // Guardar metadata
        update_comment_meta($response_id, '_hidden_by', $user_id);
        update_comment_meta($response_id, '_hidden_at', current_time('mysql'));

        wp_send_json_success([
            'message' => 'Respuesta ocultada correctamente. Solo tu psicólogo puede verla.'
        ]);
    }

    /**
     * Editar respuesta (ilimitado con trace)
     */
    public static function editResponse(): void {
        self::submitResponse();
    }

    /**
     * Eliminar respuesta (solo para compatibilidad - redirige a hide)
     */
    public static function deleteResponse(): void {
        self::hideResponse();
    }
}