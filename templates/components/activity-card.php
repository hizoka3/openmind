<?php // templates/components/activity-card.php
$activity = $args['activity'];
$completed = $args['completed'] ?? false;
$due_date = get_post_meta($activity->ID, 'due_date', true);
$is_overdue = $due_date && strtotime($due_date) < current_time('timestamp') && !$completed;
?>

<div class="activity-card <?php echo $completed ? 'completed' : ''; ?> <?php echo $is_overdue ? 'overdue' : ''; ?>">
    <div class="activity-header">
        <h3><?php echo esc_html($activity->post_title); ?></h3>

        <?php if ($due_date): ?>
            <span class="due-date">
                <?php echo $is_overdue ? '⚠️ ' : '📅 '; ?>
                <?php echo date('d/m/Y', strtotime($due_date)); ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="activity-content">
        <?php echo wp_kses_post(wpautop($activity->post_content)); ?>
    </div>

    <div class="activity-footer">
        <?php if (!$completed && current_user_can('view_activities')): ?>
            <button
                class="btn-primary"
                data-action="complete-activity"
                data-activity-id="<?php echo $activity->ID; ?>">
                Marcar como completada
            </button>
        <?php elseif ($completed): ?>
            <span class="completed-badge">✅ Completada</span>
        <?php endif; ?>
    </div>
</div>