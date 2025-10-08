<?php
// templates/pages/psychologist/actividades.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

$activities = get_posts([
        'post_type' => 'activity',
        'author' => $user_id,
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
]);
?>

<div class="max-w-7xl">
    <!-- Header con botón -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-normal text-gray-900 m-0">Actividades</h1>
        <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg border-0 cursor-pointer text-sm font-medium transition-all hover:bg-primary-700 hover:-translate-y-0.5 hover:!shadow-lg shadow-none"
                id="create-activity">
            <i class="fa-solid fa-plus"></i>
            Crear Actividad
        </button>
    </div>

    <?php if (empty($activities)): ?>
        <!-- Empty State -->
        <div class="text-center py-20 bg-white rounded-xl shadow-sm">
            <div class="text-6xl mb-4">📋</div>
            <p class="text-lg text-gray-600 mb-6">No has creado actividades aún.</p>
            <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg border-0 cursor-pointer text-sm font-medium transition-all hover:bg-gray-300"
                    id="create-first-activity">
                <i class="fa-solid fa-clipboard-list"></i>
                Crear primera actividad
            </button>
        </div>
    <?php else: ?>
        <!-- Lista de actividades -->
        <div class="flex flex-col gap-4">
            <?php foreach ($activities as $activity):
                $assigned_to = get_post_meta($activity->ID, 'assigned_to', true);
                $patient = $assigned_to ? get_userdata($assigned_to) : null;
                $due_date = get_post_meta($activity->ID, 'due_date', true);
                $completed = get_post_meta($activity->ID, 'completed', true);
                $is_overdue = $due_date && strtotime($due_date) < current_time('timestamp') && !$completed;
                ?>
                <div class="bg-white border border-gray-200 rounded-xl p-6 transition-all hover:shadow-md <?php echo $is_overdue ? 'border-l-4 border-l-red-500' : ''; ?>">
                    <!-- Header de la actividad -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">
                                <?php echo esc_html($activity->post_title); ?>
                                <?php if ($completed): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                        <i class="fa-solid fa-check-circle"></i>
                                        Completada
                                    </span>
                                <?php endif; ?>
                            </h3>

                            <!-- Fecha de vencimiento -->
                            <?php if ($due_date): ?>
                                <span class="inline-flex items-center gap-2 text-xs font-medium px-3 py-1 rounded-full <?php echo $is_overdue ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'; ?>">
                                    <i class="fa-solid fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($due_date)); ?>
                                    <?php if ($is_overdue): ?>
                                        <span class="font-semibold">VENCIDA</span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Acciones -->
                        <div class="flex items-center gap-2">
                            <button class="inline-flex items-center justify-center w-9 h-9 rounded-lg border-0 bg-gray-100 text-gray-600 cursor-pointer transition-all hover:bg-gray-200 hover:text-gray-900"
                                    data-action="edit-activity"
                                    data-id="<?php echo $activity->ID; ?>"
                                    title="Editar">
                                <i class="fa-solid fa-pencil"></i>
                            </button>
                            <button class="inline-flex items-center justify-center w-9 h-9 rounded-lg border-0 bg-primary-100 text-primary-600 cursor-pointer transition-all hover:bg-primary-200 hover:text-primary-700"
                                    data-action="assign-activity"
                                    data-id="<?php echo $activity->ID; ?>"
                                    title="Asignar paciente">
                                <i class="fa-solid fa-user-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Contenido/Descripción -->
                    <?php if (!empty($activity->post_content)): ?>
                        <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                            <?php echo wp_trim_words($activity->post_content, 30); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Meta información -->
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                        <?php if ($patient): ?>
                            <div class="flex items-center gap-2">
                                <?php echo get_avatar($patient->ID, 24, '', '', ['class' => 'rounded-full']); ?>
                                <span class="text-sm font-medium text-gray-700">
                                    <?php echo esc_html($patient->display_name); ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 text-sm text-gray-500">
                                <i class="fa-solid fa-user-slash"></i>
                                Sin asignar
                            </span>
                        <?php endif; ?>

                        <?php if (!$completed): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-700 text-xs font-medium rounded-full ml-auto">
                                <i class="fa-solid fa-hourglass-half"></i>
                                Pendiente
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Crear actividad
        const createBtns = document.querySelectorAll('#create-activity, #create-first-activity');
        createBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // TODO: Abrir modal o redirigir a formulario de creación
                alert('Funcionalidad de crear actividad - Por implementar');
            });
        });

        // Editar actividad
        document.addEventListener('click', function(e) {
            if (e.target.closest('[data-action="edit-activity"]')) {
                const btn = e.target.closest('[data-action="edit-activity"]');
                const activityId = btn.dataset.id;
                // TODO: Abrir modal de edición
                alert('Editar actividad ID: ' + activityId);
            }
        });

        // Asignar paciente
        document.addEventListener('click', function(e) {
            if (e.target.closest('[data-action="assign-activity"]')) {
                const btn = e.target.closest('[data-action="assign-activity"]');
                const activityId = btn.dataset.id;
                // TODO: Abrir modal de asignación
                alert('Asignar paciente a actividad ID: ' + activityId);
            }
        });
    });
</script>