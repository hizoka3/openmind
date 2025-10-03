<?php // src/Repositories/DiaryRepository.php
namespace Openmind\Repositories;

class DiaryRepository {

    /**
     * Crear entrada (genérico)
     */
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

    /**
     * Crear bitácora por psicólogo
     */
    public static function createByPsychologist(int $psychologist_id, int $patient_id, string $content, string $mood = ''): int {
        return self::create($patient_id, $psychologist_id, $content, $mood, false);
    }

    /**
     * Obtener bitácoras de un paciente (escritas por psicólogo)
     */
    public static function getPsychologistEntries(int $patient_id, int $limit = 10, int $offset = 0): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT d.*, 
                   u.display_name as author_name
            FROM {$wpdb->prefix}openmind_diary d
            JOIN {$wpdb->users} u ON d.author_id = u.ID
            WHERE d.patient_id = %d 
            AND d.is_private = 0
            ORDER BY d.created_at DESC
            LIMIT %d OFFSET %d
        ", $patient_id, $limit, $offset));
    }

    /**
     * Contar total de bitácoras de un paciente
     */
    public static function countPsychologistEntries(int $patient_id): int {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_diary
            WHERE patient_id = %d AND is_private = 0
        ", $patient_id));
    }

    /**
     * Obtener entradas privadas del paciente (diario personal)
     */
    public static function getByPatient(int $patient_id, int $limit = 10, bool $private_only = false): array {
        global $wpdb;

        $where = $private_only ? "AND is_private = 1" : "AND is_private = 0";

        return $wpdb->get_results($wpdb->prepare("
            SELECT * 
            FROM {$wpdb->prefix}openmind_diary
            WHERE patient_id = %d {$where}
            ORDER BY created_at DESC
            LIMIT %d
        ", $patient_id, $limit));
    }

    /**
     * Obtener una entrada por ID
     */
    public static function getById(int $entry_id): ?object {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT d.*, 
                   u.display_name as author_name
            FROM {$wpdb->prefix}openmind_diary d
            JOIN {$wpdb->users} u ON d.author_id = u.ID
            WHERE d.id = %d
        ", $entry_id));
    }

    /**
     * Actualizar entrada
     */
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

    /**
     * Eliminar entrada
     */
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
     * Obtener última entrada de bitácora de un paciente
     */
    public static function getLatestEntry(int $patient_id): ?object {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT d.*, 
                   u.display_name as author_name
            FROM {$wpdb->prefix}openmind_diary d
            JOIN {$wpdb->users} u ON d.author_id = u.ID
            WHERE d.patient_id = %d AND d.is_private = 0
            ORDER BY d.created_at DESC
            LIMIT 1
        ", $patient_id));
    }
}