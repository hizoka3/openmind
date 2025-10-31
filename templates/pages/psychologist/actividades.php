<?php
// templates/pages/psychologist/actividades.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

// Obtener biblioteca de actividades (creadas por admin)
$library_activities = \Openmind\Controllers\ActivityController::getLibraryActivities();

// Obtener pacientes del psicólogo
global $wpdb;
$patients = $wpdb->get_results($wpdb->prepare("
    SELECT u.ID, u.display_name, u.user_email
    FROM {$wpdb->users} u
    INNER JOIN {$wpdb->prefix}openmind_relationships r ON u.ID = r.patient_id
    WHERE r.psychologist_id = %d
    ORDER BY u.display_name ASC
", $user_id));

// Obtener historial de asignaciones del psicólogo
$assignments = get_posts([
        'post_type' => 'activity_assignment',
        'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $user_id]
        ],
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
]);
?>

<div class="max-w-7xl">
    <!-- Header -->
    <h1 class="text-2xl font-normal text-gray-900 mb-6">Mis Actividades</h1>

    <!-- Tabs -->
    <div class="bg-white px-3 md:px-6 rounded-xl shadow-sm mb-4">
        <div class="flex gap-2 border-b-2 border-gray-200 overflow-x-auto scrollbar-hide">
            <button class="tab-activity active flex items-center gap-2 px-4 md:px-6 py-3 bg-transparent border-0 border-b-4 cursor-pointer text-base md:text-lg font-medium text-dark-gray-300 transition-all whitespace-nowrap rounded-t-lg" data-tab="biblioteca">
                <span class="hidden sm:inline">Biblioteca</span>
                <span class="sm:hidden">Biblioteca</span>
                <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 bg-primary-600 text-white text-xs font-semibold rounded-full"><?php echo count($library_activities); ?></span>
            </button>
            <button class="tab-activity flex items-center gap-2 px-4 md:px-6 py-3 bg-transparent border-0 border-b-4 border-transparent cursor-pointer text-base md:text-lg font-medium text-dark-gray-300 transition-all whitespace-nowrap rounded-t-lg hover:text-gray-900 hover:bg-gray-50" data-tab="asignaciones">
                <span class="hidden sm:inline">Mis Asignaciones</span>
                <span class="sm:hidden">Asignaciones</span>
                <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full"><?php echo count($assignments); ?></span>
            </button>
        </div>
    </div>

    <!-- Tab: Biblioteca -->
    <div id="tab-biblioteca" class="tab-content" style="display: block;">
        <!-- Filtros y búsqueda -->
        <div class="bg-white rounded-xl shadow-sm p-3 md:p-4 mb-6">
            <div class="flex flex-col gap-3">
                <!-- Buscador -->
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text"
                           id="search-activities"
                           placeholder="Buscar actividad..."
                           class="w-full pl-10 pr-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Filtro por tipo -->
                <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                    <button class="filter-type active px-3 md:px-4 py-2 text-xs md:text-sm font-medium border rounded-lg transition-all whitespace-nowrap flex-shrink-0" data-type="all">
                        Todos
                    </button>
                    <button class="filter-type px-3 md:px-4 py-2 text-xs md:text-sm font-medium border rounded-lg transition-all whitespace-nowrap flex-shrink-0" data-type="pdf">
                        <i class="fa-solid fa-file-pdf text-red-500"></i>
                        <span class="hidden sm:inline ml-1">PDF</span>
                    </button>
                    <button class="filter-type px-3 md:px-4 py-2 text-xs md:text-sm font-medium border rounded-lg transition-all whitespace-nowrap flex-shrink-0" data-type="youtube">
                        <i class="fa-brands fa-youtube text-red-600"></i>
                        <span class="hidden sm:inline ml-1">YouTube</span>
                    </button>
                    <button class="filter-type px-3 md:px-4 py-2 text-xs md:text-sm font-medium border rounded-lg transition-all whitespace-nowrap flex-shrink-0" data-type="link">
                        <i class="fa-solid fa-link text-blue-500"></i>
                        <span class="hidden sm:inline ml-1">Link</span>
                    </button>
                </div>
            </div>
        </div>

        <?php if (empty($library_activities)): ?>
            <!-- Empty State -->
            <div class="text-center py-12 md:py-20 bg-white rounded-xl shadow-sm px-4">
                <p class="text-base md:text-lg text-dark-gray-300 mb-2">No hay actividades en la biblioteca</p>
                <p class="text-sm text-gray-500">Contacta al administrador para que agregue actividades</p>
            </div>
        <?php else: ?>
            <!-- Grid de actividades -->
            <div id="activities-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($library_activities as $activity):
                    // Obtener recursos y extraer tipos únicos
                    $resources = get_post_meta($activity->ID, '_activity_resources', true) ?: [];

                    // Fallback para actividades antiguas
                    if (empty($resources)) {
                        $old_type = get_post_meta($activity->ID, '_activity_type', true);
                        if ($old_type) {
                            $resources = [[
                                    'type' => $old_type,
                                    'file_id' => get_post_meta($activity->ID, '_activity_file', true) ?: '',
                                    'url' => get_post_meta($activity->ID, '_activity_url', true) ?: ''
                            ]];
                        }
                    }

                    // Extraer todos los tipos únicos
                    $types = array_unique(array_column($resources, 'type'));
                    $types_string = implode(',', $types);
                    $first_type = $types[0] ?? 'file';

                    // Iconos por tipo
                    $type_icons = [
                            'pdf' => '<i class="fa-solid fa-file-pdf text-red-500 text-2xl"></i>',
                            'youtube' => '<i class="fa-brands fa-youtube text-red-600 text-2xl"></i>',
                            'link' => '<i class="fa-solid fa-link text-blue-500 text-2xl"></i>'
                    ];

                    // URL del primer recurso para botón "Ver"
                    $first_resource = $resources[0] ?? null;
                    $resource_url = '';
                    if ($first_resource) {
                        if ($first_resource['type'] === 'pdf' && !empty($first_resource['file_id'])) {
                            $resource_url = wp_get_attachment_url($first_resource['file_id']);
                        } elseif (in_array($first_resource['type'], ['youtube', 'link']) && !empty($first_resource['url'])) {
                            $resource_url = $first_resource['url'];
                        }
                    }
                    ?>
                    <div class="activity-card bg-white border border-gray-200 rounded-xl p-4 md:p-5 transition-all hover:shadow-lg hover:-translate-y-1"
                         data-types="<?php echo esc_attr($types_string); ?>"
                         data-title="<?php echo esc_attr(strtolower($activity->post_title)); ?>"
                         data-description="<?php echo esc_attr(strtolower(strip_tags($activity->post_content))); ?>">

                        <!-- Icono de tipo -->
                        <div class="flex items-start mb-3 gap-x-2 md:gap-x-3">
                            <?php foreach ($types as $type): ?>
                                <div class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-gray-50 rounded-lg flex-shrink-0">
                                    <?php echo $type_icons[$type]; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Título -->
                        <h3 class="text-sm md:text-base font-semibold text-gray-900 mb-2 line-clamp-2">
                            <?php echo esc_html($activity->post_title); ?>
                        </h3>

                        <!-- Descripción -->
                        <?php if (!empty($activity->post_content)): ?>
                            <p class="text-xs md:text-sm text-gray-600 mb-4 line-clamp-3">
                                <?php echo wp_trim_words(strip_tags($activity->post_content), 20); ?>
                            </p>
                        <?php endif; ?>

                        <!-- Botones de acción -->
                        <div class="flex gap-2 pt-4 border-t border-gray-100">
                            <a href="<?php echo add_query_arg(['view' => 'biblioteca-detalle', 'library_id' => $activity->ID], home_url('/dashboard-psicologo/')); ?>"
                               class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-xs md:text-sm font-medium transition-all hover:bg-gray-200 no-underline">
                                <i class="fa-solid fa-eye"></i>
                                <span class="hidden sm:inline">Ver Detalle</span>
                                <span class="sm:hidden">Ver</span>
                            </a>
                            <button class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-primary-600 text-white rounded-lg text-xs md:text-sm font-medium transition-all hover:bg-primary-700 border-0 cursor-pointer"
                                    onclick="openAssignModal(<?php echo $activity->ID; ?>, '<?php echo esc_js($activity->post_title); ?>')">
                                <i class="fa-solid fa-user-plus"></i>
                                Asignar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Mensaje cuando no hay resultados -->
            <div id="no-results" class="hidden text-center py-12 bg-white rounded-xl shadow-sm">
                <div class="text-4xl mb-3">🔍</div>
                <p class="text-gray-600">No se encontraron actividades con ese criterio</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab: Mis Asignaciones -->
    <div id="tab-asignaciones" class="tab-content" style="display: none;">
        <?php if (empty($assignments)): ?>
            <div class="text-center py-12 md:py-20 bg-white rounded-xl shadow-sm px-4">
                <p class="text-base md:text-lg text-dark-gray-300 mb-2">No has asignado actividades aún</p>
                <p class="text-sm text-gray-600">Ve a la biblioteca y asigna actividades a tus pacientes</p>
            </div>
        <?php else: ?>
            <div class="flex flex-col gap-4">
                <?php foreach ($assignments as $assignment):
                    $library_id = $assignment->post_parent;
                    $library_activity = get_post($library_id);
                    $patient_id = get_post_meta($assignment->ID, 'patient_id', true);
                    $patient = get_userdata($patient_id);
                    $status = get_post_meta($assignment->ID, 'status', true);
                    $due_date = get_post_meta($assignment->ID, 'due_date', true);
                    $completed_at = get_post_meta($assignment->ID, 'completed_at', true);

                    // Obtener tipos de la actividad de biblioteca
                    $resources = get_post_meta($library_id, '_activity_resources', true) ?: [];
                    $types = array_unique(array_column($resources, 'type'));
                    $first_type = $types[0] ?? 'file';

                    $is_overdue = !empty($due_date) && $due_date !== 'null' && strtotime($due_date) !== false && strtotime($due_date) < current_time('timestamp') && $status !== 'completed';
                    ?>
                    <div class="bg-white border border-gray-200 rounded-xl p-4 md:p-6 transition-all hover:shadow-md <?php echo $is_overdue ? 'border-l-4 border-l-red-500' : ''; ?>">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-3 mb-2">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-900 m-0 truncate">
                                        <?php echo esc_html($assignment->post_title); ?>
                                    </h3>
                                    <div class="flex gap-1 flex-wrap">
                                        <?php foreach ($types as $type): ?>
                                            <span class="text-xs font-medium px-2 py-1 bg-gray-100 text-gray-600 rounded-full uppercase">
                                                <?php echo esc_html($type); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Paciente -->
                                <?php if ($patient): ?>
                                    <div class="flex items-center gap-2 mb-2">
                                        <img src="<?php echo esc_url(get_avatar_url($patient->ID, ['size' => 24])); ?>"
                                             alt="Avatar"
                                             class="w-6 h-6 rounded-full border-4 border-primary-100 object-cover flex-shrink-0">
                                        <span class="text-sm font-medium text-gray-700 truncate">
                                            <?php echo esc_html($patient->display_name); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <!-- Estado y fechas -->
                                <div class="flex flex-wrap items-center gap-2 mt-3">
                                    <?php if ($status === 'completed'): ?>
                                        <span class="inline-flex items-center gap-1 px-2 md:px-3 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                            <i class="fa-solid fa-check-circle"></i>
                                            <span class="hidden sm:inline">Completada</span>
                                            <?php if ($completed_at): ?>
                                                <span class="hidden md:inline">· <?php echo date('d/m/Y', strtotime($completed_at)); ?></span>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 md:px-3 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded-full">
                                            <i class="fa-solid fa-hourglass-half"></i>
                                            Pendiente
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($due_date) && $due_date !== 'null'): ?>
                                        <span class="inline-flex items-center gap-1 px-2 md:px-3 py-1 <?php echo $is_overdue ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'; ?> text-xs font-medium rounded-full">
                                            <i class="fa-solid fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($due_date)); ?>
                                            <?php if ($is_overdue): ?>
                                                <span class="font-bold hidden sm:inline">VENCIDA</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>

                                    <span class="text-xs text-gray-500 hidden md:inline ml-auto">
                                        Asignada el <?php echo date('d/m/Y', strtotime($assignment->post_date)); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Botón ver detalle -->
                            <a href="<?php echo add_query_arg(['view' => 'actividad-detalle', 'activity_id' => $assignment->ID, 'from' => 'actividades'], home_url('/dashboard-psicologo/')); ?>"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg border-0 bg-primary-100 text-primary-600 cursor-pointer transition-all hover:bg-primary-200 no-underline flex-shrink-0 self-start md:self-auto">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Mensaje personalizado (si existe) -->
                        <?php if (!empty($assignment->post_content)): ?>
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-xs md:text-sm text-gray-600 italic">
                                    "<?php echo wp_trim_words(strip_tags($assignment->post_content), 25); ?>"
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Asignar Actividad -->
<div id="assign-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-4 md:px-6 py-4 rounded-t-xl flex-shrink-0">
            <div class="flex justify-between items-start gap-4">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg md:text-xl font-semibold text-gray-900 mb-1">Asignar Actividad</h2>
                    <p id="modal-activity-title" class="text-xs md:text-sm text-gray-600 truncate"></p>
                </div>
                <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <form id="assign-form" class="p-4 md:p-6 overflow-y-auto flex-1" data-modal-scroll style="overscroll-behavior: contain;">
            <input type="hidden" id="library-activity-id" name="library_activity_id">

            <!-- Selección de pacientes -->
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-3">
                    <label class="block text-sm font-medium text-gray-700">
                        Selecciona paciente(s) *
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="select-all-patients" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-xs md:text-sm text-gray-600">Seleccionar todos</span>
                    </label>
                </div>

                <?php if (empty($patients)): ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <p class="text-gray-600 mb-2 text-sm">No tienes pacientes asignados</p>
                        <a href="<?php echo add_query_arg('view', 'pacientes', home_url('/dashboard-psicologo/')); ?>"
                           class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                            Ir a Pacientes
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3" style="overscroll-behavior: contain;">
                        <?php foreach ($patients as $patient): ?>
                            <label class="flex items-center gap-2 md:gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                <input type="checkbox"
                                       name="patient_ids[]"
                                       value="<?php echo $patient->ID; ?>"
                                       class="patient-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500 flex-shrink-0">
                                <img src="<?php echo esc_url(get_avatar_url($patient->ID, ['size' => 32])); ?>"
                                     alt="Avatar"
                                     class="w-7 h-7 md:w-8 md:h-8 rounded-full object-cover flex-shrink-0">
                                <span class="text-xs md:text-sm font-medium text-gray-700 truncate">
                                    <?php echo esc_html($patient->display_name); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Título personalizado -->
            <div class="mb-6">
                <label for="custom-title" class="block text-sm font-medium text-gray-700 mb-2">
                    Título personalizado (opcional)
                </label>
                <input type="text"
                       id="custom-title"
                       name="custom_title"
                       placeholder="Dejar vacío para usar el título original"
                       class="w-full px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <p class="text-xs text-gray-500 mt-1">El paciente verá este título en su dashboard</p>
            </div>

            <!-- Mensaje personalizado -->
            <div class="mb-6">
                <label for="custom-message" class="block text-sm font-medium text-gray-700 mb-2">
                    Mensaje personalizado (opcional)
                </label>
                <textarea id="custom-message"
                          name="custom_description"
                          rows="4"
                          placeholder="Hola, te comparto esta actividad que puede ayudarte..."
                          class="w-full px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none"></textarea>
                <p class="text-xs text-gray-500 mt-1">Este mensaje aparecerá junto con la actividad</p>
            </div>

            <!-- Fecha límite -->
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                    <label for="due-date" class="block text-sm font-medium text-gray-700">
                        Fecha límite (opcional)
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="no-due-date" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                        <span class="text-xs md:text-sm text-gray-600">Sin fecha límite</span>
                    </label>
                </div>
                <input type="date"
                       id="due-date"
                       name="due_date"
                       min="<?php echo date('Y-m-d'); ?>"
                       disabled
                       class="w-full px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-50 disabled:text-gray-500">
            </div>
        </form>

        <!-- Footer -->
        <div class="flex gap-3 p-4 md:p-6 pt-4 border-t border-gray-200 flex-shrink-0">
            <button type="button"
                    onclick="closeAssignModal()"
                    class="flex-1 px-4 py-2 text-sm md:text-base bg-gray-100 text-gray-700 rounded-lg font-medium transition-all hover:bg-gray-200 border-0 cursor-pointer">
                Cancelar
            </button>
            <button type="submit"
                    form="assign-form"
                    id="assign-submit-btn"
                    class="flex-1 px-4 py-2 text-sm md:text-base bg-primary-600 text-white rounded-lg font-medium transition-all hover:bg-primary-700 border-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="btn-text">Asignar</span>
                <span class="btn-loading hidden">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                    Asignando...
                </span>
            </button>
        </div>
    </div>
