<?php // templates/pages/psychologist/paciente-detalle.php
if (!defined('ABSPATH')) exit;

$patient_id = intval($_GET['patient_id'] ?? 0);
$patient = get_userdata($patient_id);

$psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);
if (!$patient || $psychologist_id != get_current_user_id()) {
    echo '<div class="error-message">
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
$diary_entries = \Openmind\Repositories\DiaryRepository::getByPatient($patient_id, 5);
$completion_rate = count($all_activities) > 0 ? round((count($completed_activities) / count($all_activities)) * 100) : 0;
?>

<div class="tw-max-w-7xl tw-mx-auto">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="?view=pacientes">
            <i class="fa-solid fa-arrow-left"></i>
            Volver a Pacientes
        </a>
    </div>

    <!-- Header del Paciente -->
    <div class="patient-detail-header">
        <div class="patient-main-info">
            <?php echo get_avatar($patient->ID, 100); ?>
            <div>
                <h1><?php echo esc_html($patient->display_name); ?></h1>
                <p class="patient-email">
                    <i class="fa-solid fa-envelope"></i>
                    <?php echo esc_html($patient->user_email); ?>
                </p>
                <p class="patient-since">
                    <i class="fa-solid fa-calendar-check"></i>
                    Paciente desde <?php echo date('d/m/Y', strtotime($patient->user_registered)); ?>
                </p>
            </div>
        </div>

        <div class="patient-actions">
            <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'patient_id' => $patient_id]); ?>"
               class="btn-primary tw-inline-flex tw-items-center tw-gap-2">
                <i class="fa-solid fa-message"></i>
                Enviar Mensaje
            </a>
            <button class="btn-secondary tw-inline-flex tw-items-center tw-gap-2" id="assign-new-activity">
                <i class="fa-solid fa-clipboard-list"></i>
                Asignar Actividad
            </button>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid tw-mb-8">
        <div class="stat-card">
            <div class="stat-icon tw-text-blue-500">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div class="stat-info">
                <h3 class="tw-text-blue-600"><?php echo count($all_activities); ?></h3>
                <p>Actividades totales</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon tw-text-green-500">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3 class="tw-text-green-600"><?php echo count($completed_activities); ?></h3>
                <p>Completadas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon tw-text-orange-500">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3 class="tw-text-orange-600"><?php echo count($pending_activities); ?></h3>
                <p>Pendientes</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon tw-text-purple-500">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <h3 class="tw-text-purple-600"><?php echo $completion_rate; ?>%</h3>
                <p>Tasa de completitud</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="detail-tabs">
        <button class="tab-btn active" data-tab="actividades">
            <i class="fa-solid fa-clipboard-list"></i>
            Actividades
        </button>
        <button class="tab-btn" data-tab="bitacora">
            <i class="fa-solid fa-book"></i>
            Bitácora
        </button>
        <button class="tab-btn" data-tab="mensajes">
            <i class="fa-solid fa-message"></i>
            Mensajes
        </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Tab Actividades -->
        <div class="tab-pane active" id="tab-actividades">
            <div class="section-header">
                <h2>Actividades Asignadas</h2>
            </div>

            <?php if (empty($all_activities)): ?>
                <div class="empty-state">
                    <div class="tw-text-6xl tw-mb-4">📋</div>
                    <p class="tw-text-lg tw-mb-4">No hay actividades asignadas a este paciente.</p>
                    <button class="btn-secondary tw-inline-flex tw-items-center tw-gap-2" id="assign-first-activity">
                        <i class="fa-solid fa-plus"></i>
                        Asignar Primera Actividad
                    </button>
                </div>
            <?php else: ?>
                <!-- Pendientes -->
                <?php if (!empty($pending_activities)): ?>
                    <div class="activities-section">
                        <h3>
                            <i class="fa-solid fa-hourglass-half tw-mr-2 tw-text-orange-500"></i>
                            Pendientes (<?php echo count($pending_activities); ?>)
                        </h3>
                        <div class="activities-list">
                            <?php foreach ($pending_activities as $activity):
                                $due_date = get_post_meta($activity->ID, 'due_date', true);
                                $is_overdue = $due_date && strtotime($due_date) < current_time('timestamp');
                                ?>
                                <div class="activity-item <?php echo $is_overdue ? 'overdue' : ''; ?>">
                                    <div class="activity-content">
                                        <h4><?php echo esc_html($activity->post_title); ?></h4>
                                        <p><?php echo wp_trim_words($activity->post_content, 20); ?></p>
                                        <?php if ($due_date): ?>
                                            <span class="due-date <?php echo $is_overdue ? 'tw-bg-red-100 tw-text-red-700' : ''; ?>">
                                                <i class="fa-solid fa-calendar"></i>
                                                Vence: <?php echo date('d/m/Y', strtotime($due_date)); ?>
                                                <?php if ($is_overdue): ?>
                                                    <span class="overdue-badge">Vencida</span>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Completadas -->
                <?php if (!empty($completed_activities)): ?>
                    <div class="activities-section">
                        <h3>
                            <i class="fa-solid fa-check-circle tw-mr-2 tw-text-green-500"></i>
                            Completadas (<?php echo count($completed_activities); ?>)
                        </h3>
                        <div class="activities-list">
                            <?php foreach ($completed_activities as $activity):
                                $completed_at = get_post_meta($activity->ID, 'completed_at', true);
                                ?>
                                <div class="activity-item completed">
                                    <div class="activity-content">
                                        <h4>
                                            <i class="fa-solid fa-check-circle tw-text-green-500"></i>
                                            <?php echo esc_html($activity->post_title); ?>
                                        </h4>
                                        <?php if ($completed_at): ?>
                                            <span class="completed-date">
                                                <i class="fa-solid fa-calendar-check"></i>
                                                Completada el <?php echo date('d/m/Y', strtotime($completed_at)); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Tab Bitácora -->
        <div class="tab-pane" id="tab-bitacora">
            <div class="section-header">
                <h2>Bitácora del Paciente</h2>
            </div>

            <?php if (empty($diary_entries)): ?>
                <div class="empty-state">
                    <div class="tw-text-6xl tw-mb-4">📖</div>
                    <p class="tw-text-lg">El paciente aún no ha escrito entradas en su bitácora.</p>
                </div>
            <?php else: ?>
                <div class="diary-entries">
                    <?php
                    $mood_emojis = [
                            'feliz' => '😊', 'triste' => '😢', 'ansioso' => '😰',
                            'neutral' => '😐', 'enojado' => '😠', 'calmado' => '😌'
                    ];
                    foreach ($diary_entries as $entry): ?>
                        <div class="diary-entry">
                            <div class="entry-header">
                                <div class="entry-meta">
                                    <?php if ($entry->mood): ?>
                                        <span class="mood-badge">
                                            <?php echo $mood_emojis[$entry->mood] ?? ''; ?>
                                            <?php echo esc_html(ucfirst($entry->mood)); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="entry-date">
                                        <i class="fa-solid fa-clock tw-mr-1"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($entry->created_at)); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="entry-content">
                                <?php echo wp_kses_post(wpautop($entry->content)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="tw-mt-6 tw-text-center">
                    <a href="?view=bitacora&patient_id=<?php echo $patient_id; ?>"
                       class="tw-inline-flex tw-items-center tw-gap-2 tw-text-primary-600 tw-font-medium hover:tw-text-primary-700 tw-transition-colors">
                        Ver todas las entradas
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Mensajes -->
        <div class="tab-pane" id="tab-mensajes">
            <div class="section-header">
                <h2>Conversación</h2>
                <a href="<?php echo add_query_arg(['view' => 'mensajeria', 'patient_id' => $patient_id]); ?>"
                   class="btn-secondary tw-inline-flex tw-items-center tw-gap-2">
                    <i class="fa-solid fa-comments"></i>
                    Abrir Chat Completo
                </a>
            </div>

            <div class="messages-preview">
                <i class="fa-solid fa-comments tw-text-6xl tw-text-gray-300 tw-mb-4"></i>
                <p class="help-text tw-text-base">
                    Ve a la sección de Mensajería para ver el historial completo y enviar mensajes.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.dataset.tab;

            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
        });
    });

    ['assign-new-activity', 'assign-first-activity'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', () => {
            if (typeof OpenmindApp !== 'undefined') {
                OpenmindApp.showNotification('Funcionalidad en desarrollo', 'info');
            }
        });
    });
</script>