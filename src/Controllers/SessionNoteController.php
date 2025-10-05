<?php // src/Controllers/SessionNoteController.php
namespace Openmind\Controllers;

use Openmind\Repositories\SessionNoteRepository;

class SessionNoteController {

    public static function init(): void {
        add_action('wp_ajax_openmind_delete_session_note', [self::class, 'deleteNote']);
    }

    public static function save(): void {
        if (!isset($_POST['openmind_session_note_nonce']) || !wp_verify_nonce($_POST['openmind_session_note_nonce'], 'save_session_note')) {
            wp_die('Error de seguridad');
        }

        if (!current_user_can('manage_patients')) {
            wp_die('Sin permisos');
        }

        $psychologist_id = get_current_user_id();
        $patient_id = intval($_POST['patient_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $mood = sanitize_text_field($_POST['mood'] ?? '');
        $next_steps = wp_kses_post($_POST['next_steps'] ?? '');
        $return = sanitize_text_field($_POST['return'] ?? 'lista');

        if (empty($content)) {
            wp_die('El contenido es requerido');
        }

        // Verificar relación
        $relationship_exists = get_user_meta($patient_id, 'psychologist_id', true) == $psychologist_id;
        if (!$relationship_exists) {
            wp_die('No tienes acceso a este paciente');
        }

        $note_id = SessionNoteRepository::create($psychologist_id, $patient_id, $content, $mood, $next_steps);

        if ($note_id) {
            // Procesar imágenes si hay
            self::processAttachments($note_id, $_FILES['attachments'] ?? []);

            $redirect = $return === 'detalle'
                ? add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))
                : add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'));

            wp_redirect($redirect);
            exit;
        }

        wp_die('Error al guardar la bitácora');
    }

    public static function update(): void {
        if (!isset($_POST['openmind_session_note_nonce']) || !wp_verify_nonce($_POST['openmind_session_note_nonce'], 'update_session_note')) {
            wp_die('Error de seguridad');
        }

        if (!current_user_can('manage_patients')) {
            wp_die('Sin permisos');
        }

        $note_id = intval($_POST['note_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $mood = sanitize_text_field($_POST['mood'] ?? '');
        $next_steps = wp_kses_post($_POST['next_steps'] ?? '');
        $patient_id = intval($_POST['patient_id'] ?? 0);
        $return = sanitize_text_field($_POST['return'] ?? 'lista');

        $note = SessionNoteRepository::getById($note_id);
        if (!$note || $note->psychologist_id != get_current_user_id()) {
            wp_die('No tienes permisos para editar esta bitácora');
        }

        $updated = SessionNoteRepository::update($note_id, $content, $mood, $next_steps);

        if ($updated) {
            // Procesar nuevas imágenes
            self::processAttachments($note_id, $_FILES['attachments'] ?? []);

            $redirect = $return === 'detalle'
                ? add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))
                : add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'));

            wp_redirect($redirect);
            exit;
        }

        wp_die('Error al actualizar la bitácora');
    }

    public static function deleteNote(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $note_id = intval($_POST['note_id'] ?? 0);
        $psychologist_id = get_current_user_id();

        if (!$note_id) {
            wp_send_json_error(['message' => 'ID inválido'], 400);
        }

        $deleted = SessionNoteRepository::delete($note_id, $psychologist_id);

        if ($deleted) {
            // Eliminar attachments
            \Openmind\Repositories\AttachmentRepository::deleteByEntry('session_note', $note_id);
            wp_send_json_success(['message' => 'Bitácora eliminada']);
        }

        wp_send_json_error(['message' => 'Error al eliminar'], 500);
    }

    private static function processAttachments(int $note_id, array $files): void {
        if (empty($files['name'][0])) return;

        $current_count = \Openmind\Repositories\AttachmentRepository::countByEntry('session_note', $note_id);
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
                $result = \Openmind\Services\ImageUploadService::upload($file, 'session_note');

                if ($result['success'] ?? false) {
                    \Openmind\Repositories\AttachmentRepository::create(
                        'session_note',
                        $note_id,
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