</div>

<style>
    .tab-activity {
        border-color: transparent;
    }
    .tab-activity.active {
        color: #2563eb;
        border-color: #2563eb;
    }
    .filter-type {
        background: white;
        border-color: #e5e7eb;
        color: #6b7280;
    }
    .filter-type.active {
        background: #eff6ff;
        border-color: #2563eb;
        color: #2563eb;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
</style>

<script>
    // Tabs
    document.querySelectorAll('.tab-activity').forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;

            document.querySelectorAll('.tab-activity').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            const targetTab = document.getElementById(`tab-${tab}`);
            if (targetTab) {
                targetTab.style.display = 'block';
            }
        });
    });

    // Filtros de tipo
    document.querySelectorAll('.filter-type').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-type').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterActivities();
        });
    });

    // Búsqueda
    document.getElementById('search-activities').addEventListener('input', filterActivities);

    function filterActivities() {
        const searchTerm = document.getElementById('search-activities').value.toLowerCase();
        const activeType = document.querySelector('.filter-type.active').dataset.type;
        const cards = document.querySelectorAll('.activity-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardTypes = card.dataset.types ? card.dataset.types.split(',') : [];
            const cardTitle = card.dataset.title;
            const cardDescription = card.dataset.description;

            const matchesType = activeType === 'all' || cardTypes.includes(activeType);
            const matchesSearch = searchTerm === '' || cardTitle.includes(searchTerm) || cardDescription.includes(searchTerm);

            if (matchesType && matchesSearch) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Mostrar mensaje de "no results"
        const noResults = document.getElementById('no-results');
        const grid = document.getElementById('activities-grid');
        if (visibleCount === 0) {
            grid.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            grid.classList.remove('hidden');
            noResults.classList.add('hidden');
        }
    }

    // Modal - USANDO ModalUtils
    function openAssignModal(activityId, activityTitle) {
        document.getElementById('library-activity-id').value = activityId;
        document.getElementById('modal-activity-title').textContent = activityTitle;
        document.getElementById('custom-title').placeholder = activityTitle;
        ModalUtils.openModal('assign-modal');
    }

    function closeAssignModal() {
        ModalUtils.closeModal('assign-modal');
        document.getElementById('assign-form').reset();
    }

    // Select all patients
    document.getElementById('select-all-patients')?.addEventListener('change', function() {
        document.querySelectorAll('.patient-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Toggle fecha límite
    document.getElementById('no-due-date')?.addEventListener('change', function() {
        const dueDateInput = document.getElementById('due-date');
        dueDateInput.disabled = this.checked;
        if (this.checked) {
            dueDateInput.value = '';
        }
    });

    // Submit form
    document.getElementById('assign-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const selectedPatients = document.querySelectorAll('.patient-checkbox:checked');
        if (selectedPatients.length === 0) {
            Toast.show('Selecciona al menos un paciente', 'error');
            return;
        }

        const btn = document.getElementById('assign-submit-btn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');

        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');

        const formData = new FormData(this);
        formData.append('action', 'openmind_assign_activity');
        formData.append('nonce', openmindData.nonce);

        // Asignar a cada paciente seleccionado
        let successCount = 0;
        let errorCount = 0;

        for (const checkbox of selectedPatients) {
            const patientFormData = new FormData();
            patientFormData.append('action', 'openmind_assign_activity');
            patientFormData.append('nonce', openmindData.nonce);
            patientFormData.append('library_activity_id', formData.get('library_activity_id'));
            patientFormData.append('patient_id', checkbox.value);
            patientFormData.append('custom_title', formData.get('custom_title'));
            patientFormData.append('custom_description', formData.get('custom_description'));

            // Solo enviar fecha si el checkbox está desmarcado Y hay valor
            const noDueDate = document.getElementById('no-due-date').checked;
            const dueDateValue = formData.get('due_date');
            if (!noDueDate && dueDateValue) {
                patientFormData.append('due_date', dueDateValue);
            }

            try {
                const response = await fetch(openmindData.ajaxUrl, {
                    method: 'POST',
                    body: patientFormData
                });
                const data = await response.json();

                if (data.success) {
                    successCount++;
                } else {
                    errorCount++;
                }
            } catch (error) {
                errorCount++;
            }
        }

        btn.disabled = false;
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');

        if (successCount > 0) {
            Toast.show(`Actividad asignada a ${successCount} paciente(s)`, 'success');
            closeAssignModal();

            // Recargar después de 1 segundo
            setTimeout(() => location.reload(), 1000);
        } else {
            Toast.show('Error al asignar actividad', 'error');
        }
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAssignModal();
        }
    });
</script>