<?php // templates/pages/psychologist/pacientes.php
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
        <div class="flex gap-3">
            <button class="bg-primary-500 text-white px-6 py-3 rounded-xl"
                    onclick="openAssignPatientModal()">
                Asignar Paciente Existente
            </button>
        </div>
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
                Puedes crear un nuevo paciente o asignar uno existente
            </p>
            <div class="flex gap-4 justify-center">
                <button class="bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-xl transition-colors"
                        onclick="openAssignPatientModal()">
                    <i class="fa-solid fa-user-check mr-2"></i>
                    Asignar Paciente
                </button>
                <button class="btn-primary" id="add-first-patient">
                    <i class="fa-solid fa-user-plus mr-2"></i>
                    Crear Paciente
                </button>
            </div>
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
                    $status = get_user_meta($patient->ID, 'openmind_status', true);
                    $is_active = $status === 'active';
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
                                <img id="avatar-preview"
                                     src="<?php echo esc_url(get_avatar_url($patient->ID, ['size' => 40])); ?>"
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

<!-- Modal: Asignar Paciente Existente -->
<div id="assign-patient-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Asignar Paciente</h3>
                <p class="text-sm text-gray-600 mt-1">Asigna un paciente existente y actívalo</p>
            </div>
            <button type="button"
                    onclick="closeAssignPatientModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </div>

        <!-- Body -->
        <form id="assign-patient-form" class="p-6">
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Email del paciente <span class="text-red-500">*</span>
                </label>
                <input type="email"
                       name="patient_email"
                       id="assign-patient-email"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                       placeholder="paciente@ejemplo.com"
                       required>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    El paciente debe estar registrado en el sistema
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex gap-3">
                    <i class="fa-solid fa-lightbulb text-blue-600 text-xl"></i>
                    <div class="text-sm text-blue-800">
                        <strong>Importante:</strong> El paciente será asignado y <strong>activado automáticamente</strong> para que pueda acceder a todas las funcionalidades.
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex gap-3">
                <button type="button"
                        onclick="closeAssignPatientModal()"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-3 bg-primary-500 text-white rounded-lg">
                    Asignar y Activar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal de asignación
    window.openAssignPatientModal = function() {
        document.getElementById('assign-patient-modal').style.display = 'flex';
        document.getElementById('assign-patient-email').focus();
    }

    window.closeAssignPatientModal = function() {
        document.getElementById('assign-patient-modal').style.display = 'none';
        document.getElementById('assign-patient-form').reset();
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAssignPatientModal();
            }
        });

        // Cerrar al hacer clic fuera
        const modal = document.getElementById('assign-patient-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeAssignPatientModal();
                }
            });
        }

        // Submit del formulario de asignación
        const assignForm = document.getElementById('assign-patient-form');
        if (assignForm) {
            assignForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const email = document.getElementById('assign-patient-email').value.trim();

                if (!email) {
                    Toast.show('Por favor ingresa un email', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'openmind_assign_patient');
                formData.append('nonce', openmindData.nonce);
                formData.append('patient_email', email);

                const submitBtn = assignForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Asignando...';

                try {
                    const response = await fetch(openmindData.ajaxUrl, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        Toast.show(data.data.message || 'Paciente asignado y activado correctamente', 'success');
                        closeAssignPatientModal();

                        // Recargar página después de 1 segundo
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Toast.show(data.data || 'Error al asignar paciente', 'error');
                    }
                } catch (error) {
                    Toast.show('Error de conexión', 'error');
                    console.error(error);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        }

        // Botones de agregar paciente (funcionalidad existente)
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
    });
</script>