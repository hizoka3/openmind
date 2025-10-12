<?php
/**
 * Template: Detalle de Actividad Asignada (Psicólogo)
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
if (!current_user_can('manage_patients')) wp_die('Acceso denegado');

$assignment_id = isset($_GET['activity_id']) ? absint($_GET['activity_id']) : 0;
$assignment = get_post($assignment_id);

if (!$assignment || $assignment->post_type !== 'activity_assignment') {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Actividad no encontrada
    </div>';
    return;
}

// Verificar que la actividad fue asignada por este psicólogo
$psychologist_id = get_post_meta($assignment_id, 'psychologist_id', true);
if ($psychologist_id != $user_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-lock mr-2"></i>
        Acceso denegado
    </div>';
    return;
}

// Datos de la asignación
$activity_id = $assignment->post_parent;
$activity = get_post($activity_id);
$patient_id = get_post_meta($assignment_id, 'patient_id', true);
$patient = get_userdata($patient_id);
$status = get_post_meta($assignment_id, 'status', true);
$due_date = get_post_meta($assignment_id, 'due_date', true);
$completed_at = get_post_meta($assignment_id, 'completed_at', true);

// Datos del recurso original
$activity_type = get_post_meta($activity_id, '_activity_type', true);
$activity_file = get_post_meta($activity_id, '_activity_file', true);
$activity_url = get_post_meta($activity_id, '_activity_url', true);

// Respuestas del paciente
$responses = get_comments([
    'post_id' => $assignment_id,
    'type' => 'activity_response',
    'status' => 'approve',
    'orderby' => 'comment_date',
    'order' => 'ASC'
]);

$psychologist_responses = get_comments([
    'post_id' => $assignment_id,
    'type' => 'psy_response', // 👈 ACTUALIZADO
    'status' => 'approve',
    'orderby' => 'comment_date',
    'order' => 'ASC'
]);

// Combinar y ordenar por fecha
$all_responses = array_merge($responses, $psychologist_responses);
usort($all_responses, function($a, $b) {
    return strtotime($a->comment_date) - strtotime($b->comment_date);
});
?>

<div class="max-w-4xl mx-auto">

    <!-- Navegación -->
    <div class="mb-6">
        <a href="<?php echo esc_url(add_query_arg(['view' => 'paciente-detalle', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))); ?>"
           class="inline-flex items-center gap-2 text-primary-500 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a <?php echo esc_html($patient->display_name); ?>
        </a>
    </div>

    <!-- Cabecera -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <img id="avatar-preview"
                     src="<?php echo esc_url(get_avatar_url($patient->ID, ['size' => 32])); ?>"
                     alt="Avatar del paciente"
                     class="w-12 h-12 rounded-full object-cover">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 m-0"><?php echo esc_html($assignment->post_title); ?></h1>
                    <p class="text-sm text-gray-600 m-0">Asignada a <?php echo esc_html($patient->display_name); ?></p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold <?php echo $status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'; ?>">
                <i class="fa-solid fa-<?php echo $status === 'completed' ? 'check' : 'clock'; ?>"></i>
                <?php echo $status === 'completed' ? 'Completada' : 'Pendiente'; ?>
            </span>

            <?php if ($due_date): ?>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-700">
                    <i class="fa-solid fa-calendar"></i>
                    Fecha límite: <?php echo date('d/m/Y', strtotime($due_date)); ?>
                </span>
            <?php endif; ?>

            <?php if ($completed_at): ?>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                    <i class="fa-solid fa-check-circle"></i>
                    Completada: <?php echo date('d/m/Y', strtotime($completed_at)); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tu mensaje al paciente -->
    <?php if ($assignment->post_content): ?>
        <div class="bg-blue-50 rounded-xl border-l-4 border-blue-500 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-comment-dots text-blue-600"></i>
                Tu mensaje al paciente
            </h3>
            <div class="prose max-w-none text-gray-700">
                <?php echo wp_kses_post($assignment->post_content); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recurso original de biblioteca -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-book text-purple-600"></i>
            Recurso de biblioteca: <?php echo esc_html($activity->post_title); ?>
        </h3>

        <?php if ($activity->post_content): ?>
            <div class="prose max-w-none text-gray-600 mb-4">
                <?php echo wp_kses_post($activity->post_content); ?>
            </div>
        <?php endif; ?>

        <div class="pt-4 border-t border-gray-200">
            <?php
            switch($activity_type):
                case 'pdf':
                    $file_url = wp_get_attachment_url($activity_file);
                    ?>
                    <a href="<?php echo esc_url($file_url); ?>"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors no-underline"
                       download target="_blank">
                        <i class="fa-solid fa-file-pdf"></i>
                        Descargar PDF
                    </a>
                    <?php
                    break;

                case 'video':
                    $file_url = wp_get_attachment_url($activity_file);
                    ?>
                    <video controls class="w-full rounded-lg">
                        <source src="<?php echo esc_url($file_url); ?>" type="video/mp4">
                        Tu navegador no soporta video HTML5.
                    </video>
                    <?php
                    break;

                case 'youtube':
                    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $activity_url, $matches);
                    $youtube_id = $matches[1] ?? '';
                    if ($youtube_id):
                        ?>
                        <div class="relative pb-[56.25%] h-0 overflow-hidden rounded-lg">
                            <iframe class="absolute top-0 left-0 w-full h-full"
                                    src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                            </iframe>
                        </div>
                    <?php endif;
                    break;

                case 'link':
                    ?>
                    <a href="<?php echo esc_url($activity_url); ?>"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors no-underline"
                       target="_blank" rel="noopener">
                        <i class="fa-solid fa-external-link-alt"></i>
                        Abrir recurso externo
                    </a>
                    <?php
                    break;
            endswitch;
            ?>
        </div>
    </div>

    <!-- Respuestas del paciente y psicólogo -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-comments text-green-600"></i>
            Conversación sobre la actividad (<?php echo count($all_responses); ?>)
        </h3>

        <?php if (empty($all_responses)): ?>
            <div class="text-center py-12">
                <p class="text-gray-600">Aún no hay respuestas en esta actividad</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach($all_responses as $response):
                    $is_patient = $response->comment_type === 'activity_response';
                    $author = get_userdata($response->user_id);
                    ?>
                    <div class="border-l-4 <?php echo $is_patient ? 'border-green-500 bg-green-50' : 'border-blue-500 bg-blue-50'; ?> rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <img id="avatar-preview"
                                     src="<?php echo esc_url(get_avatar_url($response->user_id, ['size' => 32])); ?>"
                                     alt="Avatar"
                                     class="w-8 h-8 rounded-full object-cover">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 m-0">
                                        <?php echo esc_html($author->display_name); ?>
                                        <?php if (!$is_patient): ?>
                                            <span class="text-xs font-normal text-blue-600">(Psicólogo)</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-600 m-0">
                                        <i class="fa-solid fa-clock mr-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($response->comment_date)); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="prose max-w-none text-gray-700">
                            <?php echo wp_kses_post($response->comment_content); ?>
                        </div>

                        <?php
                        $files = get_comment_meta($response->comment_ID, '_response_files', true);
                        if ($files && is_array($files)):
                            ?>
                            <div class="mt-4 pt-4 border-t <?php echo $is_patient ? 'border-green-200' : 'border-blue-200'; ?>">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Archivos adjuntos:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach($files as $file_id): ?>
                                        <a href="<?php echo esc_url(wp_get_attachment_url($file_id)); ?>"
                                           target="_blank"
                                           class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border <?php echo $is_patient ? 'border-green-300' : 'border-blue-300'; ?> rounded-lg text-sm text-gray-700 <?php echo $is_patient ? 'hover:bg-green-100' : 'hover:bg-blue-100'; ?> transition-colors no-underline">
                                            <i class="fa-solid fa-paperclip"></i>
                                            <?php echo esc_html(basename(get_attached_file($file_id))); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Formulario comentario del psicólogo -->
    <div class="bg-gradient-to-r from-primary-50 to-blue-50 rounded-xl shadow-sm border border-primary-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-reply text-primary-600"></i>
            Responder al paciente
        </h3>

        <form id="psychologist-response-form" enctype="multipart/form-data">
            <?php wp_nonce_field('submit_psychologist_response', 'response_nonce'); ?>
            <input type="hidden" name="assignment_id" value="<?php echo esc_attr($assignment_id); ?>">
            <input type="hidden" name="patient_id" value="<?php echo esc_attr($patient_id); ?>">

            <div class="mb-4">
                <label for="psychologist_response" class="block text-sm font-medium text-gray-700 mb-2">
                    Tu comentario
                </label>
                <?php
                wp_editor('', 'psychologist_response', [
                    'textarea_name' => 'psychologist_response',
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'teeny' => true,
                    'quicktags' => false,
                    'editor_class' => 'psychologist-response-editor'
                ]);
                ?>
            </div>

            <div class="mb-4">
                <label for="response_files" class="block text-sm font-medium text-gray-700 mb-2">
                    Archivos adjuntos (opcional, máx. 3)
                </label>
                <input type="file"
                       name="response_files[]"
                       id="response_files"
                       multiple
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                       max="3"
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none p-2">
                <p class="mt-1 text-xs text-gray-500">Formatos: PDF, DOC, DOCX, JPG, PNG, GIF</p>
            </div>

            <button type="submit"
                    class="w-full px-6 py-3 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors"
                    id="submit-response">
                <i class="fa-solid fa-paper-plane mr-2"></i>
                Enviar Comentario
            </button>
        </form>
    </div>

    <script>
        const OpenmindPsychologistResponse = {
            init() {
                this.bindEvents();
            },

            bindEvents() {
                const form = document.getElementById('psychologist-response-form');
                if (form) {
                    form.addEventListener('submit', (e) => this.handleSubmit(e));
                }
            },

            async handleSubmit(e) {
                e.preventDefault();

                const form = e.target;
                const submitBtn = form.querySelector('#submit-response');

                // Obtener contenido del editor TinyMCE
                let content = '';
                if (typeof tinymce !== 'undefined' && tinymce.get('psychologist_response')) {
                    content = tinymce.get('psychologist_response').getContent();
                } else {
                    content = document.getElementById('psychologist_response').value;
                }

                // Validar que haya contenido
                if (!content || content.trim() === '' || content === '<p><br data-mce-bogus="1"></p>') {
                    Toast.show('Por favor escribe un comentario', 'error');
                    return;
                }

                const formData = new FormData(form);

                // Asegurarnos de que el contenido del editor se incluya
                formData.set('psychologist_response', content);
                formData.append('action', 'openmind_psychologist_response');

                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Enviando...';

                try {
                    const response = await fetch(openmindData.ajaxUrl, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        Toast.show(data.data.message, 'success');

                        // Limpiar editor
                        if (typeof tinymce !== 'undefined' && tinymce.get('psychologist_response')) {
                            tinymce.get('psychologist_response').setContent('');
                        } else {
                            document.getElementById('psychologist_response').value = '';
                        }

                        // Limpiar archivos
                        document.getElementById('response_files').value = '';

                        // Recargar después de 1.5 segundos
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Toast.show(data.data?.message || 'Error al enviar comentario', 'error');
                    }
                } catch (error) {
                    console.error(error);
                    Toast.show('Error de conexión', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        };

        document.addEventListener('DOMContentLoaded', () => OpenmindPsychologistResponse.init());
    </script>