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

<div class="max-w-4xl mx-auto">
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="bitacora-form">
        <input type="hidden" name="action" value="<?php echo $ajax_action; ?>">
        <?php wp_nonce_field($nonce_action, 'openmind_diary_nonce'); ?>
        <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
        <input type="hidden" name="return" value="<?php echo $return; ?>">
        <?php if ($entry): ?>
            <input type="hidden" name="entry_id" value="<?php echo $entry->id; ?>">
        <?php endif; ?>

        <!-- Patient Info -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fa-solid fa-user text-blue-600 mr-3 text-xl"></i>
                <div>
                    <p class="text-sm font-medium text-blue-800 m-0">Bitácora para:</p>
                    <p class="text-base font-semibold text-blue-900 m-0"><?php echo esc_html($patient_name); ?></p>
                </div>
            </div>
        </div>

        <!-- Mood Selector -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="fa-solid fa-face-smile mr-2"></i>
                Estado de ánimo observado
            </label>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                <?php foreach ($mood_options as $value => $mood): ?>
                    <label class="cursor-pointer">
                        <input type="radio"
                               name="mood"
                               value="<?php echo $value; ?>"
                               class="peer sr-only"
                            <?php echo ($entry && $entry->mood === $value) ? 'checked' : ''; ?>>
                        <div class="flex flex-col items-center gap-2 p-3 border-2 border-gray-200 rounded-lg transition-all peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:border-gray-300">
                            <span class="text-3xl"><?php echo $mood['emoji']; ?></span>
                            <span class="text-xs font-medium text-gray-700 peer-checked:text-primary-700">
                                <?php echo $mood['label']; ?>
                            </span>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- WYSIWYG Editor -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="fa-solid fa-pen-to-square mr-2"></i>
                Contenido de la sesión <span class="text-red-500">*</span>
            </label>
            <div class="bg-white border border-gray-300 rounded-lg overflow-hidden">
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
            <p class="text-xs text-gray-500 mt-2">
                <i class="fa-solid fa-info-circle mr-1"></i>
                Esta información será visible para el paciente en su bitácora
            </p>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
            <a href="<?php echo $return === 'detalle'
                ? add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))
                : add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')); ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg border-0 text-sm font-medium transition-all hover:bg-gray-300 no-underline">
                <i class="fa-solid fa-xmark"></i>
                Cancelar
            </a>

            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-500 text-white rounded-lg border-0 cursor-pointer text-sm font-medium transition-all hover:bg-primary-600 hover:-translate-y-0.5 hover:shadow-lg">
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