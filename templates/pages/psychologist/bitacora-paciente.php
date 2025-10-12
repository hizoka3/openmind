<?php
// templates/pages/psychologist/bitacora-paciente.php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_patients')) {
    wp_die('Acceso denegado');
}

$patient_id = intval($_GET['patient_id'] ?? 0);
$patient = get_userdata($patient_id);
$psychologist_id = get_current_user_id();

// Verificar que el paciente existe y pertenece al psicólogo
$patient_psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);
if (!$patient || $patient_psychologist_id != $psychologist_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Paciente no encontrado o no tienes permisos para verlo.
    </div>';
    return;
}

// Paginación
$per_page = 10;
$current_page = max(1, intval($_GET['paged'] ?? 1));
$offset = ($current_page - 1) * $per_page;

// Obtener bitácoras
$entries = \Openmind\Repositories\SessionNoteRepository::getByPatient($patient_id, $per_page, $offset);
$total_entries = \Openmind\Repositories\SessionNoteRepository::countByPatient($patient_id);

// Stats
$all_activities = get_posts([
        'post_type' => 'activity',
        'meta_query' => [['key' => 'assigned_to', 'value' => $patient_id, 'compare' => '=']],
        'posts_per_page' => -1,
        'fields' => 'ids'
]);

$completed_activities = count(array_filter($all_activities, function($id) {
    return get_post_meta($id, 'completed', true) == 1;
}));

$base_url = add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'));
?>

<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="?view=bitacora" class="inline-flex items-center gap-2 text-primary-500 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Bitácora
        </a>
    </div>

    <!-- Patient Header -->
    <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex gap-6 items-start">
                <img id="avatar-preview"
                     src="<?php echo esc_url(get_avatar_url($patient->ID, ['size' => 80])); ?>"
                     alt="Avatar del paciente"
                     class="w-20 h-20 rounded-full object-cover border-4 border-gray-100">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
                        Bitácora de <?php echo esc_html($patient->display_name); ?>
                    </h1>
                    <p class="text-gray-500 m-0"><?php echo esc_html($patient->user_email); ?></p>
                </div>
            </div>

            <a href="<?php echo add_query_arg(['view' => 'bitacora-nueva', 'patient_id' => $patient_id, 'return' => 'detalle'], home_url('/dashboard-psicologo/')); ?>"
               class="inline-flex items-center gap-2 px-5 py-3 bg-primary-500 text-white rounded-xl text-sm font-semibold transition-all hover:bg-primary-600 shadow-sm hover:shadow-md no-underline">
                <i class="fa-solid fa-plus"></i>
                Nueva entrada
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-100">
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600"><?php echo $total_entries; ?></div>
                <div class="text-sm text-gray-500 mt-1">Sesiones registradas</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600"><?php echo $completed_activities; ?></div>
                <div class="text-sm text-gray-500 mt-1">Actividades completadas</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600"><?php echo count($all_activities); ?></div>
                <div class="text-sm text-gray-500 mt-1">Actividades totales</div>
            </div>
        </div>
    </div>

    <!-- Lista de bitácoras -->
    <?php
    $args = [
            'patient_id' => $patient_id,
            'entries' => $entries,
            'total' => $total_entries,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'show_actions' => true,
            'context' => 'psychologist',
            'base_url' => $base_url
    ];
    include OPENMIND_PATH . 'templates/components/bitacora-list.php';
    ?>
</div>