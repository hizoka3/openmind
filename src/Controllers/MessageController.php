<?php // src/Controllers/MessageController.php
namespace Openmind\Controllers;

use Openmind\Repositories\MessageRepository;

class MessageController {

    public static function init(): void {
        add_action('wp_ajax_openmind_send_message', [self::class, 'sendMessage']);
        add_action('wp_ajax_openmind_get_messages', [self::class, 'getMessages']);
        add_action('wp_ajax_openmind_mark_read', [self::class, 'markAsRead']);
    }

    public static function sendMessage(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $sender_id = get_current_user_id();
        $receiver_id = intval($_POST['receiver_id'] ?? 0);
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if (empty($message) || !$receiver_id) {
            wp_send_json_error(['message' => 'Datos inválidos'], 400);
        }

        // Verificar que el receptor existe y tiene relación con el sender
        if (!self::hasRelationship($sender_id, $receiver_id)) {
            wp_send_json_error(['message' => 'No tienes permiso para enviar mensajes a este usuario'], 403);
        }

        $message_id = MessageRepository::create($sender_id, $receiver_id, $message);

        if ($message_id) {
            wp_send_json_success([
                'message' => 'Mensaje enviado',
                'message_id' => $message_id,
                'created_at' => current_time('mysql')
            ]);
        }

        wp_send_json_error(['message' => 'Error al enviar mensaje'], 500);
    }

    public static function getMessages(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $user_id = get_current_user_id();
        $other_user_id = intval($_POST['other_user_id'] ?? 0);

        if (!$other_user_id) {
            wp_send_json_error(['message' => 'Usuario no especificado'], 400);
        }

        if (!self::hasRelationship($user_id, $other_user_id)) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $messages = MessageRepository::getConversation($user_id, $other_user_id);

        wp_send_json_success(['messages' => $messages]);
    }

    public static function markAsRead(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $message_id = intval($_POST['message_id'] ?? 0);
        $user_id = get_current_user_id();

        if (!$message_id) {
            wp_send_json_error(['message' => 'ID de mensaje inválido'], 400);
        }

        $success = MessageRepository::markAsRead($message_id, $user_id);

        if ($success) {
            wp_send_json_success(['message' => 'Mensaje marcado como leído']);
        }

        wp_send_json_error(['message' => 'Error al marcar mensaje'], 500);
    }

    private static function hasRelationship(int $user1, int $user2): bool {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}openmind_relationships
            WHERE (psychologist_id = %d AND patient_id = %d)
            OR (psychologist_id = %d AND patient_id = %d)
        ", $user1, $user2, $user2, $user1));

        return $count > 0;
    }
}