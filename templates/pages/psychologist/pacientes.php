<?php // templates/pages/psychologist/pacientes.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

// Si hay un patient_id en la URL, mostrar detalle
if (isset($_GET['patient_id'])) {
    include OPENMIND_PATH . 'templates/pages/psychologist/paciente-detalle.php';
    return;
}

// Obtener filtros de URL
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

// Construir query
$args = [
        'role' => 'patient',
        'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
        ]
];

// Agregar búsqueda por texto
if (!empty($search)) {
    $args['search'] = '*' . $search . '*';
    $args['search_columns'] = ['display_name', 'user_email'];
}

// Filtrar por status
if ($status_filter !== 'all') {
    $args['meta_query'][] = [
            'key' => 'openmind_status',
            'value' => $status_filter,
            'compare' => '='
    ];
}

$patients = get_users($args);
$total_patients = count($patients);
// Contar pacientes por estado
$all_patients = get_users([
        'role' => 'patient',
        'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
        ],
        'fields' => 'ID'
]);

$active_count = 0;
$inactive_count = 0;

foreach ($all_patients as $patient_id) {
    $status = get_user_meta($patient_id, 'openmind_status', true);
    if ($status === 'active') {
        $active_count++;
    } else {
        $inactive_count++;
    }
}

$total_count = count($all_patients);
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-normal text-gray-900 m-0">
                Mis Pacientes
            </h1>
            <p class="text-gray-600 m-0" id="patients-count">
                <?php echo $total_count; ?> paciente<?php echo $total_count !== 1 ? 's' : ''; ?>
                <span class="text-sm">
                    (<span class="font-medium"><?php echo $active_count; ?> activo<?php echo $active_count !== 1 ? 's' : ''; ?></span>,
                    <span class="font-medium"><?php echo $inactive_count; ?> inactivo<?php echo $inactive_count !== 1 ? 's' : ''; ?></span>)
                </span>
            </p>
        </div>
        <div class="flex gap-3">
            <button class="bg-primary-500 text-white px-6 py-3 rounded-xl"
                    onclick="openAssignPatientModal()">
                Asignar Paciente Existente
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Buscador -->
            <div class="flex-1">
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           id="patient-search"
                           value="<?php echo esc_attr($search); ?>"
                           placeholder="Buscar por nombre o correo..."
                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <?php if (!empty($search)): ?>
                        <button onclick="clearSearch()"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pills de Estado -->
            <div class="flex gap-2">
                <button onclick="filterByStatus('all')"
                        data-status="all"
                        class="status-pill px-4 py-3 rounded-lg font-medium transition-colors <?php echo $status_filter === 'all' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Todos
                </button>
                <button onclick="filterByStatus('active')"
                        data-status="active"
                        class="status-pill px-4 py-3 rounded-lg font-medium transition-colors <?php echo $status_filter === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Activos
                </button>
                <button onclick="filterByStatus('inactive')"
                        data-status="inactive"
                        class="status-pill px-4 py-3 rounded-lg font-medium transition-colors <?php echo $status_filter === 'inactive' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    Inactivos
                </button>
            </div>
        </div>
    </div>

    <!-- Contenedor de tabla (se reemplaza vía AJAX) -->
    <div id="patients-table-container">
        <?php
        // Estado vacío sin pacientes y sin filtros
        if (empty($patients) && empty($search) && $status_filter === 'all'):
            ?>
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
        <?php else:
            include OPENMIND_PATH . 'templates/components/patients-table.php';
        endif; ?>
    </div>
</div>

