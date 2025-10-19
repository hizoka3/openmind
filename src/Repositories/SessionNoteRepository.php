<?php // src/Repositories/SessionNoteRepository.php
namespace Openmind\Repositories;

class SessionNoteRepository {

    public static function create(int $psychologist_id, int $patient_id, string $private_notes, string $public_content = '', string $mood = ''): int {
        global $wpdb;

        $session_number = self::getNextSessionNumber($patient_id);

        $wpdb->insert(
            $wpdb->prefix . 'openmind_session_notes',
            [
                'psychologist_id' => $psychologist_id,
                'patient_id' => $patient_id,
                'session_number' => $session_number,
                'private_notes' => $private_notes,
                'public_content' => $public_content,
                'mood_assessment' => $mood
            ],
            ['%d', '%d', '%d', '%s', '%s', '%s']
        );

        return $wpdb->insert_id;
    }

    public static function getById(int $id): ?object {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT sn.*, 
                   p.display_name as psychologist_name
            FROM {$wpdb->prefix}openmind_session_notes sn
            JOIN {$wpdb->users} p ON sn.psychologist_id = p.ID
            WHERE sn.id = %d
        ", $id));
    }

    public static function getByPatient(int $patient_id, int $limit = 10, int $offset = 0): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT sn.*, 
                   p.display_name as psychologist_name
            FROM {$wpdb->prefix}openmind_session_notes sn
            JOIN {$wpdb->users} p ON sn.psychologist_id = p.ID
            WHERE sn.patient_id = %d
            ORDER BY sn.created_at DESC
            LIMIT %d OFFSET %d
        ", $patient_id, $limit, $offset));
    }

    public static function countByPatient(int $patient_id): int {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_session_notes
            WHERE patient_id = %d
        ", $patient_id));
    }

    public static function getLatest(int $patient_id): ?object {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT sn.*, 
                   p.display_name as psychologist_name
            FROM {$wpdb->prefix}openmind_session_notes sn
            JOIN {$wpdb->users} p ON sn.psychologist_id = p.ID
            WHERE sn.patient_id = %d
            ORDER BY sn.created_at DESC
            LIMIT 1
        ", $patient_id));
    }

    public static function update(int $id, string $private_notes, string $public_content = '', string $mood = '', string $next_steps = ''): bool {
        global $wpdb;

        $updated = $wpdb->update(
            $wpdb->prefix . 'openmind_session_notes',
            [
                'private_notes' => $private_notes,
                'public_content' => $public_content,
                'mood_assessment' => $mood,
                'next_steps' => $next_steps
            ],
            ['id' => $id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );

        return $updated !== false;
    }

    public static function delete(int $id, int $psychologist_id): bool {
        global $wpdb;

        $deleted = $wpdb->delete(
            $wpdb->prefix . 'openmind_session_notes',
            [
                'id' => $id,
                'psychologist_id' => $psychologist_id
            ],
            ['%d', '%d']
        );

        return $deleted !== false;
    }

    private static function getNextSessionNumber(int $patient_id): int {
        global $wpdb;

        $max = $wpdb->get_var($wpdb->prepare("
            SELECT MAX(session_number) 
            FROM {$wpdb->prefix}openmind_session_notes
            WHERE patient_id = %d
        ", $patient_id));

        return $max ? ($max + 1) : 1;
    }
}