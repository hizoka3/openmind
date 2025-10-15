<?php // templates/pages/patient/actividad-detalle.php
/**
 * Template: Detalle de Actividad (Paciente)
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

$patient_id = get_post_meta($assignment_id, 'patient_id', true);
if ($patient_id != $user_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-lock mr-2"></i>
        Acceso denegado
    </div>';
    return;
}

// ✅ ACTUALIZADO - Usar getActivityData con recursos múltiples
$activity_data = \Openmind\Controllers\ActivityController::getActivityData($assignment_id);

if (!$activity_data) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Error al cargar datos de la actividad
    </div>';
    return;
}

$activity = $activity_data['library'];
$psychologist_id = $activity_data['psychologist_id'];
$psychologist = get_userdata($psychologist_id);
$status = $activity_data['status'];
$due_date = $activity_data['due_date'];
$completed_at = $activity_data['completed_at'];
$resources = $activity_data['resources']; // ✅ NUEVO

// Obtener TODAS las respuestas (incluye ocultas)
$all_responses = get_comments([
        'post_id' => $assignment_id,
        'type' => ['activity_response', 'psy_response'],
        'status' => ['approve', 'hidden'],
        'orderby' => 'comment_date',
        'order' => 'DESC'
]);

// Filtrar: Mostrar activas + propias ocultas
$responses = array_filter($all_responses, function($r) use ($user_id) {
    if ($r->comment_approved === '1') return true;
    if ($r->comment_approved === 'hidden' && $r->user_id == $user_id) return true;
    return false;
});
?>

<div class="max-w-4xl mx-auto">

    <?php
    // Header
    $header_args = [
            'assignment' => $assignment,
            'user' => $psychologist,
            'status' => $status,
            'due_date' => $due_date,
            'completed_at' => $completed_at,
            'back_url' => add_query_arg('view', 'actividades', home_url('/dashboard-paciente/')),
            'back_text' => 'Volver a Actividades'
    ];
    include OPENMIND_PATH . 'templates/components/activity/header.php';
    ?>

    <?php if ($assignment->post_content): ?>
        <div class="bg-primary-50 rounded-xl border-l-4 border-primary-500 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                Mensaje del psicólogo
            </h3>
            <div class="prose max-w-none text-gray-700">
                <?php echo wp_kses_post($assignment->post_content); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // ✅ ACTUALIZADO - Usar nuevo componente de recursos múltiples
    if (!empty($resources)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-book-open text-primary-600"></i>
                Recursos de la actividad
            </h3>
            <?php
            $resources_args = ['resources' => $resources];
            include OPENMIND_PATH . 'templates/components/activity-resources.php';
            ?>
        </div>
    <?php endif; ?>

    <?php
    // Response Form PRIMERO (acordeón cerrado)
    $form_args = [
            'assignment_id' => $assignment_id,
            'form_id' => 'activity-response-form',
            'editor_id' => 'response_content',
            'button_text' => 'Enviar Respuesta',
            'title' => 'Responder',
            'icon' => 'fa-reply'
    ];
    include OPENMIND_PATH . 'templates/components/activity/response-form.php';
    ?>

    <?php if ($responses): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                Conversación (<?php echo count($responses); ?>)
            </h3>

            <div class="space-y-4">
                <?php foreach($responses as $response):
                    $response_args = [
                            'response' => $response,
                            'is_patient_response' => $response->comment_type === 'activity_response',
                            'show_actions' => $response->comment_type === 'activity_response',
                            'current_user_id' => $user_id
                    ];
                    include OPENMIND_PATH . 'templates/components/activity/response-item.php';
                endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>