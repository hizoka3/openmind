<?php
/**
 * Componente formulario de bitácora
 *
 * @param array $args {
 *     @type int     $patient_id    ID del paciente
 *     @type string  $patient_name  Nombre del paciente
 *     @type object  $entry         Entrada a editar (opcional)
 *     @type string  $return        'lista' o 'detalle'
 *     @type string  $form_action   'create' o 'update'
 * }
 */

$patient_id = $args['patient_id'] ?? 0;
$patient_name = $args['patient_name'] ?? '';
$entry = $args['entry'] ?? null;
$return = $args['return'] ?? 'lista';
$form_action = $args['form_action'] ?? 'create';

$mood_options = [
    'feliz' => ['emoji' => '😊', 'label' => 'Feliz'],
    'triste' => ['emoji' => '😢', 'label' => 'Triste'],
    'ansioso' => ['emoji' => '😰', 'label' => 'Ansioso'],
    'neutral' => ['emoji' => '😐', 'label' => 'Neutral'],
    'enojado' => ['emoji' => '😠', 'label' => 'Enojado'],
    'calmado' => ['emoji' => '😌', 'label' => 'Calmado']
];

$ajax_action = $form_action === 'update' ? 'openmind_update_psychologist_diary' : 'openmind_save_psychologist_diary';
$nonce_action = $form_action === 'update' ? 'update_psychologist_diary' : 'save_psychologist_diary';
?>

<div class="tw-max-w-4xl tw-mx-auto">
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="bitacora-form">
        <input type="hidden" name="action" value="<?php echo $ajax_action; ?>">
        <?php wp_nonce_field($nonce_action, 'openmind_diary_nonce'); ?>
        <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
        <input type="hidden" name="return" value="<?php echo $return; ?>">
        <?php if ($entry): ?>
            <input type="hidden" name="entry_id" value="<?php echo $entry->id; ?>">
        <?php endif; ?>

        <!-- Patient Info -->
        <div class="tw-bg-blue-50 tw-border-l-4 tw-border-blue-400 tw-p-4 tw-mb-6 tw-rounded-lg">
            <div class="tw-flex tw-items-center">
                <i class="fa-solid fa-user tw-text-blue-600 tw-mr-3 tw-text-xl"></i>
                <div>
                    <p class="tw-text-sm tw-font-medium tw-text-blue-800 tw-m-0">Bitácora para:</p>
                    <p class="tw-text-base tw-font-semibold tw-text-blue-900 tw-m-0"><?php echo esc_html($patient_name); ?></p>
                </div>
            </div>
        </div>

        <!-- Mood Selector -->
        <div class="tw-mb-6">
            <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-3">
                <i class="fa-solid fa-face-smile tw-mr-2"></i>
                Estado de ánimo observado
            </label>
            <div class="tw-grid tw-grid-cols-3 md:tw-grid-cols-6 tw-gap-3">
                <?php foreach ($mood_options as $value => $mood): ?>
                    <label class="tw-cursor-pointer">
                        <input type="radio"
                               name="mood"
                               value="<?php echo $value; ?>"
                               class="tw-peer tw-sr-only"
                            <?php echo ($entry && $entry->mood === $value) ? 'checked' : ''; ?>>
                        <div class="tw-flex tw-flex-col tw-items-center tw-gap-2 tw-p-3 tw-border-2 tw-border-gray-200 tw-rounded-lg tw-transition-all peer-checked:tw-border-primary-500 peer-checked:tw-bg-primary-50 hover:tw-border-gray-300">
                            <span class="tw-text-3xl"><?php echo $mood['emoji']; ?></span>
                            <span class="tw-text-xs tw-font-medium tw-text-gray-700 peer-checked:tw-text-primary-700">
                                <?php echo $mood['label']; ?>
                            </span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- WYSIWYG Editor -->
        <div class="tw-mb-6">
            <label class="tw-block tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-3">
                <i class="fa-solid fa-pen-to-square tw-mr-2"></i>
                Contenido de la sesión <span class="tw-text-red-500">*</span>
            </label>
            <div class="tw-bg-white tw-border tw-border-gray-300 tw-rounded-lg tw-overflow-hidden">
                <?php
                wp_editor(
                    $entry ? $entry->content : '',
                    'diary_content',
                    [
                        'textarea_name' => 'content',
                        'media_buttons' => false,
                        'textarea_rows' => 12,
                        'teeny' => false,
                        'quicktags' => true,
                        'tinymce' => [
                            'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,link,blockquote,hr,removeformat',
                            'toolbar2' => '',
                        ]
                    ]
                );
                ?>
            </div>
            <p class="tw-text-xs tw-text-gray-500 tw-mt-2">
                <i class="fa-solid fa-info-circle tw-mr-1"></i>
                Esta información será visible para el paciente en su bitácora
            </p>
        </div>

        <!-- Form Actions -->
        <div class="tw-flex tw-justify-between tw-items-center tw-pt-6 tw-border-t tw-border-gray-200">
            <a href="<?php echo $return === 'detalle'
                ? add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))
                : add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')); ?>"
               class="tw-inline-flex tw-items-center tw-gap-2 tw-px-5 tw-py-2.5 tw-bg-gray-200 tw-text-gray-700 tw-rounded-lg tw-border-0 tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-gray-300 tw-no-underline">
                <i class="fa-solid fa-xmark"></i>
                Cancelar
            </a>

            <button type="submit"
                    class="tw-inline-flex tw-items-center tw-gap-2 tw-px-6 tw-py-2.5 tw-bg-primary-500 tw-text-white tw-rounded-lg tw-border-0 tw-cursor-pointer tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-primary-600 hover:tw--translate-y-0.5 hover:tw-shadow-lg">
                <i class="fa-solid fa-save"></i>
                <?php echo $form_action === 'update' ? 'Actualizar Entrada' : 'Guardar Entrada'; ?>
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('bitacora-form');

        form.addEventListener('submit', function(e) {
            const content = tinymce.get('diary_content').getContent();

            if (!content.trim()) {
                e.preventDefault();
                alert('Por favor escribe el contenido de la sesión');
                return false;
            }
        });
    });
</script>