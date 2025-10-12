<?php
// templates/pages/patient/actividades.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$assignments = \Openmind\Controllers\ActivityController::getPatientAssignments($user_id);
$pending = array_filter($assignments, fn($a) => get_post_meta($a->ID, 'status', true) === 'pending');
$completed = array_filter($assignments, fn($a) => get_post_meta($a->ID, 'status', true) === 'completed');
?>

<div class="max-w-7xl">
    <h1 class="text-2xl font-normal text-gray-900 mb-6">Mis Actividades</h1>

    <div class="bg-white px-6 rounded-xl shadow-sm flex items-center gap-5 mb-4">
        <div class="flex gap-2 border-b-2 border-gray-200 overflow-x-auto">
            <button class="tab-activity active flex items-center gap-2 px-6 py-3 bg-transparent border-0 border-b-4 cursor-pointer text-lg font-medium text-dark-gray-300 transition-all whitespace-nowrap rounded-t-lg" data-filter="pending">
                Pendientes
                <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 bg-primary-600 text-white text-xs font-semibold rounded-full"><?php echo count($pending); ?></span>
            </button>
            <button class="tab-activity flex items-center gap-2 px-6 py-3 bg-transparent border-0 border-b-4 border-transparent cursor-pointer text-lg font-medium text-dark-gray-300 transition-all whitespace-nowrap rounded-t-lg hover:text-gray-900 hover:bg-gray-50" data-filter="completed">
                Completadas
                <span class="inline-flex items-center justify-center min-w-6 h-6 px-2 bg-green-100 text-green-700 text-xs font-semibold rounded-full"><?php echo count($completed); ?></span>
            </button>
        </div>
    </div>

    <?php if (empty($assignments)): ?>
        <div class="bg-white rounded-xl shadow-sm p-16 text-center">
            <div class="text-6xl mb-4">📋</div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">No tienes actividades asignadas</h3>
            <p class="text-gray-600">Tu psicólogo te asignará actividades próximamente</p>
        </div>
    <?php else: ?>
        <div class="activities-container">
            <div class="activities-list" data-status="pending" style="display: block;">
                <?php if (empty($pending)): ?>
                    <div class="bg-white rounded-xl shadow-sm p-16 text-center">
                        <div class="text-6xl mb-4">✅</div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">¡Excelente trabajo!</h3>
                        <p class="text-gray-600">No tienes actividades pendientes</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($pending as $assignment):
                            $library_id = $assignment->post_parent;
                            $type = get_post_meta($library_id, '_activity_type', true);
                            $due_date = get_post_meta($assignment->ID, 'due_date', true);
                            $psychologist_id = get_post_meta($assignment->ID, 'psychologist_id', true);
                            $psychologist = get_userdata($psychologist_id);
                            $response_count = get_post_meta($assignment->ID, 'response_count', true);
                            ?>
                            <a href="<?php echo add_query_arg(['view' => 'actividad-detalle', 'activity_id' => $assignment->ID], home_url('/dashboard-paciente/')); ?>" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all no-underline">
                                <div class="flex items-start justify-between mb-4">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                                        <i class="fa-solid fa-file"></i>
                                        <?php echo ucfirst($type); ?>
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 bg-orange-100 text-orange-700 rounded-full">
                                        <i class="fa-solid fa-clock"></i> Pendiente
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo esc_html($assignment->post_title); ?></h3>
                                <?php if ($assignment->post_content): ?>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?php echo wp_trim_words(strip_tags($assignment->post_content), 15); ?></p>
                                <?php endif; ?>
                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <?php echo get_avatar($psychologist->ID, 24, '', '', ['class' => 'rounded-full']); ?>
                                        <span class="text-gray-600"><?php echo esc_html($psychologist->display_name); ?></span>
                                    </div>
                                    <?php if ($due_date): ?>
                                        <span class="text-gray-500"><i class="fa-solid fa-calendar mr-1"></i><?php echo date('d/m', strtotime($due_date)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($response_count > 0): ?>
                                    <div class="mt-3 text-xs text-primary-600">
                                        <i class="fa-solid fa-comments mr-1"></i><?php echo $response_count; ?> respuesta<?php echo $response_count > 1 ? 's' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="activities-list" data-status="completed" style="display: none;">
                <?php if (empty($completed)): ?>
                    <div class="bg-white rounded-xl shadow-sm p-16 text-center">
                        <div class="text-6xl mb-4">🎯</div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Aún no has completado actividades</h3>
                        <p class="text-gray-600">Completa tus primeras actividades para verlas aquí</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($completed as $assignment):
                            $library_id = $assignment->post_parent;
                            $type = get_post_meta($library_id, '_activity_type', true);
                            $completed_at = get_post_meta($assignment->ID, 'completed_at', true);
                            $psychologist_id = get_post_meta($assignment->ID, 'psychologist_id', true);
                            $psychologist = get_userdata($psychologist_id);
                            $response_count = get_post_meta($assignment->ID, 'response_count', true);
                            ?>
                            <a href="<?php echo add_query_arg(['view' => 'actividad-detalle', 'activity_id' => $assignment->ID], home_url('/dashboard-paciente/')); ?>" class="block bg-gray-50 rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-all no-underline opacity-75">
                                <div class="flex items-start justify-between mb-4">
                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                                        <i class="fa-solid fa-file"></i>
                                        <?php echo ucfirst($type); ?>
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 bg-green-100 text-green-700 rounded-full">
                                        <i class="fa-solid fa-check"></i> Completada
                                    </span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo esc_html($assignment->post_title); ?></h3>
                                <?php if ($assignment->post_content): ?>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?php echo wp_trim_words(strip_tags($assignment->post_content), 15); ?></p>
                                <?php endif; ?>
                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <?php echo get_avatar($psychologist->ID, 24, '', '', ['class' => 'rounded-full']); ?>
                                        <span class="text-gray-600"><?php echo esc_html($psychologist->display_name); ?></span>
                                    </div>
                                    <span class="text-gray-500"><i class="fa-solid fa-check-circle mr-1"></i><?php echo date('d/m', strtotime($completed_at)); ?></span>
                                </div>
                                <?php if ($response_count > 0): ?>
                                    <div class="mt-3 text-xs text-primary-600">
                                        <i class="fa-solid fa-comments mr-1"></i><?php echo $response_count; ?> respuesta<?php echo $response_count > 1 ? 's' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.tab-activity').forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;
            document.querySelectorAll('.tab-activity').forEach(b => {
                b.classList.remove('active', 'border-primary-500');
                b.classList.add('border-transparent');
            });
            btn.classList.add('active', 'border-primary-500');
            btn.classList.remove('border-transparent');
            document.querySelectorAll('.activities-list').forEach(list => {
                list.style.display = list.dataset.status === filter ? 'block' : 'none';
            });
        });
    });
</script>