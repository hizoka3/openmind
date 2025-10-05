<?php // src/Repositories/AttachmentRepository.php
namespace Openmind\Repositories;

class AttachmentRepository {

    const TYPE_DIARY = 'diary';
    const TYPE_SESSION_NOTE = 'session_note';

    public static function create(string $entry_type, int $entry_id, string $file_name, string $file_path, string $file_type, int $file_size): int {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'openmind_attachments',
            [
                'entry_type' => $entry_type,
                'entry_id' => $entry_id,
                'file_name' => $file_name,
                'file_path' => $file_path,
                'file_type' => $file_type,
                'file_size' => $file_size
            ],
            ['%s', '%d', '%s', '%s', '%s', '%d']
        );

        return $wpdb->insert_id;
    }

    public static function getByEntry(string $entry_type, int $entry_id): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT * 
            FROM {$wpdb->prefix}openmind_attachments
            WHERE entry_type = %s 
            AND entry_id = %d
            ORDER BY uploaded_at ASC
        ", $entry_type, $entry_id));
    }

    public static function countByEntry(string $entry_type, int $entry_id): int {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}openmind_attachments
            WHERE entry_type = %s 
            AND entry_id = %d
        ", $entry_type, $entry_id));
    }

    public static function delete(int $id): bool {
        global $wpdb;

        $attachment = $wpdb->get_row($wpdb->prepare("
            SELECT file_path 
            FROM {$wpdb->prefix}openmind_attachments
            WHERE id = %d
        ", $id));

        if ($attachment) {
            // Eliminar archivo físico
            $file_path = ABSPATH . ltrim($attachment->file_path, '/');
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Eliminar registro
            return $wpdb->delete(
                    $wpdb->prefix . 'openmind_attachments',
                    ['id' => $id],
                    ['%d']
                ) !== false;
        }

        return false;
    }

    public static function deleteByEntry(string $entry_type, int $entry_id): bool {
        global $wpdb;

        $attachments = self::getByEntry($entry_type, $entry_id);

        foreach ($attachments as $att) {
            self::delete($att->id);
        }

        return true;
    }
}