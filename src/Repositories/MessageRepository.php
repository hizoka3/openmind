<?php // src/Repositories/MessageRepository.php
namespace Openmind\Repositories;

class MessageRepository {

    public static function create(int $sender_id, int $receiver_id, string $message): int {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'openmind_messages',
            [
                'sender_id' => $sender_id,
                'receiver_id' => $receiver_id,
                'message' => $message
            ],
            ['%d', '%d', '%s']
        );

        return $wpdb->insert_id;
    }

    public static function getConversation(int $user1, int $user2): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT m.*, 
                   u.display_name as sender_name
            FROM {$wpdb->prefix}openmind_messages m
            JOIN {$wpdb->users} u ON m.sender_id = u.ID
            WHERE (sender_id = %d AND receiver_id = %d) 
            OR (sender_id = %d AND receiver_id = %d)
            ORDER BY created_at ASC
        ", $user1, $user2, $user2, $user1));
    }

    public static function markAsRead(int $message_id, int $user_id): bool {
        global $wpdb;

        $updated = $wpdb->update(
            $wpdb->prefix . 'openmind_messages',
            ['is_read' => 1],
            [
                'id' => $message_id,
                'receiver_id' => $user_id
            ],
            ['%d'],
            ['%d', '%d']
        );

        return $updated !== false;
    }

    public static function getUnreadCount(int $user_id): int {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_messages
            WHERE receiver_id = %d AND is_read = 0
        ", $user_id));
    }
}