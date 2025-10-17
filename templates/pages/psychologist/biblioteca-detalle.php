<?php
// templates/pages/psychologist/biblioteca-detalle.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
if (!current_user_can('manage_patients')) wp_die('Acceso denegado');

$library_id = isset($_GET['library_id']) ? absint($_GET['library_id']) : 0;
$activity = get_post($library_id);

if (!$activity || $activity->post_type !== 'activity') {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Actividad no encontrada en la biblioteca
    </div>';
    return;
}

// Obtener recursos
$resources = get_post_meta($library_id, '_activity_resources', true) ?: [];

// Fallback para actividades antiguas
if (empty($resources)) {
    $old_type = get_post_meta($library_id, '_activity_type', true);
    if ($old_type) {
        $resources = [[
            'type' => $old_type,
            'file_id' => get_post_meta($library_id, '_activity_file', true) ?: '',
            'url' => get_post_meta($library_id, '_activity_url', true) ?: '',
            'title' => '',
            'order' => 0
        ]];
    }
}

// Extraer tipos únicos para badges
$types = array_unique(array_column($resources, 'type'));
?>

    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="mb-6">
            <a href="<?php echo add_query_arg('view', 'actividades', home_url('/dashboard-psicologo/')); ?>"
               class="inline-flex items-center gap-2 text-gray-600 hover:text-primary-600 transition-colors mb-4 no-underline">
                <i class="fa-solid fa-arrow-left"></i>
                Volver a Biblioteca
            </a>

            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">
                        <?php echo esc_html($activity->post_title); ?>
                    </h1>

                    <!-- Badges de tipos -->
                    <?php if (!empty($types)): ?>
                        <div class="flex gap-2 mb-4">
                            <?php
                            $type_labels = [
                                'pdf' => ['icon' => 'fa-file-pdf', 'color' => 'bg-red-100 text-red-700'],
                                'youtube' => ['icon' => 'fa-brands fa-youtube', 'color' => 'bg-red-100 text-red-600'],
                                'link' => ['icon' => 'fa-link', 'color' => 'bg-blue-100 text-blue-700']
                            ];

                            foreach ($types as $type):
                                $label = $type_labels[$type] ?? ['icon' => 'fa-file', 'color' => 'bg-gray-100 text-gray-700'];
                                ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 <?php echo esc_attr($label['color']); ?> rounded-full text-sm font-medium">
                                <i class="fa-solid <?php echo esc_attr($label['icon']); ?>"></i>
                                <?php echo esc_html(strtoupper($type)); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card principal -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">

            <!-- Descripción -->
            <?php if (!empty($activity->post_content)): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Descripción</h3>
                    <div class="prose prose-sm max-w-none text-gray-700">
                        <?php echo wpautop(wp_kses_post($activity->post_content)); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recursos -->
            <?php if (!empty($resources)): ?>
                <div class="pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-book-open text-primary-600"></i>
                        Recursos de la actividad (<?php echo count($resources); ?>)
                    </h3>
                    <?php
                    // ✅ REUTILIZAR componente existente
                    $resources_args = ['resources' => $resources];
                    include OPENMIND_PATH . 'templates/components/activity-resources.php';
                    ?>
                </div>
            <?php else: ?>
                <div class="pt-6 border-t border-gray-200">
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <i class="fa-solid fa-inbox text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600">Esta actividad no tiene recursos agregados</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botones de acción -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="<?php echo add_query_arg('view', 'actividades', home_url('/dashboard-psicologo/')); ?>"
                   class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium transition-all hover:bg-gray-200 no-underline">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver a Biblioteca
                </a>

                <button onclick="openAssignModal(<?php echo $library_id; ?>, '<?php echo esc_js($activity->post_title); ?>')"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-lg font-medium transition-all hover:bg-primary-700 border-0 cursor-pointer">
                    <i class="fa-solid fa-user-plus"></i>
                    Asignar a Pacientes
                </button>
            </div>
        </div>

        <!-- Info adicional -->
        <div class="mt-4 text-center text-sm text-gray-500">
            <p class="mb-1">
                <i class="fa-solid fa-calendar-plus mr-1"></i>
                Creada el <?php echo date('d/m/Y', strtotime($activity->post_date)); ?>
            </p>
            <?php if ($activity->post_modified !== $activity->post_date): ?>
                <p>
                    <i class="fa-solid fa-pencil mr-1"></i>
                    Última modificación: <?php echo date('d/m/Y', strtotime($activity->post_modified)); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Incluir el modal de asignar (que ya existe en actividades.php) -->
