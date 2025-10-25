<?php // src/Controllers/PatientController.php
namespace Openmind\Controllers;

use Openmind\Core\AccessControl;

class PatientController {

    public static function init(): void {
        add_action('wp_ajax_openmind_add_patient', [self::class, 'addPatient']);
        add_action('wp_ajax_openmind_remove_patient', [self::class, 'removePatient']);
        add_action('wp_ajax_openmind_get_patient_info', [self::class, 'getPatientInfo']);
        add_action('wp_ajax_openmind_get_patients', [self::class, 'getPatients']);
        add_action('wp_ajax_openmind_assign_patient', [self::class, 'assignPatient']);
    }

    public static function assignPatient(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error('Sin permisos', 403);
        }

        $psychologist_id = get_current_user_id();
        $patient_email = sanitize_email($_POST['patient_email'] ?? '');

        if (empty($patient_email) || !is_email($patient_email)) {
            wp_send_json_error('Email inválido');
        }

        // Buscar paciente
        $patient = get_user_by('email', $patient_email);

        if (!$patient) {
            wp_send_json_error('El paciente no existe en el sistema');
        }

        // Verificar que sea paciente
        if (!in_array('patient', $patient->roles)) {
            wp_send_json_error('El usuario no es un paciente');
        }

        // Verificar que NO tenga psicólogo asignado
        $current_psych = get_user_meta($patient->ID, 'psychologist_id', true);
        if ($current_psych) {
            wp_send_json_error('El paciente ya tiene un psicólogo asignado');
        }

        // Verificar que esté inactivo
        $status = get_user_meta($patient->ID, 'openmind_status', true);
        if ($status === 'active') {
            wp_send_json_error('El paciente ya está activo');
        }

        // Asignar psicólogo
        global $wpdb;
        $inserted = $wpdb->replace(
            $wpdb->prefix . 'openmind_relationships',
            [
                'psychologist_id' => $psychologist_id,
                'patient_id' => $patient->ID
            ],
            ['%d', '%d']
        );

        if ($inserted === false) {
            wp_send_json_error('Error al asignar paciente');
        }

        update_user_meta($patient->ID, 'psychologist_id', $psychologist_id);

        // Activar automáticamente
        AccessControl::activatePatient($patient->ID);

        $wpdb->insert(
            $wpdb->prefix . 'openmind_messages',
            [
                'sender_id' => $psychologist_id,
                'receiver_id' => $patient->ID,
                'message' => '¡Hola! Bienvenido/a a OpenMind. Estaré acompañándote en este proceso. Puedes escribirme cuando lo necesites.',
                'is_read' => 0,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%d', '%s']
        );

        // Obtener datos del psicólogo para los emails
        $psychologist = get_userdata($psychologist_id);

        // Email al administrador
        $admin_email = get_option('admin_email');
        wp_mail(
            $admin_email,
            'Paciente asignado - OpenMind',
            sprintf(
                "Asignación realizada en OpenMind\n\n" .
                "Psicólogo: %s (%s)\n" .
                "Paciente: %s (%s)\n" .
                "Fecha: %s\n\n" .
                "El paciente ha sido activado automáticamente.",
                $psychologist->display_name,
                $psychologist->user_email,
                $patient->display_name,
                $patient->user_email,
                current_time('d/m/Y H:i')
            )
        );

        // Email al paciente
        wp_mail(
            $patient->user_email,
            'Tu psicólogo habilitó tu espacio OpenMind',
            sprintf(
                "Hola %s,\n\n" .
                "Tu psicólogo %s habilitó tu espacio OpenMind.\n\n" .
                "Ya puedes acceder a tu panel y comenzar tu proceso:\n%s\n\n" .
                "¡Bienvenido a OpenMind!",
                $patient->display_name,
                $psychologist->display_name,
                home_url('/dashboard-paciente/')
            )
        );

        wp_send_json_success([
            'message' => 'Paciente asignado y activado correctamente',
            'patient' => [
                'id' => $patient->ID,
                'name' => $patient->display_name,
                'email' => $patient->user_email
            ]
        ]);
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
                'avatar' => get_avatar_url($patient->ID, ['size' => 40]),
                'status' => get_user_meta($patient->ID, 'openmind_status', true) ?: 'inactive'
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

            // Activar automáticamente al asignar
            AccessControl::activatePatient($patient_id);

            wp_send_json_success([
                'message' => 'Paciente agregado y activado',
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

    public static function filterPatients(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error('No tienes permisos');
        }

        $user_id = get_current_user_id();
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $status_filter = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';

        // Query filtrada
        $args = [
            'role' => 'patient',
            'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
            ]
        ];

        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = ['display_name', 'user_email'];
        }

        if ($status_filter !== 'all') {
            $args['meta_query'][] = [
                'key' => 'openmind_status',
                'value' => $status_filter,
                'compare' => '='
            ];
        }

        $patients = get_users($args);

        // Contar todos los pacientes por estado (sin filtros de búsqueda/estado)
        $all_patients = get_users([
            'role' => 'patient',
            'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
            ],
            'fields' => 'ID'
        ]);

        $active_count = 0;
        $inactive_count = 0;

        foreach ($all_patients as $patient_id) {
            $status = get_user_meta($patient_id, 'openmind_status', true);
            if ($status === 'active') {
                $active_count++;
            } else {
                $inactive_count++;
            }
        }

        $total_count = count($all_patients);

        // Generar texto del contador
        $count_text = $total_count . ' paciente' . ($total_count !== 1 ? 's' : '');
        $count_text .= ' <span class="text-sm">';
        $count_text .= '(<span class="text-green-600 font-medium">' . $active_count . ' activo' . ($active_count !== 1 ? 's' : '') . '</span>, ';
        $count_text .= '<span class="text-yellow-600 font-medium">' . $inactive_count . ' inactivo' . ($inactive_count !== 1 ? 's' : '') . '</span>)';
        $count_text .= '</span>';

        // Capturar HTML de la tabla
        ob_start();
        include OPENMIND_PATH . 'templates/components/patients-table.php';
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'count' => count($patients),
            'count_text' => $count_text
        ]);
    }
}