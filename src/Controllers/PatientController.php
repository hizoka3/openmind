<?php // src/Controllers/PatientController.php
namespace Openmind\Controllers;

class PatientController {

    public static function init(): void {
        add_action('wp_ajax_openmind_add_patient', [self::class, 'addPatient']);
        add_action('wp_ajax_openmind_remove_patient', [self::class, 'removePatient']);
        add_action('wp_ajax_openmind_get_patient_info', [self::class, 'getPatientInfo']);
        add_action('wp_ajax_openmind_get_patients', [self::class, 'getPatients']);
    }

    public static function getPatients(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $psychologist_id = get_current_user_id();

        $patients = get_users([
            'role' => 'patient',
            'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $psychologist_id, 'compare' => '=']
            ]
        ]);

        $patients_data = array_map(function($patient) {
            return [
                'ID' => $patient->ID,
                'display_name' => $patient->display_name,
                'user_email' => $patient->user_email,
                'avatar' => get_avatar_url($patient->ID, ['size' => 40])
            ];
        }, $patients);

        wp_send_json_success(['patients' => $patients_data]);
    }

    public static function addPatient(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $psychologist_id = get_current_user_id();
        $patient_email = sanitize_email($_POST['patient_email'] ?? '');

        if (empty($patient_email) || !is_email($patient_email)) {
            wp_send_json_error(['message' => 'Email inválido'], 400);
        }

        // Buscar o crear paciente
        $patient = get_user_by('email', $patient_email);

        if (!$patient) {
            // Crear nuevo usuario paciente
            $password = wp_generate_password(12, true, true);
            $username = sanitize_user(explode('@', $patient_email)[0] . '_' . wp_rand(100, 999));

            $patient_id = wp_insert_user([
                'user_email' => $patient_email,
                'user_login' => $username,
                'user_pass' => $password,
                'role' => 'patient',
                'display_name' => explode('@', $patient_email)[0]
            ]);

            if (is_wp_error($patient_id)) {
                wp_send_json_error(['message' => $patient_id->get_error_message()], 400);
            }

            // Enviar email con credenciales
            $message = sprintf(
                "Bienvenido a OpenMind\n\n" .
                "Tu cuenta ha sido creada.\n" .
                "Usuario: %s\n" .
                "Contraseña: %s\n\n" .
                "Por favor, cambia tu contraseña al iniciar sesión.\n" .
                "Accede en: %s",
                $username,
                $password,
                home_url('/dashboard-paciente')
            );

            wp_mail($patient_email, 'Bienvenido a OpenMind', $message);

            $patient = get_userdata($patient_id);
        } else {
            // Verificar que sea paciente
            if (!in_array('patient', $patient->roles)) {
                wp_send_json_error(['message' => 'El usuario no es un paciente'], 400);
            }

            // Verificar que no esté ya asignado
            $current_psych = get_user_meta($patient->ID, 'psychologist_id', true);
            if ($current_psych && $current_psych != $psychologist_id) {
                wp_send_json_error(['message' => 'El paciente ya tiene un psicólogo asignado'], 400);
            }

            $patient_id = $patient->ID;
        }

        // Crear relación
        global $wpdb;
        $inserted = $wpdb->replace(
            $wpdb->prefix . 'openmind_relationships',
            [
                'psychologist_id' => $psychologist_id,
                'patient_id' => $patient_id
            ],
            ['%d', '%d']
        );

        if ($inserted !== false) {
            update_user_meta($patient_id, 'psychologist_id', $psychologist_id);

            wp_send_json_success([
                'message' => 'Paciente agregado',
                'patient' => [
                    'id' => $patient->ID,
                    'name' => $patient->display_name,
                    'email' => $patient->user_email
                ]
            ]);
        }

        wp_send_json_error(['message' => 'Error al agregar paciente'], 500);
    }

    public static function removePatient(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $psychologist_id = get_current_user_id();
        $patient_id = intval($_POST['patient_id'] ?? 0);

        global $wpdb;
        $deleted = $wpdb->delete(
            $wpdb->prefix . 'openmind_relationships',
            [
                'psychologist_id' => $psychologist_id,
                'patient_id' => $patient_id
            ],
            ['%d', '%d']
        );

        if ($deleted) {
            delete_user_meta($patient_id, 'psychologist_id');
            wp_send_json_success(['message' => 'Paciente removido']);
        }

        wp_send_json_error(['message' => 'Error al remover paciente'], 500);
    }

    public static function getPatientInfo(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $patient_id = intval($_POST['patient_id'] ?? 0);
        $patient = get_userdata($patient_id);

        if (!$patient) {
            wp_send_json_error(['message' => 'Paciente no encontrado'], 404);
        }

        $activities = get_posts([
            'post_type' => 'activity',
            'meta_key' => 'assigned_to',
            'meta_value' => $patient_id,
            'posts_per_page' => -1
        ]);

        $completed = array_filter($activities, fn($a) => get_post_meta($a->ID, 'completed', true) == 1);

        wp_send_json_success([
            'patient' => [
                'id' => $patient->ID,
                'name' => $patient->display_name,
                'email' => $patient->user_email,
                'registered' => $patient->user_registered
            ],
            'stats' => [
                'total_activities' => count($activities),
                'completed' => count($completed),
                'pending' => count($activities) - count($completed)
            ]
        ]);
    }
}