<?php // src/Controllers/ResponseController.php

namespace Openmind\Controllers;

if (!defined('ABSPATH')) exit;

class ResponseController {

    /**
     * Ocultar respuesta (soft delete)
     */
    public static function hideResponse(): void {
        check_ajax_referer('submit_activity_response', 'nonce');

        $response_id = isset($_POST['response_id']) ? absint($_POST['response_id']) : 0;
        $user_id = get_current_user_id();

        if (!$response_id) {
            wp_send_json_error('ID de respuesta inválido');
        }

        $comment = get_comment($response_id);

        if (!$comment) {
            wp_send_json_error('Respuesta no encontrada');
        }

        // Verificar ownership
        if ($comment->user_id != $user_id) {
            wp_send_json_error('No tienes permisos para ocultar esta respuesta');
        }

        // Actualizar estado a 'hidden'
        $updated = wp_update_comment([
            'comment_ID' => $response_id,
            'comment_approved' => 'hidden'
        ]);

        if (!$updated) {
            wp_send_json_error('Error al ocultar respuesta');
        }

        // Guardar metadata
        update_comment_meta($response_id, '_hidden_by', $user_id);
        update_comment_meta($response_id, '_hidden_at', current_time('mysql'));

        wp_send_json_success([
            'message' => 'Respuesta ocultada correctamente. Solo tu psicólogo puede verla.'
        ]);
    }

    /**
     * Editar respuesta (ilimitado con trace)
     */
    public static function editResponse(): void {
        check_ajax_referer('submit_activity_response', 'response_nonce');

        $response_id = isset($_POST['response_id']) ? absint($_POST['response_id']) : 0;
        $new_content = isset($_POST['response_content']) ? wp_kses_post($_POST['response_content']) : '';
        $user_id = get_current_user_id();

        if (!$response_id || !$new_content) {
            wp_send_json_error('Datos incompletos');
        }

        $comment = get_comment($response_id);

        if (!$comment || $comment->user_id != $user_id) {
            wp_send_json_error('No tienes permisos para editar esta respuesta');
        }

        // Guardar original si es primera edición
        $original = get_comment_meta($response_id, '_original_content', true);
        if (!$original) {
            update_comment_meta($response_id, '_original_content', $comment->comment_content);
        }

        // Guardar historial de ediciones
        $edit_history = get_comment_meta($response_id, '_edit_history', true) ?: [];
        $edit_history[] = [
            'content' => $comment->comment_content,
            'edited_at' => current_time('mysql'),
            'edited_by' => $user_id
        ];
        update_comment_meta($response_id, '_edit_history', $edit_history);

        // Actualizar contenido
        wp_update_comment([
            'comment_ID' => $response_id,
            'comment_content' => $new_content
        ]);

        // Manejar archivos si hay nuevos uploads
        $file_ids = [];
        if (!empty($_FILES['response_files']['name'][0])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $files = $_FILES['response_files'];
            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = [
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    ];
                    $_FILES = ['upload_file' => $file];
                    $attachment_id = media_handle_upload('upload_file', 0);

                    if (!is_wp_error($attachment_id)) {
                        $file_ids[] = $attachment_id;
                    }
                }
            }

            if (!empty($file_ids)) {
                update_comment_meta($response_id, '_response_files', $file_ids);
            }
        }

        wp_send_json_success([
            'message' => 'Respuesta actualizada correctamente'
        ]);
    }

    /**
     * Eliminar respuesta (solo para compatibilidad - redirige a hide)
     */
    public static function deleteResponse(): void {
        self::hideResponse();
    }
}