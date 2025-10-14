<?php // templates/components/activity/response-form.php
/**
 * Component: Activity Response Form (con acordeón)
 * Props: $form_args = [
 *   'assignment_id' => int,
 *   'form_id' => string,
 *   'editor_id' => string,
 *   'button_text' => string,
 *   'title' => string,
 *   'icon' => string,
 *   'bg_class' => string,
 *   'extra_fields' => array (opcional)
 * ]
 */

if (!defined('ABSPATH')) exit;

$assignment_id = $form_args['assignment_id'];
$form_id = $form_args['form_id'];
$editor_id = $form_args['editor_id'];
$button_text = $form_args['button_text'];
$title = $form_args['title'];
$icon = $form_args['icon'] ?? 'fa-pen';
$bg_class = $form_args['bg_class'] ?? 'bg-white';
$extra_fields = $form_args['extra_fields'] ?? [];
?>

<div class="<?php echo esc_attr($bg_class); ?> rounded-xl shadow-sm border border-gray-200 mb-6">
    <!-- Header del acordeón (siempre visible) -->
    <button type="button"
            class="w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors accordion-toggle"
            data-target="<?php echo esc_attr($form_id); ?>-content">
        <div class="flex items-center gap-2">
            <h3 class="text-lg font-semibold text-gray-900 m-0"><?php echo esc_html($title); ?></h3>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform accordion-icon"></i>
    </button>

    <!-- Contenido del acordeón (inicialmente oculto) -->
    <div id="<?php echo esc_attr($form_id); ?>-content" class="accordion-content hidden border-t border-gray-200 p-6">
        <form id="<?php echo esc_attr($form_id); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('submit_activity_response', 'response_nonce'); ?>
            <input type="hidden" name="assignment_id" value="<?php echo esc_attr($assignment_id); ?>">
            <input type="hidden" name="response_id" value="0" id="response_id">

            <?php foreach($extra_fields as $name => $value): ?>
                <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
            <?php endforeach; ?>

            <div class="mb-4">
                <?php
                wp_editor('', $editor_id, [
                        'textarea_name' => $editor_id,
                        'textarea_rows' => 10,
                        'media_buttons' => false,
                        'teeny' => true,
                        'quicktags' => false
                ]);
                ?>
            </div>

            <div class="mb-4">
                <label for="response_files" class="block text-sm font-medium text-gray-700 mb-2">
                    Archivos adjuntos (máx. 5)
                </label>
                <input type="file"
                       name="response_files[]"
                       id="response_files"
                       multiple
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                       max="5"
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none p-2">
                <p class="mt-1 text-xs text-gray-500">Formatos: PDF, DOC, DOCX, JPG, PNG, GIF</p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-6 py-3 bg-primary-500 text-white rounded-lg font-semibold"
                        id="submit-response">
                    <?php echo esc_html($button_text); ?>
                </button>
                <button type="button"
                        class="px-6 py-3 bg-gray-500 text-white rounded-lg font-semibold hover:bg-gray-600 transition-colors"
                        id="cancel-edit"
                        style="display:none;">
                    <i class="fa-solid fa-times mr-2"></i>
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>