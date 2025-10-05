<?php
// templates/pages/psychologist/bitacora-nueva.php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_patients')) {
    wp_die('Acceso denegado');
}

$patient_id = intval($_GET['patient_id'] ?? 0);
$patient = get_userdata($patient_id);
$psychologist_id = get_current_user_id();
$return = sanitize_text_field($_GET['return'] ?? 'lista');

// Verificar que el paciente existe y pertenece al psicólogo
$patient_psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);
if (!$patient || $patient_psychologist_id != $psychologist_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Paciente no encontrado o no tienes permisos para verlo.
    </div>';
    return;
}
?>

<div class="max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="<?php echo $return === 'detalle'
                ? add_query_arg(['view' => 'pacientes', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/'))
                : add_query_arg(['view' => 'bitacora', 'patient_id' => $patient_id], home_url('/dashboard-psicologo/')); ?>"
           class="inline-flex items-center gap-2 text-primary-600 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
            <i class="fa-solid fa-pen-to-square mr-3 text-primary-500"></i>
            Nueva Entrada de Bitácora
        </h1>
        <p class="text-gray-600 m-0">
            Registra los detalles de la sesión terapéutica con <?php echo esc_html($patient->display_name); ?>
        </p>
    </div>

    <div class="bg-white rounded-2xl p-8 shadow-sm">
        <?php
        $args = [
                'patient_id' => $patient_id,
                'patient_name' => $patient->display_name,
                'return' => $return,
                'form_action' => 'create'
        ];
        include OPENMIND_PATH . 'templates/components/bitacora-form.php';
        ?>
    </div>
</div>