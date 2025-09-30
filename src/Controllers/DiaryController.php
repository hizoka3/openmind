<?php // src/Controllers/DiaryController.php
namespace Openmind\Controllers;

use Openmind\Repositories\DiaryRepository;

class DiaryController {

    public static function init(): void {
        add_action('wp_ajax_openmind_save_diary', [self::class, 'saveDiary']);
        add_action('wp_ajax_openmind_get_diary_entries', [self::class, 'getEntries']);
        add_action('wp_ajax_openmind_delete_diary', [self::class, 'deleteEntry']);
    }

    public static function saveDiary(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('write_diary')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $patient_id = get_current_user_id();
        $content = wp_kses_post($_POST['content'] ?? '');
        $mood = sanitize_text_field($_POST['mood'] ?? '');

        if (empty($content)) {
            wp_send_json_error(['message' => 'El contenido es requerido'], 400);
        }

        $entry_id = DiaryRepository::create($patient_id, $content, $mood);

        if ($entry_id) {
            wp_send_json_success([
                'message' => 'Entrada guardada',
                'entry_id' => $entry_id
            ]);
        }

        wp_send_json_error(['message' => 'Error al guardar entrada'], 500);
    }

    public static function getEntries(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $patient_id = get_current_user_id();
        $limit = intval($_POST['limit'] ?? 10);

        $entries = DiaryRepository::getByPatient($patient_id, $limit);

        wp_send_json_success(['entries' => $entries]);
    }

    public static function deleteEntry(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('write_diary')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $entry_id = intval($_POST['entry_id'] ?? 0);
        $patient_id = get_current_user_id();

        $deleted = DiaryRepository::delete($entry_id, $patient_id);

        if ($deleted) {
            wp_send_json_success(['message' => 'Entrada eliminada']);
        }

        wp_send_json_error(['message' => 'Error al eliminar entrada'], 500);
    }
}