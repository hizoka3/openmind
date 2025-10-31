<?php
// templates/components/modal-asignar-actividad.php
if (!defined('ABSPATH')) exit;

$library_activities = \Openmind\Controllers\ActivityController::getLibraryActivities();
?>

<!-- Modal: Asignar Actividad -->
<div id="assign-activity-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" data-lenis-prevent>
    <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] flex flex-col" data-lenis-prevent>
        <div class="p-6 border-b border-gray-200 flex-shrink-0">
            <h2 class="text-2xl font-bold text-gray-900">Asignar Actividad</h2>
            <p class="text-sm text-gray-600 mt-1">Selecciona una actividad de la biblioteca y personalízala</p>
        </div>

        <form id="assign-activity-form" class="overflow-y-auto p-6 space-y-6 flex-1" data-modal-scroll style="overscroll-behavior: contain;">
            <input type="hidden" id="assign-patient-id" name="patient_id">

            <!-- Seleccionar de biblioteca -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Actividad de la Biblioteca *</label>
                <div class="grid grid-cols-1 gap-3 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    <?php if (empty($library_activities)): ?>
                        <p class="text-sm text-gray-500 text-center py-8">
                            No hay actividades en la biblioteca.<br>
                            <a href="<?php echo admin_url('admin.php?page=openmind-biblioteca'); ?>" class="text-primary-600 hover:text-primary-700">Crear actividades en wp-admin</a>
                        </p>
                    <?php else: ?>
                        <?php foreach ($library_activities as $activity):
                            $type = get_post_meta($activity->ID, '_activity_type', true);
                            $type_icons = [
                                    'pdf' => 'fa-file-pdf text-red-600',
                                    'video' => 'fa-video text-blue-600',
                                    'link' => 'fa-link text-green-600',
                                    'youtube' => 'fa-brands fa-youtube text-red-600'
                            ];
                            ?>
                            <label class="library-activity-option flex items-start gap-3 p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition-all">
                                <input type="radio" name="library_activity_id" value="<?php echo $activity->ID; ?>" class="mt-1" required onchange="selectLibraryActivity(<?php echo $activity->ID; ?>, '<?php echo esc_js($activity->post_title); ?>', '<?php echo esc_js(wp_trim_words($activity->post_content, 50)); ?>')">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i class="fa-solid <?php echo $type_icons[$type] ?? 'fa-file'; ?>"></i>
                                        <span class="font-medium text-gray-900"><?php echo esc_html($activity->post_title); ?></span>
                                    </div>
                                    <?php if ($activity->post_content): ?>
                                        <p class="text-sm text-gray-600 line-clamp-2">
                                            <?php echo wp_trim_words(strip_tags($activity->post_content), 15); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TÃƒÂ­tulo personalizado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    TÃƒÂ­tulo personalizado
                    <span class="text-gray-500 font-normal">(opcional - se usa el de biblioteca si estÃƒÂ¡ vacÃƒÂ­o)</span>
                </label>
                <input type="text" id="assign-custom-title" name="custom_title" placeholder="Ej: Ayuda para controlar tu ansiedad" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
            </div>

            <!-- DescripciÃƒÂ³n personalizada -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mensaje para el paciente
                    <span class="text-gray-500 font-normal">(opcional)</span>
                </label>
                <textarea id="assign-custom-description" name="custom_description" rows="4" placeholder="Ej: MarÃƒÂ­a, te dejÃƒÂ© esta actividad para que complementemos la sesiÃƒÂ³n..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
            </div>

            <!-- Fecha límite (OPCIONAL) -->
            <div>
                <label class="flex items-center gap-2 mb-3">
                    <input type="checkbox" id="has-due-date" onchange="toggleDueDate()" class="rounded border-gray-300">
                    <span class="text-sm font-medium text-gray-700">Agregar fecha límite (opcional)</span>
                </label>
                <input type="date" id="assign-due-date" name="due_date" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                <p class="text-xs text-gray-500 mt-1">💡 Tip: Evita presionar al paciente con fechas si no es necesario</p>
            </div>
        </form>

        <!-- Footer con botones fijos -->
        <div class="p-6 border-t border-gray-200 flex gap-3 flex-shrink-0">
            <button type="submit" form="assign-activity-form" class="flex-1 px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                Asignar Actividad
            </button>
            <button type="button" onclick="closeAssignModal()" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
    function openAssignModal(patientId) {
        document.getElementById('assign-patient-id').value = patientId;
        document.getElementById('assign-activity-form').reset();
        document.getElementById('has-due-date').checked = false;
        document.getElementById('assign-due-date').disabled = true;
        ModalUtils.openModal('assign-activity-modal');
    }

    function closeAssignModal() {
        ModalUtils.closeModal('assign-activity-modal');
    }

    function toggleDueDate() {
        const checkbox = document.getElementById('has-due-date');
        const dateInput = document.getElementById('assign-due-date');
        dateInput.disabled = !checkbox.checked;
        if (!checkbox.checked) {
            dateInput.value = '';
        }
    }

    function selectLibraryActivity(activityId, title, description) {
        // Visual feedback
        document.querySelectorAll('.library-activity-option').forEach(el => {
            el.classList.remove('border-primary-500', 'bg-primary-50');
        });
        event.target.closest('.library-activity-option').classList.add('border-primary-500', 'bg-primary-50');

        // Pre-fill custom title if empty
        const customTitleInput = document.getElementById('assign-custom-title');
        if (!customTitleInput.value) {
            customTitleInput.placeholder = title;
        }
    }

    // Submit form
    document.getElementById('assign-activity-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        formData.append('action', 'openmind_assign_activity');
        formData.append('nonce', openmindData.nonce);

        try {
            const response = await fetch(openmindData.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Toast.show(data.data.message || 'Actividad asignada correctamente', 'success');
                closeAssignModal();
                location.reload();
            } else {
                Toast.show(data.data || 'Error al asignar actividad', 'error');
            }
        } catch (error) {
            console.error(error);
            Toast.show('Error de conexión', 'error');
        }
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('assign-activity-modal').addEventListener('click', (e) => {
        if (e.target.id === 'assign-activity-modal') {
            closeAssignModal();
        }
    });
</script>