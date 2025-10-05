<?php // src/Repositories/DiaryRepository.php
namespace Openmind\Repositories;

class DiaryRepository {

    public static function create(int $patient_id, int $author_id, string $content, string $mood = '', bool $is_private = false): int {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'openmind_diary',
            [
                'patient_id' => $patient_id,
                'author_id' => $author_id,
                'content' => $content,
                'mood' => $mood,
                'is_private' => $is_private ? 1 : 0
            ],
            ['%d', '%d', '%s', '%s', '%d']
        );

        return $wpdb->insert_id;
    }

    public static function getById(int $id): ?object {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}openmind_diary WHERE id = %d
        ", $id));
    }

    public static function getByPatient(int $patient_id, int $limit = 10, bool $private_only = false, int $offset = 0): array {
        global $wpdb;

        $where = $private_only ? "AND is_private = 1" : "";

        return $wpdb->get_results($wpdb->prepare("
            SELECT * 
            FROM {$wpdb->prefix}openmind_diary
            WHERE patient_id = %d 
            AND author_id = %d
            {$where}
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", $patient_id, $patient_id, $limit, $offset));
    }

    public static function countByPatient(int $patient_id, bool $private_only = false): int {
        global $wpdb;

        $where = $private_only ? "AND is_private = 1" : "";

        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_diary
            WHERE patient_id = %d 
            AND author_id = %d
            {$where}
        ", $patient_id, $patient_id));
    }

    public static function getSharedByPsychologist(int $psychologist_id, int $limit = 10, int $offset = 0): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT d.*, 
                   u.display_name as patient_name
            FROM {$wpdb->prefix}openmind_diary d
            JOIN {$wpdb->users} u ON d.patient_id = u.ID
            WHERE d.is_private = 0
            AND EXISTS (
                SELECT 1 FROM {$wpdb->prefix}openmind_relationships r
                WHERE r.psychologist_id = %d 
                AND r.patient_id = d.patient_id
            )
            ORDER BY d.created_at DESC
            LIMIT %d OFFSET %d
        ", $psychologist_id, $limit, $offset));
    }

    public static function countRecentSharedByPsychologist(int $psychologist_id, int $days = 7): int {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_diary d
            WHERE d.is_private = 0
            AND d.created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            AND EXISTS (
                SELECT 1 FROM {$wpdb->prefix}openmind_relationships r
                WHERE r.psychologist_id = %d 
                AND r.patient_id = d.patient_id
            )
        ", $days, $psychologist_id));
    }

    public static function toggleShare(int $entry_id, int $patient_id): array {
        global $wpdb;

        $current = $wpdb->get_var($wpdb->prepare("
            SELECT is_private 
            FROM {$wpdb->prefix}openmind_diary
            WHERE id = %d AND patient_id = %d AND author_id = %d
        ", $entry_id, $patient_id, $patient_id));

        if ($current === null) {
            return ['success' => false, 'was_private' => null];
        }

        $was_private = $current == 1;
        $new_state = $was_private ? 0 : 1;

        $updated = $wpdb->update(
            $wpdb->prefix . 'openmind_diary',
            ['is_private' => $new_state],
            ['id' => $entry_id],
            ['%d'],
            ['%d']
        );

        return [
            'success' => $updated !== false,
            'was_private' => $was_private,
            'is_now_shared' => !$was_private ? false : true
        ];
    }

    public static function update(int $entry_id, string $content, string $mood = ''): bool {
        global $wpdb;

        $updated = $wpdb->update(
            $wpdb->prefix . 'openmind_diary',
            [
                'content' => $content,
                'mood' => $mood
            ],
            ['id' => $entry_id],
            ['%s', '%s'],
            ['%d']
        );

        return $updated !== false;
    }

    public static function delete(int $entry_id, int $author_id): bool {
        global $wpdb;

        $deleted = $wpdb->delete(
            $wpdb->prefix . 'openmind_diary',
            [
                'id' => $entry_id,
                'author_id' => $author_id
            ],
            ['%d', '%d']
        );

        return $deleted !== false;
    }

    /**
     * Obtener entradas compartidas por paciente (para psicÃ³logo)
     */
    public static function getSharedByPatient(int $patient_id, int $limit = 10): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT * 
            FROM {$wpdb->prefix}openmind_diary
            WHERE patient_id = %d 
            AND author_id = %d 
            AND is_private = 0
            ORDER BY created_at DESC
            LIMIT %d
        ", $patient_id, $patient_id, $limit));
    }
}