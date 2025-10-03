<?php
/**
 * Vista completa de bitácoras de un paciente específico
 * URL: ?view=bitacora&patient_id=123
 */

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
$entries = \Openmind\Repositories\DiaryRepository::getPsychologistEntries($patient_id, $per_page, $offset);
$total_entries = \Openmind\Repositories\DiaryRepository::countPsychologistEntries($patient_id);

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
        <a href="?view=bitacora" class="inline-flex items-center gap-2 text-primary-600 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Bitácora
        </a>
    </div>

    <!-- Patient Header -->
    <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex gap-6 items-start">
                <?php echo get_avatar($patient->ID, 80, '', '', ['class' => 'rounded-2xl border-4 border-gray-100']); ?>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
                        <i class="fa-solid fa-book mr-3 text-primary-500"></i>
                        Bitácora de <?php echo esc_html($patient->display_name); ?>
                    </h1>
                    <p class="flex items-center gap-2 text-gray-600 text-sm my-1 m-0">
                        <i class="fa-solid fa-envelope"></i>
                        <?php echo esc_html($patient->user_email); ?>
                    </p>
                    <p class="flex items-center gap-2 text-gray-600 text-sm my-1 m-0">
                        <i class="fa-solid fa-calendar-check"></i>
                        Paciente desde <?php echo date('d/m/Y', strtotime($patient->user_registered)); ?>
                    </p>
                </div>
            </div>

            <a href="?view=bitacora-nueva&patient_id=<?php echo $patient_id; ?>&return=lista"
               class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white rounded-lg text-sm font-medium transition-all hover:bg-primary-600 hover:-translate-y-0.5 hover:shadow-lg no-underline">
                <i class="fa-solid fa-plus"></i>
                Nueva Entrada
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-book text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-blue-700 m-0">Total Sesiones</p>
                    <p class="text-3xl font-bold text-blue-900 m-0"><?php echo $total_entries; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-check-circle text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-green-700 m-0">Actividades Completadas</p>
                    <p class="text-3xl font-bold text-green-900 m-0"><?php echo $completed_activities; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-list text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-purple-700 m-0">Actividades Totales</p>
                    <p class="text-3xl font-bold text-purple-900 m-0"><?php echo count($all_activities); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Bitácoras -->
    <div class="bg-white rounded-2xl p-8 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 m-0">
                Registro de Sesiones
            </h2>
            <span class="text-sm text-gray-500">
                <i class="fa-solid fa-calendar-days mr-1"></i>
                <?php echo $total_entries; ?> entrada<?php echo $total_entries !== 1 ? 's' : ''; ?> total<?php echo $total_entries !== 1 ? 'es' : ''; ?>
            </span>
        </div>

        <?php
        // Usar componente reutilizable
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
</div>