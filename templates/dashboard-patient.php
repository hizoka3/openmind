<?php // templates/dashboard-patient.php
if (!current_user_can('view_activities')) wp_die('Acceso denegado');

$user_id = get_current_user_id();
$psychologist_id = get_user_meta($user_id, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;

$activities = get_posts([
    'post_type' => 'activity',
    'meta_query' => [
        [
            'key' => 'assigned_to',
            'value' => $user_id,
            'compare' => '='
        ]
    ],
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'meta_key' => 'due_date',
    'order' => 'ASC'
]);

get_header();
?>

    <div class="openmind-dashboard patient">
        <?php get_template_part('openmind/components/header', null, ['role' => 'patient']); ?>

        <div class="dashboard-content">
            <?php if ($psychologist): ?>
                <div class="psychologist-info">
                    <h3>Tu Psicólogo: <?php echo esc_html($psychologist->display_name); ?></h3>
                    <button class="btn-secondary" data-action="open-messages">Enviar Mensaje</button>
                </div>
            <?php endif; ?>

            <div class="dashboard-section">
                <h2>Tus Actividades</h2>

                <div class="activities-tabs">
                    <button class="tab active" data-filter="pending">Pendientes</button>
                    <button class="tab" data-filter="completed">Completadas</button>
                </div>

                <div class="activities-list" id="activities-container">
                    <?php if (empty($activities)): ?>
                        <p class="empty-state">No tienes actividades asignadas.</p>
                    <?php else: ?>
                        <?php foreach ($activities as $activity):
                            $completed = get_post_meta($activity->ID, 'completed', true);
                            $status = $completed ? 'completed' : 'pending';
                            ?>
                            <div class="activity-card" data-status="<?php echo $status; ?>">
                                <?php get_template_part('openmind/components/activity-card', null, [
                                    'activity' => $activity,
                                    'completed' => $completed
                                ]); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Mi Bitácora</h2>
                <button class="btn-primary" id="new-diary-entry">Nueva Entrada</button>

                <div id="diary-entries">
                    <?php get_template_part('openmind/components/diary-list', null, ['patient_id' => $user_id]); ?>
                </div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>