<?php // src/Controllers/DiaryController.php
namespace Openmind\Controllers;

use Openmind\Repositories\DiaryRepository;

class DiaryController {

    public static function init(): void {
        add_action('wp_ajax_openmind_save_diary', [self::class, 'saveDiary']);
        add_action('wp_ajax_openmind_delete_diary', [self::class, 'deleteEntry']);
        add_action('wp_ajax_openmind_get_diary_entries', [self::class, 'getEntries']);
        add_action('wp_ajax_openmind_toggle_share_diary', [self::class, 'toggleShareDiary']);
        add_action('wp_ajax_openmind_get_shared_count', [self::class, 'getSharedCount']);
    }

    public static function saveDiary(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('write_diary')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $patient_id = get_current_user_id();
        $content = wp_kses_post($_POST['content'] ?? '');
        $mood = sanitize_text_field($_POST['mood'] ?? '');
        $is_private = isset($_POST['is_private']) && $_POST['is_private'] === '1';

        if (empty($content)) {
            wp_send_json_error(['message' => 'El contenido es requerido'], 400);
        }

        $entry_id = DiaryRepository::create($patient_id, $patient_id, $content, $mood, $is_private);

        if ($entry_id) {
            wp_send_json_success([
                'message' => 'Entrada guardada',
                'entry_id' => $entry_id
            ]);
        }

        wp_send_json_error(['message' => 'Error al guardar entrada'], 500);
    }

    public static function savePatientDiary(): void {
        if (!isset($_POST['openmind_diary_nonce']) || !wp_verify_nonce($_POST['openmind_diary_nonce'], 'save_patient_diary')) {
            wp_die('Error de seguridad');
        }

        if (!current_user_can('write_diary')) {
            wp_die('Sin permisos');
        }

        $patient_id = get_current_user_id();
        $content = wp_kses_post($_POST['content'] ?? '');
        $mood = sanitize_text_field($_POST['mood'] ?? '');

        if (empty($content)) {
            wp_die('El contenido es requerido');
        }

        $entry_id = DiaryRepository::create($patient_id, $patient_id, $content, $mood, true);

        if ($entry_id) {
            // Procesar attachments
            self::processAttachments($entry_id, $_FILES['attachments'] ?? []);

            wp_redirect(add_query_arg('view', 'diario', home_url('/dashboard-paciente/')));
            exit;
        }

        wp_die('Error al guardar la entrada');
    }

    public static function toggleShareDiary(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('write_diary')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $entry_id = intval($_POST['entry_id'] ?? 0);
        $patient_id = get_current_user_id();

        if (!$entry_id) {
            wp_send_json_error(['message' => 'ID inválido'], 400);
        }

        $result = DiaryRepository::toggleShare($entry_id, $patient_id);

        if (!$result['success']) {
            wp_send_json_error(['message' => 'Error al cambiar estado'], 500);
        }

        // Email al psicólogo si se compartió
        if ($result['is_now_shared']) {
            $psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);
            if ($psychologist_id) {
                $psychologist = get_userdata($psychologist_id);
                $patient = get_userdata($patient_id);

                wp_mail(
                    $psychologist->user_email,
                    'Nueva entrada de diario compartida - OpenMind',
                    "Hola {$psychologist->display_name},\n\n" .
                    "{$patient->display_name} ha compartido una nueva entrada de su diario personal contigo.\n\n" .
                    "Fecha: " . current_time('d/m/Y H:i') . "\n\n" .
                    "Inicia sesión para verla: " . home_url('/dashboard-psicologo/')
                );
            }
        }

        $message = $result['was_private']
            ? 'Entrada compartida con tu psicólogo'
            : 'Entrada movida a privado';

        wp_send_json_success([
            'message' => $message,
            'is_shared' => $result['is_now_shared']
        ]);
    }

    public static function getSharedCount(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $psychologist_id = get_current_user_id();
        $count = DiaryRepository::countRecentSharedByPsychologist($psychologist_id);

        wp_send_json_success(['count' => $count]);
    }

    public static function getEntries(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $patient_id = get_current_user_id();
        $limit = intval($_POST['limit'] ?? 10);
        $private_only = isset($_POST['private_only']) && $_POST['private_only'] === '1';

        $entries = DiaryRepository::getByPatient($patient_id, $limit, $private_only);

        wp_send_json_success(['entries' => $entries]);
    }

    public static function deleteEntry(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $entry_id = intval($_POST['entry_id'] ?? 0);
        $user_id = get_current_user_id();

        if (!$entry_id) {
            wp_send_json_error(['message' => 'ID de entrada inválido'], 400);
        }

        $entry = DiaryRepository::getById($entry_id);
        if (!$entry || $entry->author_id != $user_id) {
            wp_send_json_error(['message' => 'No tienes permisos'], 403);
        }

        $deleted = DiaryRepository::delete($entry_id, $user_id);

        if ($deleted) {
            // Eliminar attachments
            \Openmind\Repositories\AttachmentRepository::deleteByEntry('diary', $entry_id);
            wp_send_json_success(['message' => 'Entrada eliminada']);
        }

        wp_send_json_error(['message' => 'Error al eliminar entrada'], 500);
    }

    private static function processAttachments(int $entry_id, array $files): void {
        if (empty($files['name'][0])) return;

        $current_count = \Openmind\Repositories\AttachmentRepository::countByEntry('diary', $entry_id);
        $max_allowed = \Openmind\Services\ImageUploadService::MAX_FILES - $current_count;

        $count = min(count($files['name']), $max_allowed);

        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'size' => $files['size'][$i],
                'error' => $files['error'][$i]
            ];

            if ($file['error'] === UPLOAD_ERR_OK) {
                $result = \Openmind\Services\ImageUploadService::upload($file, 'diary');

                if ($result['success'] ?? false) {
                    \Openmind\Repositories\AttachmentRepository::create(
                        'diary',
                        $entry_id,
                        $result['file_name'],
                        $result['file_path'],
                        $result['file_type'],
                        $result['file_size']
                    );
                }
            }
        }
    }
}