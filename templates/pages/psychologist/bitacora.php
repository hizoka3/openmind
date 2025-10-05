<?php
// templates/pages/psychologist/bitacora.php
if (!defined('ABSPATH')) exit;

// Si hay patient_id, mostrar bitacora-paciente.php
if (isset($_GET['patient_id'])) {
    include OPENMIND_PATH . 'templates/pages/psychologist/bitacora-paciente.php';
    return;
}

// Vista principal: lista de pacientes
$user_id = get_current_user_id();

$patients = get_users([
        'role' => 'patient',
        'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
        ]
]);
?>

<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 m-0 mb-2">
            <i class="fa-solid fa-book mr-3 text-primary-500"></i>
            Bitácora de Pacientes
        </h1>
        <p class="text-gray-600 m-0">
            Revisa y crea entradas de sesión para tus pacientes
        </p>
    </div>

    <?php if (empty($patients)): ?>
        <div class="text-center py-16 text-gray-400">
            <div class="text-6xl mb-4">👥</div>
            <p class="text-lg not-italic text-gray-600">No tienes pacientes asignados.</p>
            <a href="?view=pacientes" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-primary-500 text-white rounded-lg text-sm font-medium transition-all hover:bg-primary-600 no-underline">
                <i class="fa-solid fa-user-plus"></i>
                Agregar Paciente
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($patients as $patient):
                $latest_entry = \Openmind\Repositories\SessionNoteRepository::getLatest($patient->ID);
                $total_entries = \Openmind\Repositories\SessionNoteRepository::countByPatient($patient->ID);
                ?>
                <div class="bg-white border border-gray-200 rounded-xl p-6 transition-all hover:shadow-lg hover:-translate-y-1">
                    <!-- Header -->
                    <div class="flex items-start gap-4 mb-4">
                        <?php echo get_avatar($patient->ID, 60, '', '', ['class' => 'rounded-xl border-2 border-gray-100']); ?>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 m-0 mb-1">
                                <?php echo esc_html($patient->display_name); ?>
                            </h3>
                            <p class="text-sm text-gray-500 m-0">
                                <?php echo $total_entries; ?> sesión<?php echo $total_entries !== 1 ? 'es' : ''; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Última entrada -->
                    <?php if ($latest_entry): ?>
                        <div class="bg-gray-50 rounded-lg p-3 mb-4">
                            <p class="text-xs text-gray-500 mb-1">Última sesión:</p>
                            <p class="text-sm text-gray-700 m-0">
                                <?php echo date('d/m/Y', strtotime($latest_entry->created_at)); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Acciones -->
                    <div class="flex gap-2">
                        <a href="<?php echo add_query_arg(['view' => 'bitacora', 'patient_id' => $patient->ID], home_url('/dashboard-psicologo/')); ?>"
                           class="flex-1 text-center px-4 py-2 bg-primary-50 text-primary-600 rounded-lg text-sm font-medium transition-all hover:bg-primary-100 no-underline">
                            Ver bitácora
                        </a>
                        <a href="<?php echo add_query_arg(['view' => 'bitacora-nueva', 'patient_id' => $patient->ID, 'return' => 'lista'], home_url('/dashboard-psicologo/')); ?>"
                           class="px-4 py-2 bg-primary-500 text-white rounded-lg text-sm font-medium transition-all hover:bg-primary-600 no-underline">
                            <i class="fa-solid fa-plus"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>