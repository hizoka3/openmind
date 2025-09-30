<?php // templates/components/patient-card.php
$patient = $args['patient'];
$last_activity = get_user_meta($patient->ID, 'last_activity_date', true);
$pending_activities = get_posts([
    'post_type' => 'activity',
    'meta_query' => [
        ['key' => 'assigned_to', 'value' => $patient->ID],
        ['key' => 'completed', 'value' => '0']
    ],
    'posts_per_page' => 1,
    'fields' => 'ids'
]);
?>

<div class="patient-card" data-patient-id="<?php echo $patient->ID; ?>">
    <div class="patient-avatar">
        <?php echo get_avatar($patient->ID, 60); ?>
    </div>

    <div class="patient-info">
        <h3><?php echo esc_html($patient->display_name); ?></h3>
        <p class="patient-email"><?php echo esc_html($patient->user_email); ?></p>

        <div class="patient-stats">
            <span class="stat">
                <strong><?php echo count($pending_activities); ?></strong> pendientes
            </span>

            <?php if ($last_activity): ?>
                <span class="stat last-activity">
                    Última actividad: <?php echo human_time_diff(strtotime($last_activity), current_time('timestamp')); ?> atrás
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="patient-actions">
        <button class="btn-icon" data-action="view-patient" data-patient-id="<?php echo $patient->ID; ?>">
            Ver
        </button>
        <button class="btn-icon" data-action="message-patient" data-patient-id="<?php echo $patient->ID; ?>">
            💬
        </button>
    </div>
</div>