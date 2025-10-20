<?php // templates/components/patients-table.php
if (!defined('ABSPATH')) exit;

// Este componente espera recibir $patients
if (!isset($patients)) return;

$total = count($patients);
?>

<?php if (empty($patients)): ?>
    <div class="bg-white border border-gray-200 rounded-xl p-16 text-center">
        <div class="mb-6">
            <i class="fa-solid fa-search text-6xl text-gray-300"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2 m-0">
            No se encontraron pacientes
        </h3>
        <p class="text-gray-600 m-0 mb-6">
            Intenta con otros criterios de búsqueda
        </p>
        <button onclick="clearFilters()"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
            <i class="fa-solid fa-filter-circle-xmark"></i>
            Limpiar Filtros
        </button>
    </div>
<?php else: ?>
    <!-- Desktop Table -->
    <div class="hidden md:block bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
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
                $status = get_user_meta($patient->ID, 'openmind_status', true);
                $is_active = $status === 'active';
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
                            <img src="<?php echo esc_url(get_avatar_url($patient->ID, ['size' => 40])); ?>"
                                 alt="Avatar"
                                 class="w-10 h-10 rounded-full border-4 border-primary-100 object-cover">
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
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1 rounded-full <?php echo $is_active ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                            <?php echo $is_active ? '🟢 Activo' : '🟡 Inactivo'; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-end gap-2">
                            <a href="<?php echo add_query_arg(['view' => 'pacientes', 'patient_id' => $patient->ID], home_url('/dashboard-psicologo/')); ?>"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary-500 text-white rounded-lg text-sm font-medium transition-colors hover:bg-primary-600 no-underline"
                               title="Ver detalles">
                                <i class="fa-solid fa-eye"></i>
                                <span>Ver</span>
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

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3">
        <?php foreach ($patients as $patient):
            $status = get_user_meta($patient->ID, 'openmind_status', true);
            $is_active = $status === 'active';
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
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <!-- Header con avatar y nombre -->
                <div class="flex items-start gap-3 mb-3">
                    <img src="<?php echo esc_url(get_avatar_url($patient->ID, ['size' => 48])); ?>"
                         alt="Avatar"
                         class="w-12 h-12 rounded-full border-4 border-primary-100 object-cover flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 m-0 truncate">
                            <?php echo esc_html($patient->display_name); ?>
                        </h3>
                        <p class="text-sm text-gray-600 m-0 truncate">
                            <?php echo esc_html($patient->user_email); ?>
                        </p>
                    </div>
                    <!-- Estado -->
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full flex-shrink-0 <?php echo $is_active ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                        <?php echo $is_active ? '🟢' : '🟡'; ?>
                    </span>
                </div>

                <!-- Info adicional -->
                <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                    <span>
                        <i class="fa-solid fa-calendar-days mr-1"></i>
                        <?php echo date('d/m/Y', strtotime($patient->user_registered)); ?>
                    </span>
                    <?php if ($pending_count > 0): ?>
                        <span class="text-orange-600">
                            <i class="fa-solid fa-clipboard-list mr-1"></i>
                            <?php echo $pending_count; ?> pendiente<?php echo $pending_count !== 1 ? 's' : ''; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-2">
                    <a href="<?php echo add_query_arg(['view' => 'pacientes', 'patient_id' => $patient->ID], home_url('/dashboard-psicologo/')); ?>"
                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-500 text-white rounded-lg text-sm font-medium transition-colors hover:bg-primary-600 no-underline">
                        <i class="fa-solid fa-eye"></i>
                        Ver Perfil
                    </a>
                    <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'user_id' => $patient->ID], home_url('/dashboard-psicologo/')); ?>"
                       class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition-colors hover:bg-gray-200 no-underline"
                       title="Enviar mensaje">
                        <i class="fa-solid fa-message"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>