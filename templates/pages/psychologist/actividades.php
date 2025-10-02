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

<div class="page-actividades">
    <div class="page-header">
        <h1>Actividades</h1>
        <button class="btn-primary" id="create-activity">+ Crear Actividad</button>
    </div>

    <div class="activities-layout">
        <div class="activities-main">
            <?php if (empty($activities)): ?>
                <div class="empty-state">
                    <p>📋 No has creado actividades aún.</p>
                    <button class="btn-secondary" id="create-first-activity">Crear primera actividad</button>
                </div>
            <?php else: ?>
                <div class="activities-grid">
                    <?php foreach ($activities as $activity):
                        $assigned_to = get_post_meta($activity->ID, 'assigned_to', true);
                        $patient = $assigned_to ? get_userdata($assigned_to) : null;
                        $due_date = get_post_meta($activity->ID, 'due_date', true);
                        $completed = get_post_meta($activity->ID, 'completed', true);
                        ?>
                        <div class="activity-item <?php echo $completed ? 'completed' : ''; ?>">
                            <div class="activity-header">
                                <h3><?php echo esc_html($activity->post_title); ?></h3>
                                <?php if ($due_date): ?>
                                    <span class="due-date">📅 <?php echo date('d/m/Y', strtotime($due_date)); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="activity-meta">
                                <?php if ($patient): ?>
                                    <span class="assigned-to">
                                        👤 <?php echo esc_html($patient->display_name); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="unassigned">Sin asignar</span>
                                <?php endif; ?>

                                <?php if ($completed): ?>
                                    <span class="status completed">✅ Completada</span>
                                <?php else: ?>
                                    <span class="status pending">⏳ Pendiente</span>
                                <?php endif; ?>
                            </div>

                            <div class="activity-actions">
                                <button class="btn-icon" data-action="edit-activity" data-id="<?php echo $activity->ID; ?>">✏️</button>
                                <button class="btn-icon" data-action="assign-activity" data-id="<?php echo $activity->ID; ?>">👥</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="activities-sidebar">
            <div class="sidebar-section">
                <h3>Asignar a Paciente</h3>
                <p class="help-text">Selecciona una actividad y elige un paciente para asignarla.</p>
                <div id="assign-panel" style="display: none;">
                    <!-- Se llenará con JS al seleccionar actividad -->
                </div>
            </div>
        </div>
    </div>
</div>