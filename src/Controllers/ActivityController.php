<?php // src/Controllers/ActivityController.php
namespace Openmind\Controllers;

class ActivityController {

    public static function init(): void {
        add_action('wp_ajax_complete_activity', [self::class, 'ajaxCompleteActivity']);
        add_action('wp_ajax_openmind_create_activity', [self::class, 'createActivity']);
        add_action('wp_ajax_openmind_assign_activity', [self::class, 'assignActivity']);

    }

    public static function ajaxCompleteActivity(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('view_activities')) {
            wp_send_json_error('Sin permisos', 403);
        }

        $activity_id = absint($_POST['activity_id'] ?? 0);

        if (!$activity_id) {
            wp_send_json_error('ID inválido');
        }

        $activity = get_post($activity_id);

        if (!$activity || $activity->post_type !== 'activity') {
            wp_send_json_error('Actividad no encontrada');
        }

        // Verificar que sea asignada al usuario actual
        $assigned_to = get_post_meta($activity_id, 'assigned_to', true);
        if ($assigned_to != get_current_user_id()) {
            wp_send_json_error('No puedes completar esta actividad');
        }

        // Marcar como completada
        update_post_meta($activity_id, 'completed', '1');
        update_post_meta($activity_id, 'completed_at', current_time('mysql'));

        wp_send_json_success([
            'message' => '¡Actividad completada! 🎉',
            'activity_id' => $activity_id
        ]);
    }

    public static function createActivity(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_activities')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $title = sanitize_text_field($_POST['title'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');
        $due_date = sanitize_text_field($_POST['due_date'] ?? '');

        if (empty($title)) {
            wp_send_json_error(['message' => 'El título es requerido'], 400);
        }

        $activity_id = wp_insert_post([
            'post_type' => 'activity',
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ]);

        if ($activity_id && $due_date) {
            update_post_meta($activity_id, 'due_date', $due_date);
        }

        wp_send_json_success([
            'message' => 'Actividad creada',
            'activity_id' => $activity_id
        ]);
    }

    public static function assignActivity(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_activities')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $activity_id = intval($_POST['activity_id'] ?? 0);
        $patient_id = intval($_POST['patient_id'] ?? 0);

        if (!$activity_id || !$patient_id) {
            wp_send_json_error(['message' => 'Datos inválidos'], 400);
        }

        $patient = get_userdata($patient_id);
        if (!$patient || !in_array('patient', $patient->roles)) {
            wp_send_json_error(['message' => 'Paciente no válido'], 400);
        }

        update_post_meta($activity_id, 'assigned_to', $patient_id);
        update_post_meta($activity_id, 'completed', 0);

        // Enviar notificación por email
        $activity = get_post($activity_id);
        wp_mail(
            $patient->user_email,
            'Nueva actividad asignada - OpenMind',
            "Hola {$patient->display_name},\n\nSe te ha asignado una nueva actividad: {$activity->post_title}\n\nInicia sesión para verla."
        );

        wp_send_json_success([
            'message' => 'Actividad asignada',
            'activity_id' => $activity_id,
            'patient_id' => $patient_id
        ]);
    }

    public static function getPatientActivities(int $patient_id): array {
        return get_posts([
            'post_type' => 'activity',
            'meta_query' => [
                [
                    'key' => 'assigned_to',
                    'value' => $patient_id,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'meta_key' => 'due_date',
            'order' => 'ASC'
        ]);
    }
}