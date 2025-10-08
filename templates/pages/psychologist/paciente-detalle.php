<?php // templates/pages/patient/diario-detalle.php
if (!defined('ABSPATH')) exit;

$patient_id = intval($_GET['patient_id'] ?? 0);
$patient = get_userdata($patient_id);

$psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);
if (!$patient || $psychologist_id != get_current_user_id()) {
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-center my-6">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        Paciente no encontrado o no tienes permisos para verlo.
    </div>';
    return;
}

$all_activities = get_posts([
        'post_type' => 'activity',
        'meta_query' => [['key' => 'assigned_to', 'value' => $patient_id, 'compare' => '=']],
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
]);

$completed_activities = array_filter($all_activities, fn($a) => get_post_meta($a->ID, 'completed', true) == 1);
$pending_activities = array_filter($all_activities, fn($a) => !get_post_meta($a->ID, 'completed', true));
$diary_entries = \Openmind\Repositories\DiaryRepository::getByPatient($patient_id, 10);
$private_diary_entries = \Openmind\Repositories\DiaryRepository::getByPatient($patient_id, 10, true);
$completion_rate = count($all_activities) > 0 ? round((count($completed_activities) / count($all_activities)) * 100) : 0;
?>

<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="?view=pacientes" class="inline-flex items-center gap-2 text-primary-600 text-sm font-medium transition-colors hover:text-primary-700 no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Pacientes
        </a>
    </div>

    <!-- Header del Paciente -->
    <div class="bg-white rounded-2xl p-8 mb-4 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex gap-6 items-start">
                <?php echo get_avatar($patient->ID, 100, '', '', ['class' => 'rounded-2xl border-4 border-gray-100']); ?>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 m-0 mb-3"><?php echo esc_html($patient->display_name); ?></h1>
                    <p class="flex items-center gap-2 text-gray-600 text-sm my-1 m-0">
                        <i class="fa-solid fa-envelope"></i>
                        <?php echo esc_html($patient->user_email); ?>
                    </p>
                    <p class="flex items-center gap-2 text-gray-600 text-sm my-1 m-0">
                        <i class="fa-solid fa-calendar-check"></i>
                        Paciente desde <?php echo date('d/m/Y', strtotime($patient->user_registered)); ?>
                    </p>
                </div>
            </div>

            <div class="flex gap-3 flex-col md:flex-row w-full md:w-auto">
                <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'patient_id' => $patient_id]); ?>"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-500 text-white rounded-lg text-sm font-medium transition-all hover:bg-primary-600 hover:-translate-y-0.5 hover:shadow-lg no-underline">
                    <i class="fa-solid fa-message"></i>
                    Enviar Mensaje
                </a>
                <button class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg border-0 cursor-pointer text-sm font-medium transition-all hover:bg-gray-300" id="assign-new-activity">
                    <i class="fa-solid fa-clipboard-list"></i>
                    Asignar Actividad
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="bg-white px-6 py-2.5 rounded-xl shadow-sm flex items-center gap-5 transition-all hover:-translate-y-1 hover:shadow-md">
            <div class="text-3xl w-12 h-12 flex items-center justify-center bg-primary-200 rounded-xl text-primary-500">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <h3 class="text-xl font-normal text-primary-500 m-0"><?php echo count($all_activities); ?></h3>
                <p class="text-sm text-gray-500 mt-1 m-0">Actividades</p>
            </div>
        </div>

        <div class="bg-white px-6 py-2.5 rounded-xl shadow-sm flex items-center gap-5 transition-all hover:-translate-y-1 hover:shadow-md">
            <div class="text-3xl w-12 h-12 flex items-center justify-center bg-primary-200 rounded-xl text-primary-500">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div>
                <h3 class="text-xl font-normal text-primary-500 m-0"><?php echo count($completed_activities); ?></h3>
                <p class="text-sm text-gray-500 mt-1 m-0">Completadas</p>
            </div>
        </div>

        <div class="bg-white px-6 py-2.5 rounded-xl shadow-sm flex items-center gap-5 transition-all hover:-translate-y-1 hover:shadow-md">
            <div class="text-3xl w-12 h-12 flex items-center justify-center bg-primary-200 rounded-xl text-primary-500">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h3 class="text-xl font-normal text-primary-500 m-0"><?php echo count($pending_activities); ?></h3>
                <p class="text-sm text-gray-500 mt-1 m-0">Pendientes</p>
            </div>
        </div>

        <div class="bg-white px-6 py-2.5 rounded-xl shadow-sm flex items-center gap-5 transition-all hover:-translate-y-1 hover:shadow-md">
            <div class="text-3xl w-12 h-12 flex items-center justify-center bg-primary-200 rounded-xl text-primary-500">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <h3 class="text-xl font-normal text-primary-500 m-0"><?php echo $completion_rate; ?>%</h3>
                <p class="text-sm text-gray-500 mt-1 m-0">Realizadas</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white px-6 rounded-xl shadow-sm flex items-center gap-5 mb-4">
        <div class="flex gap-2 border-b-2 border-gray-200 overflow-x-auto">
            <button class="tab-btn active flex items-center gap-2 px-6 py-3 bg-transparent border-0 border-b-4 cursor-pointer text-lg font-medium text-dark-gray-300 transition-all whitespace-nowrap rounded-t-lg" data-tab="actividades">
                Actividades
            </button>
            <button class="tab-btn flex items-center gap-2 px-6 py-3 bg-transparent border-0 border-b-4 border-transparent cursor-pointer text-lg font-medium text-dark-gray-300 transition-all whitespace-nowrap rounded-t-lg hover:text-gray-900 hover:bg-gray-50" data-tab="bitacora">
                Bitácora
            </button>
            <button class="tab-btn flex items-center gap-2 px-6 py-3 bg-transparent border-0 border-b-4 border-transparent cursor-pointer text-lg font-medium text-dark-gray-300 transition-all whitespace-nowrap rounded-t-lg hover:text-gray-900 hover:bg-gray-50" data-tab="diario">
                Diario de vida
            </button>
            <button class="tab-btn flex items-center gap-2 px-6 py-3 bg-transparent border-0 border-b-4 border-transparent cursor-pointer text-lg font-medium text-dark-gray-300 transition-all whitespace-nowrap rounded-t-lg hover:text-gray-900 hover:bg-gray-50" data-tab="mensajes">
                Mensajes
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="min-h-96 bg-white p-6 rounded-xl shadow-sm">
        <!-- Tab Actividades -->
        <div class="tab-pane" id="tab-actividades" style="display: block;">

            <?php if (empty($all_activities)): ?>
                <div class="text-center py-16 text-gray-400">
                    <div class="text-6xl mb-4">📋</div>
                    <p class="text-lg mb-4 not-italic text-gray-600">No hay actividades asignadas a este paciente.</p>
                    <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg border-0 cursor-pointer text-sm font-medium transition-all hover:bg-gray-300" id="assign-first-activity">
                        <i class="fa-solid fa-plus"></i>
                        Asignar Primera Actividad
                    </button>
                </div>
            <?php else: ?>
                <!-- Completadas -->
                <?php if (!empty($completed_activities)): ?>
                    <div class="mb-10">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-gray-100">
                            <i class="fa-solid fa-check-circle mr-2 text-green-500"></i>
                            Completadas (<?php echo count($completed_activities); ?>)
                        </h3>
                        <div class="flex flex-col gap-4">
                            <?php foreach ($completed_activities as $activity):
                                $completed_at = get_post_meta($activity->ID, 'completed_at', true);
                                ?>
                                <div class="bg-gray-50 opacity-75 border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md">
                                    <h4 class="text-base font-semibold text-gray-900 flex items-center gap-2 m-0">
                                        <?php echo esc_html($activity->post_title); ?>
                                    </h4>
                                    <?php if ($completed_at): ?>
                                        <span class="text-xs font-medium text-green-600 flex items-center gap-1 whitespace-nowrap">
                                            Completada el <?php echo date('d/m/Y', strtotime($completed_at)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Pendientes -->
                <?php if (!empty($pending_activities)): ?>
                    <div class="mb-10">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b-2 border-gray-100">
                            <i class="fa-solid fa-hourglass-half mr-2 text-orange-500"></i>
                            Pendientes (<?php echo count($pending_activities); ?>)
                        </h3>
                        <div class="flex flex-col gap-4">
                            <?php foreach ($pending_activities as $activity):
                                $due_date = get_post_meta($activity->ID, 'due_date', true);
                                $is_overdue = $due_date && strtotime($due_date) < current_time('timestamp');
                                ?>
                                <div class="bg-white border border-gray-200 rounded-xl p-5 transition-all hover:shadow-md">
                                    <h4 class="text-base font-semibold text-gray-900 mb-2 flex items-center gap-2 m-0">
                                        <?php echo esc_html($activity->post_title); ?>
                                    </h4>
                                    <p class="text-sm text-gray-600 mb-3 leading-relaxed m-0"><?php echo wp_trim_words($activity->post_content, 20); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Tab Bitácora -->
        <div class="tab-pane" id="tab-bitacora" style="display: none;">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 m-0">Bitácora del Paciente</h2>
                <div class="flex gap-3">
                    <a href="?view=bitacora&patient_id=<?php echo $patient_id; ?>"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium transition-all hover:bg-gray-200 no-underline">
                        <i class="fa-solid fa-book-open"></i>
                        Ver Todas
                    </a>
                    <a href="?view=bitacora-nueva&patient_id=<?php echo $patient_id; ?>&return=detalle"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white rounded-lg text-sm font-medium transition-all hover:bg-primary-600 hover:-translate-y-0.5 hover:shadow-lg no-underline">
                        <i class="fa-solid fa-plus"></i>
                        Nueva Bitácora
                    </a>
                </div>
            </div>

            <div class="bg-primary-50 border-l-4 border-primary-500 p-4 mb-6 rounded-lg">
                <div class="flex items-center">
                    <i class="fa-solid fa-info-circle text-primary-500 mr-3"></i>
                    <div>
                        <p class="text-sm text-dark-gray-300 m-0">
                            <strong>Bitácora de sesiones:</strong> Registro de las sesiones terapéuticas.
                            El paciente puede ver estas entradas en su dashboard.
                        </p>
                    </div>
                </div>
            </div>

            <?php
            // Obtener solo las últimas 5 entradas para preview
            $preview_entries = \Openmind\Repositories\SessionNoteRepository::getByPatient($patient_id, 5, 0);
            $total_entries = \Openmind\Repositories\SessionNoteRepository::countByPatient($patient_id);

            // Usar componente reutilizable
            $args = [
                    'patient_id' => $patient_id,
                    'entries' => $preview_entries,
                    'total' => $total_entries,
                    'per_page' => 5,
                    'current_page' => 1,
                    'show_actions' => true,
                    'context' => 'patient-detail',
                    'base_url' => ''
            ];
            include OPENMIND_PATH . 'templates/components/bitacora-list.php';

            // Si hay más de 5 entradas, mostrar botón para ver todas
            if ($total_entries > 5): ?>
                <div class="mt-6 text-center">
                    <a href="?view=bitacora&patient_id=<?php echo $patient_id; ?>"
                       class="inline-flex items-center gap-2 text-primary-600 font-medium hover:text-primary-700 transition-colors no-underline">
                        Ver las <?php echo $total_entries; ?> entradas completas
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Diario de vida -->
        <div class="tab-pane" id="tab-diario" style="display: none;">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 m-0 mb-2">Diario Compartido</h2>
                    <p class="text-sm text-gray-500 m-0">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Solo las entradas que <?php echo esc_html($patient->display_name); ?> ha decidido compartir contigo
                    </p>
                </div>

                <?php
                $shared_count = \Openmind\Repositories\DiaryRepository::countRecentSharedByPsychologist(get_current_user_id());
                if ($shared_count > 0):
                    ?>
                    <span class="inline-flex items-center gap-2 bg-primary-50 text-dark-gray-300 px-4 py-2 rounded-full text-sm font-medium">
                        <i class="fa-solid fa-bell"></i>
                        <?php echo $shared_count; ?> nueva<?php echo $shared_count > 1 ? 's' : ''; ?> (últimos 7 días)
                    </span>
                <?php endif; ?>
            </div>

            <div class="bg-primary-50 border-l-4 border-primary-500 p-4 mb-6 rounded-lg">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-info-circle text-primary-500 text-xl mt-1"></i>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-dark-gray-300 m-0 mb-1">
                            Solo entradas compartidas
                        </p>
                        <p class="text-sm text-dark-gray-300 m-0">
                            Solo puedes ver las entradas que <?php echo esc_html($patient->display_name); ?> ha decidido compartir contigo.
                        </p>
                    </div>
                </div>
            </div>

            <?php
            // Usar componente reutilizable
            $args = [
                    'user_id' => $patient_id,
                    'show_shared_only' => true,
                    'is_psychologist' => true,
                    'per_page' => 10
            ];
            include OPENMIND_PATH . 'templates/components/diary-list.php';
            ?>
        </div>

        <!-- Tab Mensajes -->
        <div class="tab-pane" id="tab-mensajes" style="display: none;">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 m-0 mb-2">Conversación con <?php echo esc_html($patient->display_name); ?></h2>
                    <p class="text-sm text-gray-500 m-0">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Preview de los últimos mensajes
                    </p>
                </div>
                <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'user_id' => $patient_id], home_url('/dashboard-psicologo/')); ?>"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white rounded-lg text-sm font-medium transition-all hover:bg-primary-600 hover:-translate-y-0.5 hover:shadow-lg no-underline">
                    <i class="fa-solid fa-comments"></i>
                    Abrir Chat Completo
                </a>
            </div>

            <?php
            // Obtener últimos 5 mensajes
            $last_messages = \Openmind\Repositories\MessageRepository::getConversationPaginated(
                    get_current_user_id(),
                    $patient_id,
                    5,
                    0
            );

            if (empty($last_messages)): ?>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-dashed border-blue-300 rounded-2xl p-16 text-center">
                    <div class="mb-6">
                        <i class="fa-solid fa-comments text-7xl text-blue-300"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2 m-0">
                        Sin conversación iniciada
                    </h3>
                    <p class="text-gray-600 m-0 mb-6 text-base">
                        No hay mensajes con este paciente aún.
                    </p>
                    <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'user_id' => $patient_id], home_url('/dashboard-psicologo/')); ?>"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white rounded-lg text-sm font-medium transition-all hover:bg-primary-600 hover:-translate-y-1 hover:shadow-lg no-underline">
                        <i class="fa-solid fa-message"></i>
                        Iniciar Conversación
                    </a>
                </div>
            <?php else: ?>
                <!-- Contenedor de mensajes con mejor diseño -->
                <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-2xl p-6 mb-6">
                    <div class="space-y-4">
                        <?php
                        // Invertir para mostrar cronológicamente (más antiguo arriba, más nuevo abajo)
                        $messages_display = array_reverse($last_messages);
                        foreach ($messages_display as $index => $msg):
                            $is_sent = $msg->sender_id == get_current_user_id();
                            ?>
                            <div class="flex gap-3 <?php if ($is_sent): ?>justify-end<?php else: ?>justify-start<?php endif; ?>">
                                <!-- Avatar paciente (izquierda) -->
                                <?php if (!$is_sent): ?>
                                    <div class="flex-shrink-0 mt-1">
                                        <?php echo get_avatar($patient_id, 32, '', '', ['class' => 'rounded-full border-2 border-white shadow-sm']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Burbuja de mensaje -->
                                <div class="flex flex-col <?php echo $is_sent ? 'items-end ml-auto' : 'items-start mr-auto'; ?> max-w-lg">
                                    <div class="px-4 py-3 rounded-2xl shadow-sm <?php echo $is_sent ? 'bg-primary-500 text-white rounded-br-sm' : 'bg-white text-gray-800 rounded-bl-sm border border-gray-200'; ?>">
                                        <p class="m-0 text-sm leading-relaxed" style="word-wrap: break-word;">
                                            <?php echo esc_html($msg->message); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1 px-1">
                                        <i class="fa-solid fa-clock text-xs <?php echo $is_sent ? 'text-primary-600' : 'text-gray-400'; ?>"></i>
                                        <span class="text-xs <?php echo $is_sent ? 'text-primary-600' : 'text-gray-500'; ?>">
                                            <?php
                                            $date = new DateTime($msg->created_at);
                                            $now = new DateTime();
                                            $diff = $now->diff($date);

                                            if ($diff->days == 0) {
                                                echo 'Hoy ' . $date->format('H:i');
                                            } elseif ($diff->days == 1) {
                                                echo 'Ayer ' . $date->format('H:i');
                                            } else {
                                                echo $date->format('d/m/Y H:i');
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Avatar psicólogo (derecha) -->
                                <?php if ($is_sent): ?>
                                    <div class="flex-shrink-0 mt-1">
                                        <?php echo get_avatar(get_current_user_id(), 32, '', '', ['class' => 'rounded-full border-2 border-primary-200 shadow-sm']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php
                            // Separador de día (si cambia la fecha)
                            if ($index < count($messages_display) - 1) {
                                $current_date = date('Y-m-d', strtotime($msg->created_at));
                                $next_date = date('Y-m-d', strtotime($messages_display[$index + 1]->created_at));

                                if ($current_date !== $next_date):
                                    ?>
                                    <div class="flex items-center gap-3 my-4">
                                        <div class="flex-1 border-t border-gray-300"></div>
                                        <span class="text-xs font-medium text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200">
                                <?php echo date('d/m/Y', strtotime($messages_display[$index + 1]->created_at)); ?>
                            </span>
                                        <div class="flex-1 border-t border-gray-300"></div>
                                    </div>
                                <?php
                                endif;
                            }
                            ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Footer con estadísticas y link -->
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center gap-6">
                            <?php
                            // Contar mensajes totales
                            $total_messages = \Openmind\Repositories\MessageRepository::getConversationCount(
                                    get_current_user_id(),
                                    $patient_id
                            );

                            // Contar mensajes enviados y recibidos
                            $sent_count = 0;
                            $received_count = 0;
                            foreach ($last_messages as $msg) {
                                if ($msg->sender_id == get_current_user_id()) {
                                    $sent_count++;
                                } else {
                                    $received_count++;
                                }
                            }
                            ?>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900"><?php echo $total_messages; ?></div>
                                <div class="text-xs text-gray-500">Total mensajes</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-primary-600"><?php echo count($last_messages); ?></div>
                                <div class="text-xs text-gray-500">Mostrando</div>
                            </div>
                        </div>

                        <?php if ($total_messages > 5): ?>
                            <div class="flex items-center gap-3">
                                <div class="text-sm text-gray-600">
                                    <i class="fa-solid fa-arrow-down mr-1"></i>
                                    Hay <strong><?php echo $total_messages - 5; ?> mensajes más</strong> en el historial
                                </div>
                                <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'user_id' => $patient_id], home_url('/dashboard-psicologo/')); ?>"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-lg text-sm font-medium transition-all hover:from-primary-600 hover:to-primary-700 hover:-translate-y-0.5 hover:shadow-lg no-underline">
                                    Ver todos
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-gray-500">
                                <i class="fa-solid fa-check-circle text-green-500 mr-1"></i>
                                Estás viendo toda la conversación
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        function initTabs() {
            const tabs = document.querySelectorAll('.tab-btn');
            const panes = document.querySelectorAll('.tab-pane');

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');

                    // Actualizar botones
                    tabs.forEach(function(btn) {
                        btn.classList.remove('active');
                    });

                    this.classList.add('active');

                    // Actualizar contenido
                    panes.forEach(function(pane) {
                        pane.style.display = 'none';
                    });

                    const selectedPane = document.getElementById('tab-' + tabName);
                    if (selectedPane) {
                        selectedPane.style.display = 'block';
                    }
                });
            });
        }

        function initButtons() {
            const assignBtns = ['assign-new-activity', 'assign-first-activity'];
            assignBtns.forEach(function(id) {
                const btn = document.getElementById(id);
                if (btn) {
                    btn.addEventListener('click', function() {
                        if (typeof OpenmindApp !== 'undefined') {
                            OpenmindApp.showNotification('Funcionalidad en desarrollo', 'info');
                        } else {
                            alert('Funcionalidad en desarrollo');
                        }
                    });
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initTabs();
                initButtons();
            });
        } else {
            initTabs();
            initButtons();
        }
    })();
</script>