<?php // src/Controllers/DiaryController.php
namespace Openmind\Controllers;

use Openmind\Repositories\DiaryRepository;

class DiaryController {

    public static function init(): void {
        add_action('wp_ajax_openmind_save_diary', [self::class, 'saveDiary']);
        add_action('wp_ajax_openmind_save_psychologist_diary', [self::class, 'savePsychologistDiary']);
        add_action('wp_ajax_openmind_update_psychologist_diary', [self::class, 'updatePsychologistDiary']);
        add_action('wp_ajax_openmind_delete_diary', [self::class, 'deleteEntry']);
        add_action('wp_ajax_openmind_get_diary_entries', [self::class, 'getEntries']);
        add_action('wp_ajax_openmind_toggle_share_diary', [self::class, 'toggleShareDiary']);
        add_action('wp_ajax_openmind_get_shared_count', [self::class, 'getSharedCount']);
    }

    /**
     * Guardar entrada de diario personal del paciente (AJAX - legacy)
     */
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

    /**
     * Guardar entrada de diario del paciente (form submit)
     */
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

        $entry_id = DiaryRepository::create($patient_id, $patient_id, $content, $mood, true); // Siempre privado

        if ($entry_id) {
            wp_redirect(add_query_arg('view', 'diario', home_url('/dashboard-paciente/')));
            exit;
        }

        wp_die('Error al guardar la entrada');
    }

    /**
     * Toggle compartir/descompartir entrada
     */
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

        // Si cambió de privado → compartido, enviar email al psicólogo
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

    /**
     * Obtener contador de entradas compartidas nuevas (para badge)
     */
    public static function getSharedCount(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        if (!current_user_can('manage_patients')) {
            wp_send_json_error(['message' => 'Sin permisos'], 403);
        }

        $psychologist_id = get_current_user_id();
        $count = DiaryRepository::countRecentSharedByPsychologist($psychologist_id);

        wp_send_json_success(['count' => $count]);
    }

    /**
     * Guardar bitácora del psicólogo (NO AJAX - form submit)
     */
    public static function savePsychologistDiary(): void {
        if (!isset($_POST['openmind_diary_nonce']) || !wp_verify_nonce($_POST['openmind_diary_nonce'], 'save_psychologist_diary')) {
            wp_die('Error de seguridad');
        }

        if (!current_user_can('manage_patients')) {
            wp_die('Sin permisos');
        }

        $psychologist_id = get_current_user_id();
        $patient_id = intval($_POST['patient_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $mood = sanitize_text_field($_POST['mood'] ?? '');
        $return = sanitize_text_field($_POST['return'] ?? 'lista');

        if (empty($content)) {
            wp_die('El contenido es requerido');
        }

        $relationship_exists = get_user_meta($patient_id, 'psychologist_id', true) == $psychologist_id;
        if (!$relationship_exists) {
            wp_die('No tienes acceso a este paciente');
        }

        $entry_id = DiaryRepository::createByPsychologist($psychologist_id, $patient_id, $content, $mood);

        if ($entry_id) {
            if ($return === 'detalle') {
                wp_redirect(add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')));
            } else {
                wp_redirect(add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')));
            }
            exit;
        }

        wp_die('Error al guardar la entrada');
    }

    /**
     * Actualizar bitácora del psicólogo
     */
    public static function updatePsychologistDiary(): void {
        if (!isset($_POST['openmind_diary_nonce']) || !wp_verify_nonce($_POST['openmind_diary_nonce'], 'update_psychologist_diary')) {
            wp_die('Error de seguridad');
        }

        if (!current_user_can('manage_patients')) {
            wp_die('Sin permisos');
        }

        $entry_id = intval($_POST['entry_id'] ?? 0);
        $content = wp_kses_post($_POST['content'] ?? '');
        $mood = sanitize_text_field($_POST['mood'] ?? '');
        $patient_id = intval($_POST['patient_id'] ?? 0);
        $return = sanitize_text_field($_POST['return'] ?? 'lista');

        $entry = DiaryRepository::getById($entry_id);
        if (!$entry || $entry->author_id != get_current_user_id()) {
            wp_die('No tienes permisos para editar esta entrada');
        }

        $updated = DiaryRepository::update($entry_id, $content, $mood);

        if ($updated) {
            if ($return === 'detalle') {
                wp_redirect(add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')));
            } else {
                wp_redirect(add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')));
            }
            exit;
        }

        wp_die('Error al actualizar la entrada');
    }

    /**
     * Obtener entradas
     */
    public static function getEntries(): void {
        check_ajax_referer('openmind_nonce', 'nonce');

        $patient_id = get_current_user_id();
        $limit = intval($_POST['limit'] ?? 10);
        $private_only = isset($_POST['private_only']) && $_POST['private_only'] === '1';

        $entries = DiaryRepository::getByPatient($patient_id, $limit, $private_only);

        wp_send_json_success(['entries' => $entries]);
    }

    /**
     * Eliminar entrada
     */
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
            wp_send_json_success(['message' => 'Entrada eliminada']);
        }

        wp_send_json_error(['message' => 'Error al eliminar entrada'], 500);
    }
}