<!-- Modal: Asignar Paciente Existente -->
<div id="assign-patient-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
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
                        <strong>Importante:</strong> El paciente será asignado y <strong>activado automáticamente</strong>.
                    </div>
                </div>
            </div>

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
    let searchTimeout;
    let currentSearch = '<?php echo esc_js($search); ?>';
    let currentStatus = '<?php echo esc_js($status_filter); ?>';

    // ========== FILTROS AJAX ==========
    async function fetchPatients(search = '', status = 'all') {
        const container = document.getElementById('patients-table-container');
        const countEl = document.getElementById('patients-count');

        // Loading state
        container.innerHTML = '<div class="bg-white border border-gray-200 rounded-xl p-16 text-center"><i class="fa-solid fa-spinner fa-spin text-4xl text-gray-400"></i></div>';

        const formData = new FormData();
        formData.append('action', 'openmind_filter_patients');
        formData.append('nonce', openmindData.nonce);
        formData.append('search', search);
        formData.append('status', status);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                container.innerHTML = data.data.html;
                countEl.innerHTML = data.data.count_text;

                // Actualizar URL sin recargar
                const url = new URL(window.location.href);
                url.searchParams.set('view', 'pacientes');

                if (search) {
                    url.searchParams.set('search', search);
                } else {
                    url.searchParams.delete('search');
                }

                if (status !== 'all') {
                    url.searchParams.set('status', status);
                } else {
                    url.searchParams.delete('status');
                }

                history.pushState({}, '', url.toString());

                // Actualizar pills activos
                updateActivePills(status);

            } else {
                Toast.show('Error al cargar pacientes', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            Toast.show('Error de conexión', 'error');
        }
    }

    // Actualizar clases activas de pills
    function updateActivePills(status) {
        document.querySelectorAll('.status-pill').forEach(pill => {
            const pillStatus = pill.dataset.status;
            pill.className = 'status-pill px-4 py-3 rounded-lg font-medium transition-colors';

            if (pillStatus === status) {
                if (status === 'all') {
                    pill.classList.add('bg-primary-500', 'text-white');
                } else if (status === 'active') {
                    pill.classList.add('bg-green-500', 'text-white');
                } else if (status === 'inactive') {
                    pill.classList.add('bg-yellow-500', 'text-white');
                }
            } else {
                pill.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            }
        });
    }

    // Búsqueda con debounce
    document.getElementById('patient-search')?.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentSearch = e.target.value.trim();
            fetchPatients(currentSearch, currentStatus);
        }, 300);
    });

    // Filtrar por estado
    window.filterByStatus = function(status) {
        currentStatus = status;
        fetchPatients(currentSearch, status);
    }

    // Limpiar búsqueda
    window.clearSearch = function() {
        document.getElementById('patient-search').value = '';
        currentSearch = '';
        fetchPatients('', currentStatus);
    }

    // Limpiar todos los filtros
    window.clearFilters = function() {
        document.getElementById('patient-search').value = '';
        currentSearch = '';
        currentStatus = 'all';
        fetchPatients('', 'all');
    }

    // ========== MODAL DE ASIGNACIÓN ==========
    window.openAssignPatientModal = function() {
        document.getElementById('assign-patient-modal').style.display = 'flex';
        document.getElementById('assign-patient-email').focus();
    }

    window.closeAssignPatientModal = function() {
        document.getElementById('assign-patient-modal').style.display = 'none';
        document.getElementById('assign-patient-form').reset();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAssignPatientModal();
        });

        // Cerrar al hacer clic fuera
        const modal = document.getElementById('assign-patient-modal');
        modal?.addEventListener('click', function(e) {
            if (e.target === modal) closeAssignPatientModal();
        });

        // Submit del formulario de asignación
        document.getElementById('assign-patient-form')?.addEventListener('submit', async function(e) {
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

            const submitBtn = this.querySelector('button[type="submit"]');
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
                    Toast.show(data.data.message || 'Paciente asignado correctamente', 'success');
                    closeAssignPatientModal();
                    setTimeout(() => fetchPatients(currentSearch, currentStatus), 1000);
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

        // Botones de agregar paciente
        document.getElementById('add-patient')?.addEventListener('click', () => {
            if (typeof OpenmindApp !== 'undefined') OpenmindApp.showAddPatientModal();
        });

        document.getElementById('add-first-patient')?.addEventListener('click', () => {
            if (typeof OpenmindApp !== 'undefined') OpenmindApp.showAddPatientModal();
        });
    });
</script>