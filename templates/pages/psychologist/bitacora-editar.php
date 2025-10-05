<?php
// templates/pages/psychologist/bitacora-editar.php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_patients')) {
    wp_die('Acceso denegado');
}

$note_id = intval($_GET['note_id'] ?? 0);
$patient_id = intval($_GET['patient_id'] ?? 0);
$psychologist_id = get_current_user_id();
$return = sanitize_text_field($_GET['return'] ?? 'lista');

// Obtener entrada
$entry = \Openmind\Repositories\SessionNoteRepository::getById($note_id);

if (!$entry || $entry->psychologist_id != $psychologist_id) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Entrada no encontrada o no tienes permisos para editarla.
    </div>';
    return;
}

$patient = get_userdata($patient_id);
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
            <i class="fa-solid fa-pen mr-3 text-primary-500"></i>
            Editar Entrada de Bitácora
        </h1>
        <p class="text-gray-600 m-0">
            Sesión #<?php echo $entry->session_number; ?> - Creada el <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?>
        </p>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-2xl p-8 shadow-sm">
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