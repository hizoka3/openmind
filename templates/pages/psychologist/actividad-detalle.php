<?php // templates/pages/psychologist/actividad-detalle.php
/**
 * Template: Detalle de Actividad (Psicólogo)
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

$psychologist_id = get_post_meta($assignment_id, 'psychologist_id', true);
if ($psychologist_id != $user_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-lock mr-2"></i>
        Acceso denegado
    </div>';
    return;
}

// Cargar datos
$activity_id = $assignment->post_parent;
$activity = get_post($activity_id);
$patient_id = get_post_meta($assignment_id, 'patient_id', true);
$patient = get_userdata($patient_id);
$status = get_post_meta($assignment_id, 'status', true);
$due_date = get_post_meta($assignment_id, 'due_date', true);
$completed_at = get_post_meta($assignment_id, 'completed_at', true);

$activity_type = get_post_meta($activity_id, '_activity_type', true);
$activity_file = get_post_meta($activity_id, '_activity_file', true);
$activity_url = get_post_meta($activity_id, '_activity_url', true);

// Obtener TODAS las respuestas (paciente + psicólogo) - incluye ocultas
$responses = get_comments([
        'post_id' => $assignment_id,
        'type' => ['activity_response', 'psy_response'],
        'status' => ['approve', 'hidden'], // Psicólogo ve TODO
        'orderby' => 'comment_date',
        'order' => 'ASC'
]);
?>

    <div class="max-w-4xl mx-auto">

        <?php
        // Header
        $header_args = [
                'assignment' => $assignment,
                'user' => $patient,
                'status' => $status,
                'due_date' => $due_date,
                'completed_at' => $completed_at,
                'back_url' => add_query_arg(['view' => 'paciente-detalle', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')),
                'back_text' => 'Volver a ' . $patient->display_name
        ];
        include OPENMIND_PATH . 'templates/components/activity/header.php';
        ?>

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

        <?php
        // Resource Viewer
        $resource_args = [
                'activity' => $activity,
                'activity_type' => $activity_type,
                'activity_file' => $activity_file,
                'activity_url' => $activity_url
        ];
        include OPENMIND_PATH . 'templates/components/activity/resource-viewer.php';
        ?>

        <?php if ($responses): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-comments text-green-600"></i>
                    Conversación sobre la actividad (<?php echo count($responses); ?>)
                </h3>

                <div class="space-y-6">
                    <?php foreach($responses as $response):
                        $response_args = [
                                'response' => $response,
                                'is_patient_response' => $response->comment_type === 'activity_response',
                                'show_actions' => false
                        ];
                        include OPENMIND_PATH . 'templates/components/activity/response-item.php';
                    endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 text-center py-12">
                <p class="text-gray-600">Aún no hay respuestas en esta actividad</p>
            </div>
        <?php endif; ?>

        <?php
        // Response Form Psicólogo
        $form_args = [
                'assignment_id' => $assignment_id,
                'form_id' => 'psychologist-response-form',
                'editor_id' => 'psychologist_response',
                'button_text' => 'Enviar Comentario',
                'title' => 'Responder al paciente',
                'icon' => 'fa-reply',
                'bg_class' => 'bg-gradient-to-r from-primary-50 to-blue-50',
                'extra_fields' => ['patient_id' => $patient_id]
        ];
        include OPENMIND_PATH . 'templates/components/activity/response-form.php';
        ?>

    </div>

<?php
// Incluir modales al final (psicólogo no los necesita, pero los incluimos por si acaso)
include OPENMIND_PATH . 'templates/components/modal-edit-response.php';
include OPENMIND_PATH . 'templates/components/modal-hide-response.php';
?>