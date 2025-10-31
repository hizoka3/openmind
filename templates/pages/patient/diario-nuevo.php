<?php // templates/pages/patient/diario-nuevo.php
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
           class="inline-flex items-center gap-2 text-primary-500 text-sm font-medium no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Diario
        </a>
    </div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-medium text-gray-900 m-0 mb-2">
            Nueva Entrada de Diario
        </h1>
        <p class="text-gray-600 m-0">
            Registra tus pensamientos y emociones del día
        </p>
    </div>

    <div class="bg-white rounded-2xl p-8 shadow-sm">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data" id="diario-form">
            <input type="hidden" name="action" value="openmind_save_patient_diary">
            <?php wp_nonce_field('save_patient_diary', 'openmind_diary_nonce'); ?>

            <!-- Info Box -->
            <div class="bg-primary-50 border-l-4 border-primary-500 p-4 mb-6 rounded-lg">
                <div class="flex items-center">
                    <i class="fa-solid fa-lock text-dark-gray-300 mr-3 text-xl"></i>
                    <div>
                        <p class="text-sm font-medium text-dark-gray-300 m-0">
                            <strong>Esta entrada será privada por defecto.</strong>
                        </p>
                        <p class="text-sm text-dark-gray-300 m-0 mt-1">
                            Podrás compartirla con <?php echo $psychologist ? esc_html($psychologist->display_name) : 'tu psicólogo'; ?> después si lo deseas.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mood Selector -->
            <?php
            $mood_args = [
                    'name' => 'mood',
                    'selected' => '',
                    'label' => '¿Cómo te sientes hoy?',
                    'required' => false
            ];
            include OPENMIND_PATH . 'templates/components/mood-selector.php';
            ?>

            <!-- WYSIWYG Editor -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <i class="fa-solid fa-pen mr-2"></i>
                    Escribe tus pensamientos <span class="text-red-500">*</span>
                </label>
                <div class="border border-gray-300 rounded-lg overflow-hidden">

                    <textarea name="content"
                              id="diary_content"
                              class="w-full p-4 text-gray-700 focus:outline-none"
                              rows="10"
                              required ></textarea>

                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    Esta entrada será privada. Solo tú podrás verla a menos que decidas compartirla.
                </p>
            </div>

            <!-- Adjuntar imágenes -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Adjuntar imágenes (máximo 5)
                </label>
                <input type="file"
                       name="attachments[]"
                       accept="image/jpeg,image/png,image/webp"
                       multiple
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-500 hover:file:bg-primary-100">
                <p class="text-xs text-gray-500 mt-2">JPG, PNG o WebP. Máximo 5MB por imagen.</p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <a href="<?php echo add_query_arg('view', 'diario', home_url('/dashboard-paciente/')); ?>"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg border-0 text-sm font-medium transition-all hover:bg-gray-300 shadow-none no-underline">
                    <i class="fa-solid fa-xmark"></i>
                    Cancelar
                </a>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-lg border-0 cursor-pointer text-sm font-semibold transition-all hover:-translate-y-0.5 hover:shadow-lg shadow-none">
                    <i class="fa-solid fa-save"></i>
                    Guardar Entrada
                </button>
            </div>
        </form>
    </div>
</div>

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