<?php
// Solo incluir el modal si no está ya en la página
if (!did_action('openmind_assign_modal_loaded')):
    // Obtener pacientes para el modal
    global $wpdb;
    $patients = $wpdb->get_results($wpdb->prepare("
        SELECT u.ID, u.display_name, u.user_email
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->prefix}openmind_relationships r ON u.ID = r.patient_id
        WHERE r.psychologist_id = %d
        ORDER BY u.display_name ASC
    ", $user_id));
    ?>

    <!-- Modal: Asignar Actividad -->
    <div id="assign-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-xl">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-1">Asignar Actividad</h2>
                        <p id="modal-activity-title" class="text-sm text-gray-600"></p>
                    </div>
                    <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <form id="assign-form" class="p-6">
                <input type="hidden" id="library-activity-id" name="library_activity_id">

                <!-- Selección de pacientes -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Selecciona paciente(s) *
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="select-all-patients" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-600">Seleccionar todos</span>
                        </label>
                    </div>

                    <?php if (empty($patients)): ?>
                        <div class="text-center py-8 bg-gray-50 rounded-lg">
                            <p class="text-gray-600 mb-2">No tienes pacientes asignados</p>
                            <a href="<?php echo add_query_arg('view', 'pacientes', home_url('/dashboard-psicologo/')); ?>"
                               class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                Ir a Pacientes
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-3">
                            <?php foreach ($patients as $patient): ?>
                                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="checkbox"
                                           name="patient_ids[]"
                                           value="<?php echo $patient->ID; ?>"
                                           class="patient-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <img src="<?php echo esc_url(get_avatar_url($patient->ID, ['size' => 32])); ?>"
                                         alt="Avatar"
                                         class="w-8 h-8 rounded-full object-cover">
                                    <span class="text-sm font-medium text-gray-700">
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
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
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
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Este mensaje aparecerá junto con la actividad</p>
                </div>

                <!-- Fecha límite -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <label for="due-date" class="block text-sm font-medium text-gray-700">
                            Fecha límite (opcional)
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="no-due-date" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" checked>
                            <span class="text-sm text-gray-600">Sin fecha límite</span>
                        </label>
                    </div>
                    <input type="date"
                           id="due-date"
                           name="due_date"
                           min="<?php echo date('Y-m-d'); ?>"
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-50 disabled:text-gray-500">
                </div>

                <!-- Botones -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="button"
                            onclick="closeAssignModal()"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium transition-all hover:bg-gray-200 border-0 cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                            id="assign-submit-btn"
                            class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg font-medium transition-all hover:bg-primary-700 border-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="btn-text">Asignar Actividad</span>
                        <span class="btn-loading hidden">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                        Asignando...
                    </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openAssignModal(activityId, activityTitle) {
            document.getElementById('library-activity-id').value = activityId;
            document.getElementById('modal-activity-title').textContent = activityTitle;
            document.getElementById('custom-title').placeholder = activityTitle;
            document.getElementById('assign-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAssignModal() {
            document.getElementById('assign-modal').classList.add('hidden');
            document.getElementById('assign-form').reset();
            document.body.style.overflow = '';
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

    <?php
    do_action('openmind_assign_modal_loaded');
endif;
?>