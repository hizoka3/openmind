<?php
// templates/pages/psychologist/pacientes.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

// Si hay un patient_id en la URL, mostrar detalle
if (isset($_GET['patient_id'])) {
    include OPENMIND_PATH . 'templates/pages/psychologist/paciente-detalle.php';
    return;
}

$patients = get_users([
        'role' => 'patient',
        'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
        ]
]);
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-normal text-gray-900 m-0">
                Mis Pacientes
            </h1>
            <p class="text-gray-600 m-0">
                <?php echo count($patients); ?> paciente<?php echo count($patients) !== 1 ? 's' : ''; ?> registrado<?php echo count($patients) !== 1 ? 's' : ''; ?>
            </p>
        </div>
        <button class="btn-primary" id="add-patient">
            <i class="fa-solid fa-user-plus mr-2"></i>
            Agregar Paciente
        </button>
    </div>

    <?php if (empty($patients)): ?>
        <div class="bg-white border border-gray-200 rounded-xl p-16 text-center">
            <div class="mb-6">
                <i class="fa-solid fa-users text-6xl text-gray-300"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2 m-0">
                No tienes pacientes asignados
            </h3>
            <p class="text-gray-600 m-0 mb-6">
                Comienza agregando tu primer paciente
            </p>
            <button class="btn-primary" id="add-first-patient">
                <i class="fa-solid fa-user-plus mr-2"></i>
                Agregar Primer Paciente
            </button>
        </div>
    <?php else: ?>
        <!-- Tabla Modernizada -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Paciente
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Correo
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Registro
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Estado
                    </th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                <?php foreach ($patients as $patient):
                    $last_activity = get_user_meta($patient->ID, 'last_activity_date', true);
                    $pending_count = count(get_posts([
                            'post_type' => 'activity',
                            'meta_query' => [
                                    ['key' => 'assigned_to', 'value' => $patient->ID],
                                    ['key' => 'completed', 'value' => '0']
                            ],
                            'posts_per_page' => -1,
                            'fields' => 'ids'
                    ]));
                    ?>
                    <tr class="transition-colors hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <?php echo get_avatar($patient->ID, 40, '', '', ['class' => 'rounded-lg border-2 border-gray-100']); ?>
                                <div>
                                    <div class="font-semibold text-gray-900">
                                        <?php echo esc_html($patient->display_name); ?>
                                    </div>
                                    <?php if ($pending_count > 0): ?>
                                        <div class="text-xs text-orange-600 mt-0.5">
                                            <i class="fa-solid fa-clipboard-list mr-1"></i>
                                            <?php echo $pending_count; ?> pendiente<?php echo $pending_count !== 1 ? 's' : ''; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo esc_html($patient->user_email); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <?php echo date('d/m/Y', strtotime($patient->user_registered)); ?>
                        </td>
                        <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full <?php echo $last_activity ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'; ?>">
                                    <?php echo $last_activity ? '🟢 Activo' : '⚪ Inactivo'; ?>
                                </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="<?php echo add_query_arg(['view' => 'pacientes', 'patient_id' => $patient->ID], home_url('/dashboard-psicologo/')); ?>"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary-500 text-white rounded-lg text-sm font-medium transition-colors hover:bg-primary-600 no-underline"
                                   title="Ver detalles">
                                    <i class="fa-solid fa-eye"></i>
                                    <span class="hidden md:inline">Ver</span>
                                </a>
                                <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'user_id' => $patient->ID], home_url('/dashboard-psicologo/')); ?>"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition-colors hover:bg-gray-200 no-underline"
                                   title="Enviar mensaje">
                                    <i class="fa-solid fa-message"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    document.getElementById('add-patient')?.addEventListener('click', () => {
        if (typeof OpenmindApp !== 'undefined') {
            OpenmindApp.showAddPatientModal();
        }
    });

    document.getElementById('add-first-patient')?.addEventListener('click', () => {
        if (typeof OpenmindApp !== 'undefined') {
            OpenmindApp.showAddPatientModal();
        }
    });
</script>