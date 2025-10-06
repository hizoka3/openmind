<?php
// src/Controllers/AuthController.php
namespace Openmind\Controllers;

class AuthController {

    public static function init(): void {
        // Endpoints públicos (sin autenticación)
        add_action('wp_ajax_nopriv_openmind_login', [self::class, 'login']);
        add_action('wp_ajax_nopriv_openmind_register', [self::class, 'register']);
        add_action('wp_ajax_nopriv_openmind_forgot_password', [self::class, 'forgotPassword']);

        // Endpoints privados (requieren autenticación)
        add_action('wp_ajax_openmind_change_password', [self::class, 'changePassword']);
        add_action('wp_ajax_openmind_update_profile', [self::class, 'updateProfile']);
    }

    public static function login(): void {
        check_ajax_referer('openmind_auth', 'nonce');

        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            wp_send_json_error(['message' => 'Email y contraseña son requeridos'], 400);
        }

        // Buscar usuario por email
        $user = get_user_by('email', $email);

        if (!$user) {
            wp_send_json_error(['message' => 'Credenciales incorrectas'], 401);
        }

        // Verificar contraseña
        if (!wp_check_password($password, $user->user_pass, $user->ID)) {
            wp_send_json_error(['message' => 'Credenciales incorrectas'], 401);
        }

        // Login exitoso
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);

        // Determinar redirect según rol
        $redirect_url = in_array('psychologist', $user->roles)
            ? home_url('/dashboard-psicologo/')
            : home_url('/dashboard-paciente/');

        wp_send_json_success([
            'message' => '¡Bienvenido!',
            'redirect_url' => $redirect_url,
            'user' => [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'role' => $user->roles[0]
            ]
        ]);
    }

    public static function register(): void {
        check_ajax_referer('openmind_auth', 'nonce');

        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validaciones
        if (empty($name) || empty($email) || empty($password)) {
            wp_send_json_error(['message' => 'Todos los campos son requeridos'], 400);
        }

        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Email inválido'], 400);
        }

        if (strlen($password) < 8) {
            wp_send_json_error(['message' => 'La contraseña debe tener al menos 8 caracteres'], 400);
        }

        // Verificar si el email ya existe
        if (email_exists($email)) {
            wp_send_json_error(['message' => 'Este email ya está registrado'], 400);
        }

        // Generar username único
        $username = sanitize_user(strtolower(str_replace(' ', '', $name)));
        if (username_exists($username)) {
            $username .= '_' . wp_rand(100, 999);
        }

        // Crear usuario paciente
        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'display_name' => $name,
            'role' => 'patient',
            'show_admin_bar_front' => false
        ]);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()], 500);
        }

        // Login automático después del registro
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);

        // Email de bienvenida
        wp_mail(
            $email,
            'Bienvenido a OpenMind',
            sprintf(
                "¡Hola %s!\n\nTu cuenta ha sido creada exitosamente.\n\nPuedes acceder a tu panel en: %s\n\n¡Bienvenido a OpenMind!",
                $name,
                home_url('/dashboard-paciente/')
            )
        );

        wp_send_json_success([
            'message' => '¡Cuenta creada exitosamente!',
            'redirect_url' => home_url('/dashboard-paciente/'),
            'user' => [
                'id' => $user_id,
                'name' => $name,
                'email' => $email,
                'role' => 'patient'
            ]
        ]);
    }

    public static function changePassword(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $user_id = get_current_user_id();
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            wp_send_json_error(['message' => 'Todos los campos son requeridos'], 400);
        }

        if (strlen($new_password) < 8) {
            wp_send_json_error(['message' => 'La contraseña debe tener al menos 8 caracteres'], 400);
        }

        // Verificar contraseña actual
        $user = get_userdata($user_id);
        if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
            wp_send_json_error(['message' => 'La contraseña actual es incorrecta'], 401);
        }

        // Cambiar contraseña
        wp_set_password($new_password, $user_id);

        // Email de confirmación
        wp_mail(
            $user->user_email,
            'Contraseña cambiada - OpenMind',
            "Tu contraseña ha sido cambiada exitosamente.\n\nSi no realizaste este cambio, contacta al administrador inmediatamente."
        );

        wp_send_json_success(['message' => 'Contraseña cambiada exitosamente']);
    }

    public static function updateProfile(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $user_id = get_current_user_id();
        $display_name = sanitize_text_field($_POST['display_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');

        if (empty($display_name) || empty($email)) {
            wp_send_json_error(['message' => 'Nombre y email son requeridos'], 400);
        }

        if (!is_email($email)) {
            wp_send_json_error(['message' => 'Email inválido'], 400);
        }

        // Verificar que el email no esté en uso por otro usuario
        $email_exists = email_exists($email);
        if ($email_exists && $email_exists != $user_id) {
            wp_send_json_error(['message' => 'El email ya está en uso'], 400);
        }

        $updated = wp_update_user([
            'ID' => $user_id,
            'display_name' => $display_name,
            'user_email' => $email
        ]);

        if (is_wp_error($updated)) {
            wp_send_json_error(['message' => $updated->get_error_message()], 500);
        }

        wp_send_json_success(['message' => 'Perfil actualizado exitosamente']);
    }
}