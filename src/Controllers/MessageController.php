<?php // src/Controllers/MessageController.php
namespace Openmind\Controllers;

use Openmind\Repositories\MessageRepository;

class MessageController {

    public static function init(): void {
        add_action('wp_ajax_openmind_send_message', [self::class, 'sendMessage']);
        add_action('wp_ajax_openmind_get_messages', [self::class, 'getMessages']);
        add_action('wp_ajax_openmind_mark_read', [self::class, 'markAsRead']);
        add_action('wp_ajax_openmind_get_unread_count', [self::class, 'getUnreadCount']);
        add_action('wp_ajax_openmind_get_conversations', [self::class, 'getConversations']);
        add_action('wp_ajax_openmind_mark_conversation_read', [self::class, 'markConversationRead']);
    }

    public static function sendMessage(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $sender_id = get_current_user_id();
        $receiver_id = intval($_POST['receiver_id'] ?? 0);
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if (empty($message) || !$receiver_id) {
            wp_send_json_error(['message' => 'Datos inválidos'], 400);
        }

        // Validación especial para pacientes
        if (current_user_can('view_activities')) {
            $current_psychologist_id = get_user_meta($sender_id, 'psychologist_id', true);

            if ($receiver_id != $current_psychologist_id) {
                wp_send_json_error([
                    'message' => 'Solo puedes enviar mensajes a tu psicólogo actual'
                ], 403);
            }
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
        $page = intval($_POST['page'] ?? 1);
        $per_page = 50;
        $offset = ($page - 1) * $per_page;

        if (!$other_user_id) {
            wp_send_json_error(['message' => 'Usuario no especificado'], 400);
        }

        if (!self::hasRelationship($user_id, $other_user_id)) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        // Obtener mensajes paginados
        $messages = MessageRepository::getConversationPaginated($user_id, $other_user_id, $per_page, $offset);

        // Formatear fechas para cada mensaje
        foreach ($messages as $msg) {
            $msg->formatted_time = openmind_time_ago($msg->created_at);
        }

        // Marcar conversación como leída al abrir
        MessageRepository::markConversationAsRead($user_id, $other_user_id);

        // Invertir array para mostrar cronológicamente
        wp_send_json_success([
            'messages' => array_reverse($messages),
            'has_more' => count($messages) === $per_page
        ]);
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

    public static function getUnreadCount(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $user_id = get_current_user_id();
        $count = MessageRepository::getUnreadCount($user_id);

        wp_send_json_success(['count' => $count]);
    }

    public static function getConversations(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $user_id = get_current_user_id();

        // Determinar si es psicólogo o paciente
        $conversations = current_user_can('manage_patients')
            ? MessageRepository::getPsychologistConversations($user_id)
            : MessageRepository::getPatientConversations($user_id);

        // Formatear fechas para cada conversación
        foreach ($conversations as $conv) {
            $conv->formatted_time = openmind_time_ago($conv->last_message_at);
        }

        wp_send_json_success(['conversations' => $conversations]);
    }

    public static function markConversationRead(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $receiver_id = get_current_user_id();
        $sender_id = intval($_POST['other_user_id'] ?? 0);

        if (!$sender_id) {
            wp_send_json_error(['message' => 'Usuario no especificado'], 400);
        }

        MessageRepository::markConversationAsRead($receiver_id, $sender_id);

        wp_send_json_success(['message' => 'Conversación marcada como leída']);
    }

    private static function hasRelationship(int $user1, int $user2): bool {
        global $wpdb;

        // Verificar en tabla de relaciones
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}openmind_relationships
            WHERE (psychologist_id = %d AND patient_id = %d)
            OR (psychologist_id = %d AND patient_id = %d)
        ", $user1, $user2, $user2, $user1));

        if ($count > 0) {
            return true;
        }

        // También verificar en historial de mensajes
        $has_history = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}openmind_messages
            WHERE (sender_id = %d AND receiver_id = %d)
            OR (sender_id = %d AND receiver_id = %d)
        ", $user1, $user2, $user2, $user1));

        return $has_history > 0;
    }
}