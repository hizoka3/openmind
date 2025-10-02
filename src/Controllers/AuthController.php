<?php // src/Controllers/AuthController.php
namespace Openmind\Controllers;

class AuthController {

    public static function init(): void {
        add_action('wp_ajax_openmind_change_password', [self::class, 'changePassword']);
        add_action('wp_ajax_openmind_update_profile', [self::class, 'updateProfile']);
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

        // Enviar email de confirmación
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