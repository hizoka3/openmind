<?php // src/Repositories/DiaryRepository.php
namespace Openmind\Repositories;

class DiaryRepository {

    public static function create(int $patient_id, string $content, string $mood = ''): int {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'openmind_diary',
            [
                'patient_id' => $patient_id,
                'content' => $content,
                'mood' => $mood
            ],
            ['%d', '%s', '%s']
        );

        return $wpdb->insert_id;
    }

    public static function getByPatient(int $patient_id, int $limit = 10): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT * 
            FROM {$wpdb->prefix}openmind_diary
            WHERE patient_id = %d
            ORDER BY created_at DESC
            LIMIT %d
        ", $patient_id, $limit));
    }

    public static function delete(int $entry_id, int $patient_id): bool {
        global $wpdb;

        $deleted = $wpdb->delete(
            $wpdb->prefix . 'openmind_diary',
            [
                'id' => $entry_id,
                'patient_id' => $patient_id
            ],
            ['%d', '%d']
        );

        return $deleted !== false;
    }
}