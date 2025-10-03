<?php
/**
 * Formulario para editar bitácora existente
 * URL: ?view=bitacora-editar&entry_id=123&patient_id=456
 */

if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_patients')) {
    wp_die('Acceso denegado');
}

$entry_id = intval($_GET['entry_id'] ?? 0);
$patient_id = intval($_GET['patient_id'] ?? 0);
$psychologist_id = get_current_user_id();
$return = sanitize_text_field($_GET['return'] ?? 'lista');

// Obtener entrada
$entry = \Openmind\Repositories\DiaryRepository::getById($entry_id);

if (!$entry || $entry->author_id != $psychologist_id) {
    echo '<div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-4 tw-text-red-700 tw-text-center tw-my-6">
        <i class="fa-solid fa-triangle-exclamation tw-mr-2"></i>
        Entrada no encontrada o no tienes permisos para editarla.
    </div>';
    return;
}

$patient = get_userdata($patient_id);
?>

<div class="tw-max-w-5xl tw-mx-auto">
    <!-- Breadcrumb -->
    <div class="tw-mb-6">
        <a href="<?php echo $return === 'detalle'
            ? add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))
            : add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')); ?>"
           class="tw-inline-flex tw-items-center tw-gap-2 tw-text-primary-600 tw-text-sm tw-font-medium tw-transition-colors hover:tw-text-primary-700 tw-no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    <!-- Header -->
    <div class="tw-mb-8">
        <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-m-0 tw-mb-2">
            <i class="fa-solid fa-pen tw-mr-3 tw-text-primary-500"></i>
            Editar Entrada de Bitácora
        </h1>
        <p class="tw-text-gray-600 tw-m-0">
            Creada el <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?>
        </p>
    </div>

    <!-- Formulario -->
    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-shadow-sm">
        <?php
        $args = [
            'patient_id' => $patient_id,
            'patient_name' => $patient->display_name,
            'entry' => $entry,
            'return' => $return,
            'form_action' => 'update'
        ];
        include OPENMIND_PATH . 'templates/components/bitacora-form.php';
        ?>
    </div>
</div>