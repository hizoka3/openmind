<?php
/**
 * Formulario para crear nueva entrada de diario personal
 * URL: ?view=diario-nuevo
 */

if (!defined('ABSPATH')) exit;

if (!current_user_can('write_diary')) {
    wp_die('Acceso denegado');
}

$user_id = get_current_user_id();
$psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;
?>

<div class="max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="<?php echo add_query_arg('view', 'diario', home_url('/dashboard-paciente/')); ?>"
           class="inline-flex items-center gap-2 text-purple-600 text-sm font-medium transition-colors hover:text-purple-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Diario
        </a>
    </div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
            <i class="fa-solid fa-pen-to-square mr-3 text-purple-500"></i>
            Nueva Entrada de Diario
        </h1>
        <p class="text-gray-600 m-0">
            Registra tus pensamientos y emociones del día
        </p>
    </div>

    <div class="bg-white rounded-2xl p-8 shadow-sm">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="diario-form">
            <input type="hidden" name="action" value="openmind_save_patient_diary">
            <?php wp_nonce_field('save_patient_diary', 'openmind_diary_nonce'); ?>

            <!-- Info Box -->
            <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-6 rounded-lg">
                <div class="flex items-center">
                    <i class="fa-solid fa-lock text-purple-600 mr-3 text-xl"></i>
                    <div>
                        <p class="text-sm font-medium text-purple-800 m-0">
                            <strong>Esta entrada será privada por defecto.</strong>
                        </p>
                        <p class="text-sm text-purple-700 m-0 mt-1">
                            Podrás compartirla con <?php echo $psychologist ? esc_html($psychologist->display_name) : 'tu psicólogo'; ?> después si lo deseas.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mood Selector -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <i class="fa-solid fa-face-smile mr-2"></i>
                    ¿Cómo te sientes hoy?
                </label>
                <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                    <?php
                    $mood_options = [
                        'feliz' => ['emoji' => '😊', 'label' => 'Feliz'],
                        'triste' => ['emoji' => '😢', 'label' => 'Triste'],
                        'ansioso' => ['emoji' => '😰', 'label' => 'Ansioso'],
                        'neutral' => ['emoji' => '😐', 'label' => 'Neutral'],
                        'enojado' => ['emoji' => '😠', 'label' => 'Enojado'],
                        'calmado' => ['emoji' => '😌', 'label' => 'Calmado']
                    ];

                    foreach ($mood_options as $value => $mood): ?>
                        <label class="cursor-pointer">
                            <input type="radio"
                                   name="mood"
                                   value="<?php echo $value; ?>"
                                   class="peer sr-only">
                            <div class="flex flex-col items-center gap-2 p-3 border-2 border-gray-200 rounded-lg transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300">
                                <span class="text-3xl"><?php echo $mood['emoji']; ?></span>
                                <span class="text-xs font-medium text-gray-700 peer-checked:text-purple-700">
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
                    <i class="fa-solid fa-pen mr-2"></i>
                    Escribe tus pensamientos <span class="text-red-500">*</span>
                </label>
                <div class="bg-white border border-gray-300 rounded-lg overflow-hidden">
                    <?php
                    wp_editor(
                        '',
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
                    Esta entrada será privada. Solo tú podrás verla a menos que decidas compartirla.
                </p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <a href="<?php echo add_query_arg('view', 'diario', home_url('/dashboard-paciente/')); ?>"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg border-0 text-sm font-medium transition-all hover:bg-gray-300 shadow-none no-underline">
                    <i class="fa-solid fa-xmark"></i>
                    Cancelar
                </a>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg border-0 cursor-pointer text-sm font-semibold transition-all hover:from-purple-600 hover:to-pink-600 hover:-translate-y-0.5 hover:shadow-lg shadow-none">
                    <i class="fa-solid fa-save"></i>
                    Guardar Entrada
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('diario-form');

        form.addEventListener('submit', function(e) {
            const content = tinymce.get('diary_content').getContent();

            if (!content.trim()) {
                e.preventDefault();
                alert('Por favor escribe algo en tu entrada de diario');
                return false;
            }
        });
    });
</script>