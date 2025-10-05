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
                'message' => $message,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s']
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

    public static function getUnreadCountByUser(int $receiver_id, int $sender_id): int {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_messages
            WHERE receiver_id = %d AND sender_id = %d AND is_read = 0
        ", $receiver_id, $sender_id));
    }

    public static function getPatientConversations(int $patient_id): array {
        global $wpdb;

        $current_psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);

        $results = $wpdb->get_results($wpdb->prepare("
        SELECT 
            CASE 
                WHEN m.sender_id = %d THEN m.receiver_id
                ELSE m.sender_id
            END as psychologist_id,
            MAX(m.created_at) as last_message_at
        FROM {$wpdb->prefix}openmind_messages m
        WHERE m.sender_id = %d OR m.receiver_id = %d
        GROUP BY psychologist_id
        ORDER BY last_message_at DESC
    ", $patient_id, $patient_id, $patient_id));

        foreach ($results as $conv) {
            $user = get_userdata($conv->psychologist_id);
            $conv->display_name = $user ? $user->display_name : 'Usuario desconocido';
            $conv->is_current = ($conv->psychologist_id == $current_psychologist_id);

            $conv->unread_count = (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_messages
            WHERE receiver_id = %d 
            AND sender_id = %d 
            AND is_read = 0
        ", $patient_id, $conv->psychologist_id));
        }

        return $results;
    }

    public static function getPsychologistConversations(int $psychologist_id): array {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("
        SELECT 
            CASE 
                WHEN m.sender_id = %d THEN m.receiver_id
                ELSE m.sender_id
            END as patient_id,
            MAX(m.created_at) as last_message_at
        FROM {$wpdb->prefix}openmind_messages m
        WHERE m.sender_id = %d OR m.receiver_id = %d
        GROUP BY patient_id
        ORDER BY last_message_at DESC
    ", $psychologist_id, $psychologist_id, $psychologist_id));

        foreach ($results as $conv) {
            $user = get_userdata($conv->patient_id);
            $conv->display_name = $user ? $user->display_name : 'Usuario desconocido';

            $conv->unread_count = (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_messages
            WHERE receiver_id = %d 
            AND sender_id = %d 
            AND is_read = 0
        ", $psychologist_id, $conv->patient_id));
        }

        return $results;
    }

    public static function markConversationAsRead(int $receiver_id, int $sender_id): bool {
        global $wpdb;

        return $wpdb->update(
                $wpdb->prefix . 'openmind_messages',
                ['is_read' => 1],
                [
                    'receiver_id' => $receiver_id,
                    'sender_id' => $sender_id,
                    'is_read' => 0
                ],
                ['%d'],
                ['%d', '%d', '%d']
            ) !== false;
    }

    public static function getConversationPaginated(int $user1, int $user2, int $limit = 50, int $offset = 0): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT m.*, 
                   u.display_name as sender_name
            FROM {$wpdb->prefix}openmind_messages m
            JOIN {$wpdb->users} u ON m.sender_id = u.ID
            WHERE (sender_id = %d AND receiver_id = %d) 
            OR (sender_id = %d AND receiver_id = %d)
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", $user1, $user2, $user2, $user1, $limit, $offset));
    }

    public static function getConversationCount(int $user1, int $user2): int {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_messages
            WHERE (sender_id = %d AND receiver_id = %d) 
            OR (sender_id = %d AND receiver_id = %d)
        ", $user1, $user2, $user2, $user1));
    }
}