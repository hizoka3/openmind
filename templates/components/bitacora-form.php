<?php
// templates/components/bitacora-form.php
/**
 * @param array $args {
 *     @type int    $patient_id
 *     @type string $patient_name
 *     @type object $entry (opcional - para editar)
 *     @type string $return ('lista' | 'detalle')
 *     @type string $form_action ('create' | 'update')
 * }
 */

$patient_id = $args['patient_id'] ?? 0;
$patient_name = $args['patient_name'] ?? '';
$entry = $args['entry'] ?? null;
$return = $args['return'] ?? 'lista';
$form_action = $args['form_action'] ?? 'create';

$is_edit = $form_action === 'update';
$action = $is_edit ? 'openmind_update_session_note' : 'openmind_save_session_note';
$nonce_action = $is_edit ? 'update_session_note' : 'save_session_note';

$content = $entry->content ?? '';
$mood = $entry->mood_assessment ?? '';
$next_steps = $entry->next_steps ?? '';
$note_id = $entry->id ?? 0;

// Obtener attachments existentes
$attachments = $is_edit
        ? \Openmind\Repositories\AttachmentRepository::getByEntry('session_note', $note_id)
        : [];

$mood_options = [
        '' => 'Seleccionar...',
        'feliz' => '😊 Feliz',
        'triste' => '😢 Triste',
        'ansioso' => '😰 Ansioso/a',
        'neutral' => '😐 Neutral',
        'enojado' => '😠 Enojado/a',
        'calmado' => '😌 Calmado/a'
];
?>

<style>
    /* Fix para el editor dentro del dashboard */
    .wp-editor-wrap {
        border-radius: 8px;
        overflow: hidden;
    }

    .wp-editor-container {
        border: none !important;
    }

    .mce-toolbar-grp {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    .mce-ico {
        color: #374151 !important;
    }

    .mce-btn:hover {
        background: #e5e7eb !important;
    }
</style>

<form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
    <?php wp_nonce_field($nonce_action, 'openmind_session_note_nonce'); ?>
    <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
    <input type="hidden" name="return" value="<?php echo esc_attr($return); ?>">
    <?php if ($is_edit): ?>
        <input type="hidden" name="note_id" value="<?php echo $note_id; ?>">
    <?php endif; ?>

    <!-- Info del paciente -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <p class="text-sm text-gray-600 m-0">
            <strong>Paciente:</strong> <?php echo esc_html($patient_name); ?>
        </p>
        <?php if ($is_edit): ?>
            <p class="text-sm text-gray-600 m-0 mt-1">
                <strong>Sesión:</strong> #<?php echo $entry->session_number; ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Contenido -->
    <div class="mb-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            Contenido de la sesión *
        </label>
        <div class="border border-gray-300 rounded-lg overflow-hidden">
            <?php
            $editor_settings = [
                    'textarea_name' => 'content',
                    'textarea_rows' => 15,
                    'media_buttons' => true,
                    'teeny' => false,
                    'quicktags' => true,
                    'tinymce' => [
                            'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv',
                            'toolbar2' => 'forecolor,backcolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                            'content_css' => OPENMIND_URL . 'assets/css/editor-style.css',
                            'height' => 400,
                            'menubar' => false,
                            'statusbar' => true,
                            'resize' => true,
                            'branding' => false,
                            'elementpath' => false
                    ]
            ];
            wp_editor($content, 'session_content', $editor_settings);
            ?>
        </div>
        <p class="text-xs text-gray-500 mt-2">Describe lo trabajado en la sesión, observaciones y avances del paciente.</p>
    </div>

    <!-- Estado anímico -->
    <?php
    $mood_args = [
            'name' => 'mood',
            'selected' => $mood,
            'label' => 'Estado anímico observado del paciente',
            'color' => 'primary',
            'required' => false
    ];
    include OPENMIND_PATH . 'templates/components/mood-selector.php';
    ?>


    <!-- Imágenes -->
    <div class="mb-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            Adjuntar imágenes (máximo 5)
        </label>

        <!-- Imágenes existentes -->
        <?php if (!empty($attachments)): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-4">
                <?php foreach ($attachments as $att): ?>
                    <div class="relative group">
                        <img src="<?php echo esc_url($att->file_path); ?>"
                             alt="Adjunto"
                             class="w-full h-32 object-cover rounded-lg">
                        <button type="button"
                                class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                data-attachment-id="<?php echo $att->id; ?>"
                                onclick="deleteAttachment(<?php echo $att->id; ?>, this)">
                            ×
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Upload nuevo -->
        <input type="file"
               name="attachments[]"
               accept="image/jpeg,image/png,image/webp"
               multiple
               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
        <p class="text-xs text-gray-500 mt-2">JPG, PNG o WebP. Máximo 5MB por imagen.</p>
    </div>

    <!-- Botones -->
    <div class="flex gap-3 justify-end pt-6 border-t">
        <a href="<?php echo $return === 'detalle'
                ? add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))
                : add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')); ?>"
           class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium transition-all hover:bg-gray-50 no-underline">
            Cancelar
        </a>
        <button type="submit"
                class="px-6 py-2.5 bg-primary-500 text-white rounded-lg text-sm font-semibold transition-all hover:bg-primary-600 shadow-sm hover:shadow-md">
            <?php echo $is_edit ? 'Actualizar bitácora' : 'Guardar bitácora'; ?>
        </button>
    </div>
</form>

<script>
    function deleteAttachment(attachmentId, button) {
        if (!confirm('¿Eliminar esta imagen?')) return;

        fetch(openmindData.ajaxUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'openmind_delete_attachment',
                nonce: openmindData.nonce,
                attachment_id: attachmentId
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    button.closest('.relative').remove();
                } else {
                    alert(data.data.message || 'Error al eliminar');
                }
            });
    }
</script>