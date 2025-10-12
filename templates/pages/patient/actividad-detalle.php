<?php
/**
 * Template: Detalle de Actividad Asignada (Paciente)
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
if (!current_user_can('patient')) wp_die('Acceso denegado');

$assignment_id = isset($_GET['activity_id']) ? absint($_GET['activity_id']) : 0;
$assignment = get_post($assignment_id);

if (!$assignment || $assignment->post_type !== 'activity_assignment') {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Actividad no encontrada
    </div>';
    return;
}

// Verificar que la actividad pertenece al paciente
$patient_id = get_post_meta($assignment_id, 'patient_id', true);
if ($patient_id != $user_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-lock mr-2"></i>
        Acceso denegado
    </div>';
    return;
}

// Datos de la asignación
$activity_id = $assignment->post_parent;
$activity = get_post($activity_id);
$psychologist_id = get_post_meta($assignment_id, 'psychologist_id', true);
$psychologist = get_userdata($psychologist_id);
$status = get_post_meta($assignment_id, 'status', true);
$due_date = get_post_meta($assignment_id, 'due_date', true);
$completed_at = get_post_meta($assignment_id, 'completed_at', true);

// Datos del recurso original
$activity_type = get_post_meta($activity_id, '_activity_type', true);
$activity_file = get_post_meta($activity_id, '_activity_file', true);
$activity_url = get_post_meta($activity_id, '_activity_url', true);

// Respuestas previas
$responses = get_comments([
        'post_id' => $assignment_id,
        'type' => 'activity_response',
        'status' => 'approve',
        'orderby' => 'comment_date',
        'order' => 'DESC'
]);
?>

<div class="max-w-4xl mx-auto">

    <!-- Navegación -->
    <div class="mb-6">
        <a href="<?php echo esc_url(add_query_arg('view', 'actividades', home_url('/dashboard-paciente/'))); ?>"
           class="inline-flex items-center gap-2 text-primary-500 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Actividades
        </a>
    </div>

    <!-- Cabecera -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo esc_html($assignment->post_title); ?></h1>

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

            <div class="flex items-center gap-2">
                <?php echo get_avatar($psychologist->ID, 24, '', '', ['class' => 'rounded-full']); ?>
                <span class="text-sm text-gray-600">Asignada por: <strong><?php echo esc_html($psychologist->display_name); ?></strong></span>
            </div>
        </div>
    </div>

    <!-- Descripción del psicólogo -->
    <?php if ($assignment->post_content): ?>
        <div class="bg-primary-50 rounded-xl border-l-4 border-primary-500 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-message text-primary-600"></i>
                Mensaje del psicólogo
            </h3>
            <div class="prose max-w-none text-gray-700">
                <?php echo wp_kses_post($assignment->post_content); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recurso original -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-book-open text-blue-600"></i>
            Recurso: <?php echo esc_html($activity->post_title); ?>
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

    <!-- Formulario de respuesta -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-pen text-green-600"></i>
            Tu respuesta
        </h3>

        <form id="activity-response-form" enctype="multipart/form-data">
            <?php wp_nonce_field('submit_activity_response', 'response_nonce'); ?>
            <input type="hidden" name="assignment_id" value="<?php echo esc_attr($assignment_id); ?>">
            <input type="hidden" name="response_id" value="0" id="response_id">

            <div class="mb-4">
                <?php
                wp_editor('', 'response_content', [
                        'textarea_name' => 'response_content',
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
                <p class="mt-1 text-xs text-gray-500">Formatos: PDF, DOC, DOCX, JPG, PNG, GIF - Máximo 5 archivos</p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors"
                        id="submit-response">
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Enviar Respuesta
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

    <!-- Respuestas anteriores -->
    <?php if ($responses): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-comments text-purple-600"></i>
                Respuestas anteriores (<?php echo count($responses); ?>)
            </h3>

            <div class="space-y-4">
                <?php foreach($responses as $response): ?>
                    <div class="border-l-4 border-blue-500 bg-gray-50 rounded-lg p-4" data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
                        <div class="flex items-start justify-between mb-3">
                        <span class="text-sm text-gray-600">
                            <i class="fa-solid fa-clock mr-1"></i>
                            <?php echo date('d/m/Y H:i', strtotime($response->comment_date)); ?>
                        </span>
                            <div class="flex gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors"
                                        data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
                                    <i class="fa-solid fa-edit mr-1"></i>
                                    Editar
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800 text-sm font-medium transition-colors"
                                        data-response-id="<?php echo esc_attr($response->comment_ID); ?>">
                                    <i class="fa-solid fa-trash mr-1"></i>
                                    Eliminar
                                </button>
                            </div>
                        </div>

                        <div class="response-content prose max-w-none text-gray-700">
                            <?php echo wp_kses_post($response->comment_content); ?>
                        </div>

                        <?php
                        $files = get_comment_meta($response->comment_ID, '_response_files', true);
                        if ($files && is_array($files)):
                            ?>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Archivos adjuntos:</p>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach($files as $file_id): ?>
                                        <a href="<?php echo esc_url(wp_get_attachment_url($file_id)); ?>"
                                           target="_blank"
                                           class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors no-underline">
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
        </div>
    <?php endif; ?>

</div>

<script>
    const OpenmindActivityDetail = {
        init() {
            this.bindEvents();
        },

        bindEvents() {
            const form = document.getElementById('activity-response-form');
            if (form) {
                form.addEventListener('submit', (e) => this.handleSubmit(e));
            }

            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleEdit(e));
            });

            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleDelete(e));
            });

            const cancelBtn = document.getElementById('cancel-edit');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => this.cancelEdit());
            }
        },

        async handleSubmit(e) {
            e.preventDefault();

            const form = e.target;
            const submitBtn = form.querySelector('#submit-response');
            const formData = new FormData(form);

            formData.append('action', 'openmind_submit_response');

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
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Toast.show(data.data?.message || 'Error al enviar respuesta', 'error');
                }
            } catch (error) {
                console.error(error);
                Toast.show('Error de conexión', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        },

        handleEdit(e) {
            const responseId = e.currentTarget.dataset.responseId;
            const responseItem = document.querySelector(`[data-response-id="${responseId}"]`);
            const content = responseItem.querySelector('.response-content').innerHTML;

            if (typeof tinymce !== 'undefined') {
                tinymce.get('response_content').setContent(content);
            } else {
                document.getElementById('response_content').value = content;
            }

            document.getElementById('response_id').value = responseId;
            document.getElementById('submit-response').innerHTML = '<i class="fa-solid fa-save mr-2"></i>Actualizar Respuesta';
            document.getElementById('cancel-edit').style.display = 'inline-block';

            document.querySelector('.bg-white.rounded-xl.shadow-sm.border.border-gray-200.p-6.mb-6:has(#activity-response-form)').scrollIntoView({ behavior: 'smooth' });
        },

        async handleDelete(e) {
            if (!confirm('¿Estás seguro de eliminar esta respuesta?')) return;

            const responseId = e.currentTarget.dataset.responseId;

            try {
                const response = await fetch(openmindData.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'openmind_delete_response',
                        response_id: responseId,
                        nonce: openmindData.nonce
                    })
                });

                const data = await response.json();

                if (data.success) {
                    document.querySelector(`[data-response-id="${responseId}"]`).remove();
                    Toast.show('Respuesta eliminada exitosamente', 'success');
                } else {
                    Toast.show(data.data?.message || 'Error al eliminar respuesta', 'error');
                }
            } catch (error) {
                console.error(error);
                Toast.show('Error de conexión', 'error');
            }
        },

        cancelEdit() {
            document.getElementById('response_id').value = '0';
            document.getElementById('submit-response').innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i>Enviar Respuesta';
            document.getElementById('cancel-edit').style.display = 'none';

            if (typeof tinymce !== 'undefined') {
                tinymce.get('response_content').setContent('');
            } else {
                document.getElementById('response_content').value = '';
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => OpenmindActivityDetail.init());
</script>