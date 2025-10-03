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
    echo '<div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-4 tw-text-red-700 tw-text-center tw-my-6">
        <i class="fa-solid fa-triangle-exclamation tw-mr-2"></i>
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

<div class="tw-max-w-6xl tw-mx-auto">
    <!-- Breadcrumb -->
    <div class="tw-mb-6">
        <a href="?view=bitacora" class="tw-inline-flex tw-items-center tw-gap-2 tw-text-primary-600 tw-text-sm tw-font-medium tw-transition-colors hover:tw-text-primary-700 tw-no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Bitácora
        </a>
    </div>

    <!-- Patient Header -->
    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-mb-8 tw-shadow-sm">
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-start md:tw-items-center tw-gap-6">
            <div class="tw-flex tw-gap-6 tw-items-start">
                <?php echo get_avatar($patient->ID, 80, '', '', ['class' => 'tw-rounded-2xl tw-border-4 tw-border-gray-100']); ?>
                <div>
                    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-m-0 tw-mb-2">
                        <i class="fa-solid fa-book tw-mr-3 tw-text-primary-500"></i>
                        Bitácora de <?php echo esc_html($patient->display_name); ?>
                    </h1>
                    <p class="tw-flex tw-items-center tw-gap-2 tw-text-gray-600 tw-text-sm tw-my-1 tw-m-0">
                        <i class="fa-solid fa-envelope"></i>
                        <?php echo esc_html($patient->user_email); ?>
                    </p>
                    <p class="tw-flex tw-items-center tw-gap-2 tw-text-gray-600 tw-text-sm tw-my-1 tw-m-0">
                        <i class="fa-solid fa-calendar-check"></i>
                        Paciente desde <?php echo date('d/m/Y', strtotime($patient->user_registered)); ?>
                    </p>
                </div>
            </div>

            <a href="?view=bitacora-nueva&patient_id=<?php echo $patient_id; ?>&return=lista"
               class="tw-inline-flex tw-items-center tw-gap-2 tw-px-6 tw-py-3 tw-bg-primary-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-primary-600 hover:tw--translate-y-0.5 hover:tw-shadow-lg tw-no-underline">
                <i class="fa-solid fa-plus"></i>
                Nueva Entrada
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6 tw-mb-8">
        <div class="tw-bg-gradient-to-br tw-from-blue-50 tw-to-blue-100 tw-p-6 tw-rounded-xl tw-border tw-border-blue-200">
            <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-w-12 tw-h-12 tw-bg-blue-500 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                    <i class="fa-solid fa-book tw-text-white tw-text-xl"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-text-blue-700 tw-m-0">Total Sesiones</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-blue-900 tw-m-0"><?php echo $total_entries; ?></p>
                </div>
            </div>
        </div>

        <div class="tw-bg-gradient-to-br tw-from-green-50 tw-to-green-100 tw-p-6 tw-rounded-xl tw-border tw-border-green-200">
            <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-w-12 tw-h-12 tw-bg-green-500 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                    <i class="fa-solid fa-check-circle tw-text-white tw-text-xl"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-text-green-700 tw-m-0">Actividades Completadas</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-green-900 tw-m-0"><?php echo $completed_activities; ?></p>
                </div>
            </div>
        </div>

        <div class="tw-bg-gradient-to-br tw-from-purple-50 tw-to-purple-100 tw-p-6 tw-rounded-xl tw-border tw-border-purple-200">
            <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-w-12 tw-h-12 tw-bg-purple-500 tw-rounded-xl tw-flex tw-items-center tw-justify-center">
                    <i class="fa-solid fa-clipboard-list tw-text-white tw-text-xl"></i>
                </div>
                <div>
                    <p class="tw-text-sm tw-text-purple-700 tw-m-0">Actividades Totales</p>
                    <p class="tw-text-3xl tw-font-bold tw-text-purple-900 tw-m-0"><?php echo count($all_activities); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Bitácoras -->
    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-shadow-sm">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-m-0">
                Registro de Sesiones
            </h2>
            <span class="tw-text-sm tw-text-gray-500">
                <i class="fa-solid fa-calendar-days tw-mr-1"></i>
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