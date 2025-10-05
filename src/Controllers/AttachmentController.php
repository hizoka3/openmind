<?php // src/Controllers/AttachmentController.php
namespace Openmind\Controllers;

use Openmind\Repositories\AttachmentRepository;
use Openmind\Services\ImageUploadService;

class AttachmentController {

    public static function init(): void {
        add_action('wp_ajax_openmind_upload_attachment', [self::class, 'upload']);
        add_action('wp_ajax_openmind_delete_attachment', [self::class, 'delete']);
    }

    public static function upload(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $entry_type = sanitize_text_field($_POST['entry_type'] ?? '');
        $entry_id = intval($_POST['entry_id'] ?? 0);

        // Validar permisos según tipo
        if ($entry_type === 'session_note' && !current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        if ($entry_type === 'diary' && !current_user_can('write_diary')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        // Validar límite
        $current_count = AttachmentRepository::countByEntry($entry_type, $entry_id);
        if ($current_count >= ImageUploadService::MAX_FILES) {
            wp_send_json_error(['message' => 'Máximo ' . ImageUploadService::MAX_FILES . ' imágenes'], 400);
        }

        if (empty($_FILES['file'])) {
            wp_send_json_error(['message' => 'No se recibió archivo'], 400);
        }

        $result = ImageUploadService::upload($_FILES['file'], $entry_type);

        if (isset($result['error'])) {
            wp_send_json_error(['message' => $result['error']], 400);
        }

        // Guardar en DB
        $attachment_id = AttachmentRepository::create(
            $entry_type,
            $entry_id,
            $result['file_name'],
            $result['file_path'],
            $result['file_type'],
            $result['file_size']
        );

        if ($attachment_id) {
            wp_send_json_success([
                'attachment' => [
                    'id' => $attachment_id,
                    'file_path' => $result['file_path'],
                    'file_name' => $result['file_name']
                ]
            ]);
        }

        wp_send_json_error(['message' => 'Error al guardar adjunto'], 500);
    }

    public static function delete(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $attachment_id = intval($_POST['attachment_id'] ?? 0);

        if (!$attachment_id) {
            wp_send_json_error(['message' => 'ID inválido'], 400);
        }

        // Obtener attachment para verificar permisos
        global $wpdb;
        $attachment = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}openmind_attachments WHERE id = %d
        ", $attachment_id));

        if (!$attachment) {
            wp_send_json_error(['message' => 'Adjunto no encontrado'], 404);
        }

        // Verificar permisos según tipo
        if ($attachment->entry_type === 'session_note') {
            $note = \Openmind\Repositories\SessionNoteRepository::getById($attachment->entry_id);
            if (!$note || $note->psychologist_id != get_current_user_id()) {
                wp_send_json_error(['message' => 'Sin permisos'], 403);
            }
        } elseif ($attachment->entry_type === 'diary') {
            $entry = \Openmind\Repositories\DiaryRepository::getById($attachment->entry_id);
            if (!$entry || $entry->patient_id != get_current_user_id()) {
                wp_send_json_error(['message' => 'Sin permisos'], 403);
            }
        }

        $deleted = AttachmentRepository::delete($attachment_id);

        if ($deleted) {
            wp_send_json_success(['message' => 'Imagen eliminada']);
        }

        wp_send_json_error(['message' => 'Error al eliminar'], 500);
    }
}