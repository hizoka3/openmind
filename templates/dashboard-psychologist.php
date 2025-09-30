<?php // templates/dashboard-psychologist.php
if (!current_user_can('manage_patients')) wp_die('Acceso denegado');

$user_id = get_current_user_id();
$patients = get_users([
    'role' => 'patient',
    'meta_query' => [
        [
            'key' => 'psychologist_id',
            'value' => $user_id,
            'compare' => '='
        ]
    ]
]);

get_header();
?>

    <div class="openmind-dashboard psychologist">
        <?php get_template_part('openmind/components/header', null, ['role' => 'psychologist']); ?>

        <div class="dashboard-content">
            <div class="dashboard-section">
                <h2>Mis Pacientes (<?php echo count($patients); ?>)</h2>

                <div class="patients-grid">
                    <?php if (empty($patients)): ?>
                        <p class="empty-state">No tienes pacientes asignados aún.</p>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                            <?php get_template_part('openmind/components/patient-card', null, ['patient' => $patient]); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <button class="btn-primary" id="add-patient">Agregar Paciente</button>
            </div>

            <div class="dashboard-section">
                <h2>Actividades Recientes</h2>
                <?php
                $activities = get_posts([
                    'post_type' => 'activity',
                    'author' => $user_id,
                    'posts_per_page' => 5,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ]);
                ?>

                <div class="activities-list">
                    <?php foreach ($activities as $activity): ?>
                        <?php get_template_part('openmind/components/activity-item', null, ['activity' => $activity]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>