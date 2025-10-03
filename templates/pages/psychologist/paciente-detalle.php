<?php
if (!defined('ABSPATH')) exit;

$patient_id = intval($_GET['patient_id'] ?? 0);
$patient = get_userdata($patient_id);

$psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);
if (!$patient || $psychologist_id != get_current_user_id()) {
    echo '<div class="tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-xl tw-p-4 tw-text-red-700 tw-text-center tw-my-6">
        <i class="fa-solid fa-triangle-exclamation tw-mr-2"></i>
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

<div class="tw-max-w-7xl tw-mx-auto">
    <!-- Breadcrumb -->
    <div class="tw-mb-6">
        <a href="?view=pacientes" class="tw-inline-flex tw-items-center tw-gap-2 tw-text-primary-600 tw-text-sm tw-font-medium tw-transition-colors hover:tw-text-primary-700 tw-no-underline">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Pacientes
        </a>
    </div>

    <!-- Header del Paciente -->
    <div class="tw-bg-white tw-rounded-2xl tw-p-8 tw-mb-8 tw-shadow-sm">
        <div class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-start md:tw-items-center tw-gap-6">
            <div class="tw-flex tw-gap-6 tw-items-start">
                <?php echo get_avatar($patient->ID, 100, '', '', ['class' => 'tw-rounded-2xl tw-border-4 tw-border-gray-100']); ?>
                <div>
                    <h1 class="tw-text-3xl tw-font-bold tw-text-gray-900 tw-m-0 tw-mb-3"><?php echo esc_html($patient->display_name); ?></h1>
                    <p class="tw-flex tw-items-center tw-gap-2 tw-text-gray-600 tw-text-sm tw-my-1 tw-m-0">
                        <i class="fa-solid fa-envelope"></i>
                        <?php echo esc_html($patient->user_email); ?>
                    </p>
                    <p class="tw-flex tw-items-center tw-gap-2 tw-text-gray-600 tw-text-sm tw-my-1 tw-m-0">
                        <i class="fa-solid fa-calendar-check"></i>
                        Paciente desde <?php echo date('d/m/Y', strtotime($patient->user_registered)); ?>
                    </p>
                </div>
            </div>

            <div class="tw-flex tw-gap-3 tw-flex-col md:tw-flex-row tw-w-full md:tw-w-auto">
                <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'patient_id' => $patient_id]); ?>"
                   class="tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-5 tw-py-2.5 tw-bg-primary-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-primary-600 hover:tw--translate-y-0.5 hover:tw-shadow-lg tw-no-underline">
                    <i class="fa-solid fa-message"></i>
                    Enviar Mensaje
                </a>
                <button class="tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-5 tw-py-2.5 tw-bg-gray-200 tw-text-gray-700 tw-rounded-lg tw-border-0 tw-cursor-pointer tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-gray-300" id="assign-new-activity">
                    <i class="fa-solid fa-clipboard-list"></i>
                    Asignar Actividad
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6 tw-mb-8">
        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-sm tw-flex tw-items-center tw-gap-5 tw-transition-all hover:tw--translate-y-1 hover:tw-shadow-md">
            <div class="tw-text-4xl tw-w-16 tw-h-16 tw-flex tw-items-center tw-justify-center tw-bg-blue-50 tw-rounded-xl tw-text-blue-500">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <h3 class="tw-text-3xl tw-font-bold tw-text-blue-600 tw-m-0"><?php echo count($all_activities); ?></h3>
                <p class="tw-text-sm tw-text-gray-500 tw-mt-1 tw-m-0">Actividades totales</p>
            </div>
        </div>

        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-sm tw-flex tw-items-center tw-gap-5 tw-transition-all hover:tw--translate-y-1 hover:tw-shadow-md">
            <div class="tw-text-4xl tw-w-16 tw-h-16 tw-flex tw-items-center tw-justify-center tw-bg-green-50 tw-rounded-xl tw-text-green-500">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div>
                <h3 class="tw-text-3xl tw-font-bold tw-text-green-600 tw-m-0"><?php echo count($completed_activities); ?></h3>
                <p class="tw-text-sm tw-text-gray-500 tw-mt-1 tw-m-0">Completadas</p>
            </div>
        </div>

        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-sm tw-flex tw-items-center tw-gap-5 tw-transition-all hover:tw--translate-y-1 hover:tw-shadow-md">
            <div class="tw-text-4xl tw-w-16 tw-h-16 tw-flex tw-items-center tw-justify-center tw-bg-orange-50 tw-rounded-xl tw-text-orange-500">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h3 class="tw-text-3xl tw-font-bold tw-text-orange-600 tw-m-0"><?php echo count($pending_activities); ?></h3>
                <p class="tw-text-sm tw-text-gray-500 tw-mt-1 tw-m-0">Pendientes</p>
            </div>
        </div>

        <div class="tw-bg-white tw-p-6 tw-rounded-xl tw-shadow-sm tw-flex tw-items-center tw-gap-5 tw-transition-all hover:tw--translate-y-1 hover:tw-shadow-md">
            <div class="tw-text-4xl tw-w-16 tw-h-16 tw-flex tw-items-center tw-justify-center tw-bg-purple-50 tw-rounded-xl tw-text-purple-500">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <h3 class="tw-text-3xl tw-font-bold tw-text-purple-600 tw-m-0"><?php echo $completion_rate; ?>%</h3>
                <p class="tw-text-sm tw-text-gray-500 tw-mt-1 tw-m-0">Tasa de completitud</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tw-flex tw-gap-2 tw-border-b-2 tw-border-gray-200 tw-mb-8 tw-overflow-x-auto">
        <button class="tab-btn active tw-flex tw-items-center tw-gap-2 tw-px-6 tw-py-3 tw-bg-transparent tw-border-0 tw-border-b-4 tw-border-primary-600 tw-cursor-pointer tw-text-sm tw-font-medium tw-text-primary-600 tw-transition-all tw-whitespace-nowrap tw-rounded-t-lg" data-tab="actividades">
            <i class="fa-solid fa-clipboard-list"></i>
            Actividades
        </button>
        <button class="tab-btn tw-flex tw-items-center tw-gap-2 tw-px-6 tw-py-3 tw-bg-transparent tw-border-0 tw-border-b-4 tw-border-transparent tw-cursor-pointer tw-text-sm tw-font-medium tw-text-gray-500 tw-transition-all tw-whitespace-nowrap tw-rounded-t-lg hover:tw-text-gray-900 hover:tw-bg-gray-50" data-tab="bitacora">
            <i class="fa-solid fa-book"></i>
            Bitácora
        </button>
        <button class="tab-btn tw-flex tw-items-center tw-gap-2 tw-px-6 tw-py-3 tw-bg-transparent tw-border-0 tw-border-b-4 tw-border-transparent tw-cursor-pointer tw-text-sm tw-font-medium tw-text-gray-500 tw-transition-all tw-whitespace-nowrap tw-rounded-t-lg hover:tw-text-gray-900 hover:tw-bg-gray-50" data-tab="diario">
            <i class="fa-solid fa-pen-to-square"></i>
            Diario de vida
        </button>
        <button class="tab-btn tw-flex tw-items-center tw-gap-2 tw-px-6 tw-py-3 tw-bg-transparent tw-border-0 tw-border-b-4 tw-border-transparent tw-cursor-pointer tw-text-sm tw-font-medium tw-text-gray-500 tw-transition-all tw-whitespace-nowrap tw-rounded-t-lg hover:tw-text-gray-900 hover:tw-bg-gray-50" data-tab="mensajes">
            <i class="fa-solid fa-message"></i>
            Mensajes
        </button>
    </div>

    <!-- Tab Content -->
    <div class="tw-min-h-96">
        <!-- Tab Actividades -->
        <div class="tab-pane" id="tab-actividades" style="display: block;">
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-m-0">Actividades Asignadas</h2>
            </div>

            <?php if (empty($all_activities)): ?>
                <div class="tw-text-center tw-py-16 tw-text-gray-400">
                    <div class="tw-text-6xl tw-mb-4">📋</div>
                    <p class="tw-text-lg tw-mb-4 tw-not-italic tw-text-gray-600">No hay actividades asignadas a este paciente.</p>
                    <button class="tw-inline-flex tw-items-center tw-gap-2 tw-px-5 tw-py-2.5 tw-bg-gray-200 tw-text-gray-700 tw-rounded-lg tw-border-0 tw-cursor-pointer tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-gray-300" id="assign-first-activity">
                        <i class="fa-solid fa-plus"></i>
                        Asignar Primera Actividad
                    </button>
                </div>
            <?php else: ?>
                <!-- Pendientes -->
                <?php if (!empty($pending_activities)): ?>
                    <div class="tw-mb-10">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4 tw-pb-2 tw-border-b-2 tw-border-gray-100">
                            <i class="fa-solid fa-hourglass-half tw-mr-2 tw-text-orange-500"></i>
                            Pendientes (<?php echo count($pending_activities); ?>)
                        </h3>
                        <div class="tw-flex tw-flex-col tw-gap-4">
                            <?php foreach ($pending_activities as $activity):
                                $due_date = get_post_meta($activity->ID, 'due_date', true);
                                $is_overdue = $due_date && strtotime($due_date) < current_time('timestamp');
                                ?>
                                <div class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-xl tw-p-5 tw-transition-all hover:tw-shadow-md <?php echo $is_overdue ? 'tw-border-l-4 tw-border-l-red-500 tw-bg-red-50' : ''; ?>">
                                    <h4 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-mb-2 tw-flex tw-items-center tw-gap-2 tw-m-0">
                                        <?php echo esc_html($activity->post_title); ?>
                                    </h4>
                                    <p class="tw-text-sm tw-text-gray-600 tw-mb-3 tw-leading-relaxed tw-m-0"><?php echo wp_trim_words($activity->post_content, 20); ?></p>
                                    <?php if ($due_date): ?>
                                        <span class="tw-inline-flex tw-items-center tw-gap-2 tw-text-xs tw-font-medium tw-px-3 tw-py-1 tw-rounded-full <?php echo $is_overdue ? 'tw-bg-red-100 tw-text-red-700' : 'tw-bg-gray-100 tw-text-gray-600'; ?>">
                                            <i class="fa-solid fa-calendar"></i>
                                            Vence: <?php echo date('d/m/Y', strtotime($due_date)); ?>
                                            <?php if ($is_overdue): ?>
                                                <span class="tw-bg-red-500 tw-text-white tw-px-2 tw-py-1 tw-rounded-full tw-text-xs tw-font-semibold tw-ml-2">Vencida</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Completadas -->
                <?php if (!empty($completed_activities)): ?>
                    <div class="tw-mb-10">
                        <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4 tw-pb-2 tw-border-b-2 tw-border-gray-100">
                            <i class="fa-solid fa-check-circle tw-mr-2 tw-text-green-500"></i>
                            Completadas (<?php echo count($completed_activities); ?>)
                        </h3>
                        <div class="tw-flex tw-flex-col tw-gap-4">
                            <?php foreach ($completed_activities as $activity):
                                $completed_at = get_post_meta($activity->ID, 'completed_at', true);
                                ?>
                                <div class="tw-bg-gray-50 tw-opacity-75 tw-border tw-border-gray-200 tw-rounded-xl tw-p-5 tw-transition-all hover:tw-shadow-md">
                                    <h4 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-mb-2 tw-flex tw-items-center tw-gap-2 tw-m-0">
                                        <i class="fa-solid fa-check-circle tw-text-green-500"></i>
                                        <?php echo esc_html($activity->post_title); ?>
                                    </h4>
                                    <?php if ($completed_at): ?>
                                        <span class="tw-text-xs tw-font-medium tw-text-green-600 tw-flex tw-items-center tw-gap-1">
                                            <i class="fa-solid fa-calendar-check"></i>
                                            Completada el <?php echo date('d/m/Y', strtotime($completed_at)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Tab Bitácora -->
        <div class="tab-pane" id="tab-bitacora" style="display: none;">
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-m-0">Bitácora del Paciente</h2>
                <div class="tw-flex tw-gap-3">
                    <a href="?view=bitacora&patient_id=<?php echo $patient_id; ?>"
                       class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2 tw-bg-gray-100 tw-text-gray-700 tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-gray-200 tw-no-underline">
                        <i class="fa-solid fa-book-open"></i>
                        Ver Todas
                    </a>
                    <a href="?view=bitacora-nueva&patient_id=<?php echo $patient_id; ?>&return=detalle"
                       class="tw-inline-flex tw-items-center tw-gap-2 tw-px-5 tw-py-2.5 tw-bg-primary-500 tw-text-white tw-rounded-lg tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-primary-600 hover:tw--translate-y-0.5 hover:tw-shadow-lg tw-no-underline">
                        <i class="fa-solid fa-plus"></i>
                        Nueva Entrada
                    </a>
                </div>
            </div>

            <div class="tw-bg-blue-50 tw-border-l-4 tw-border-blue-400 tw-p-4 tw-mb-6 tw-rounded-lg">
                <div class="tw-flex tw-items-start">
                    <i class="fa-solid fa-info-circle tw-text-blue-600 tw-mr-3 tw-mt-1"></i>
                    <div>
                        <p class="tw-text-sm tw-text-blue-800 tw-m-0">
                            <strong>Bitácora de sesiones:</strong> Registro de las sesiones terapéuticas.
                            El paciente puede ver estas entradas en su dashboard.
                        </p>
                    </div>
                </div>
            </div>

            <?php
            // Obtener solo las últimas 5 entradas para preview
            $preview_entries = \Openmind\Repositories\DiaryRepository::getPsychologistEntries($patient_id, 5, 0);
            $total_entries = \Openmind\Repositories\DiaryRepository::countPsychologistEntries($patient_id);

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
                <div class="tw-mt-6 tw-text-center">
                    <a href="?view=bitacora&patient_id=<?php echo $patient_id; ?>"
                       class="tw-inline-flex tw-items-center tw-gap-2 tw-text-primary-600 tw-font-medium hover:tw-text-primary-700 tw-transition-colors tw-no-underline">
                        Ver las <?php echo $total_entries; ?> entradas completas
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Diario de vida -->
        <div class="tab-pane" id="tab-diario" style="display: none;">
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-m-0">Diario Personal del Paciente</h2>
                <span class="tw-text-sm tw-text-red-600 tw-font-medium">
                    <i class="fa-solid fa-lock tw-mr-1"></i>
                    Contenido privado del paciente
                </span>
            </div>

            <div class="tw-bg-yellow-50 tw-border-l-4 tw-border-yellow-400 tw-p-4 tw-mb-6 tw-rounded-lg">
                <div class="tw-flex tw-items-start">
                    <i class="fa-solid fa-info-circle tw-text-yellow-600 tw-mr-3 tw-mt-1"></i>
                    <div>
                        <h4 class="tw-text-sm tw-font-semibold tw-text-yellow-800 tw-m-0 tw-mb-1">Nota sobre privacidad</h4>
                        <p class="tw-text-sm tw-text-yellow-700 tw-m-0">
                            El diario personal es un espacio privado del paciente. Solo tú como psicólogo tienes acceso para mejor seguimiento terapéutico.
                            Estas entradas NO son visibles en la bitácora compartida.
                        </p>
                    </div>
                </div>
            </div>

            <?php if (empty($private_diary_entries)): ?>
                <div class="tw-text-center tw-py-16 tw-text-gray-400">
                    <div class="tw-text-6xl tw-mb-4">✍️</div>
                    <p class="tw-text-lg tw-not-italic tw-text-gray-600">El paciente aún no ha escrito en su diario personal.</p>
                </div>
            <?php else: ?>
                <div class="tw-space-y-6">
                    <?php
                    $mood_emojis = [
                            'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
                            'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
                    ];
                    foreach ($private_diary_entries as $entry): ?>
                        <div class="tw-bg-gradient-to-r tw-from-purple-50 tw-to-pink-50 tw-border tw-border-purple-200 tw-rounded-xl tw-p-6 tw-transition-all hover:tw-shadow-sm">
                            <div class="tw-mb-4">
                                <div class="tw-flex tw-gap-3 tw-items-center tw-flex-wrap">
                                    <?php if ($entry->mood): ?>
                                        <span class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-purple-100 tw-text-purple-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium">
                                            <?php echo $mood_emojis[$entry->mood] ?? ''; ?>
                                            <?php echo esc_html(ucfirst($entry->mood)); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="tw-text-sm tw-text-purple-600">
                                        <i class="fa-solid fa-clock tw-mr-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?>
                                    </span>
                                    <span class="tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-bg-purple-200 tw-text-purple-800 tw-px-2 tw-py-1 tw-rounded-full tw-font-medium">
                                        <i class="fa-solid fa-lock"></i>
                                        Privado
                                    </span>
                                </div>
                            </div>
                            <div class="tw-text-gray-800 tw-leading-relaxed">
                                <?php echo wp_kses_post(wpautop($entry->content)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Mensajes -->
        <div class="tab-pane" id="tab-mensajes" style="display: none;">
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-m-0">Conversación</h2>
                <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'patient_id' => $patient_id]); ?>"
                   class="tw-inline-flex tw-items-center tw-gap-2 tw-px-5 tw-py-2.5 tw-bg-gray-200 tw-text-gray-700 tw-rounded-lg tw-border-0 tw-text-sm tw-font-medium tw-transition-all hover:tw-bg-gray-300 tw-no-underline">
                    <i class="fa-solid fa-comments"></i>
                    Abrir Chat Completo
                </a>
            </div>

            <div class="tw-bg-gray-50 tw-border-2 tw-border-dashed tw-border-gray-300 tw-rounded-xl tw-p-12 tw-text-center">
                <i class="fa-solid fa-comments tw-text-6xl tw-text-gray-300 tw-mb-4"></i>
                <p class="tw-text-gray-600 tw-m-0 tw-text-base">
                    Ve a la sección de Mensajería para ver el historial completo y enviar mensajes.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        function initTabs() {
            const tabs = document.querySelectorAll('.tab-btn');
            const panes = document.querySelectorAll('.tab-pane');

            console.log('Tabs encontrados:', tabs.length);
            console.log('Panes encontrados:', panes.length);

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    console.log('Tab clickeado:', tabName);

                    // Actualizar botones
                    tabs.forEach(function(btn) {
                        btn.classList.remove('tw-border-primary-600', 'tw-text-primary-600');
                        btn.classList.add('tw-border-transparent', 'tw-text-gray-500');
                        btn.classList.remove('active');
                    });

                    this.classList.add('tw-border-primary-600', 'tw-text-primary-600');
                    this.classList.remove('tw-border-transparent', 'tw-text-gray-500');
                    this.classList.add('active');

                    // Actualizar contenido
                    panes.forEach(function(pane) {
                        pane.style.display = 'none';
                    });

                    const selectedPane = document.getElementById('tab-' + tabName);
                    if (selectedPane) {
                        selectedPane.style.display = 'block';
                        console.log('Mostrando pane:', 'tab-' + tabName);
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