<?php
// templates/pages/patient/actividades.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();

$activities = get_posts([
    'post_type' => 'activity',
    'meta_query' => [
        ['key' => 'assigned_to', 'value' => $user_id, 'compare' => '=']
    ],
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'meta_key' => 'due_date',
    'order' => 'ASC'
]);

// Separar pendientes y completadas
$pending = array_filter($activities, fn($a) => !get_post_meta($a->ID, 'completed', true));
$completed = array_filter($activities, fn($a) => get_post_meta($a->ID, 'completed', true));
?>

<div class="page-actividades-patient">
    <h1>Mis Actividades</h1>

    <div class="activities-tabs">
        <button class="tab active" data-filter="pending">
            Pendientes <span class="count">(<?php echo count($pending); ?>)</span>
        </button>
        <button class="tab" data-filter="completed">
            Completadas <span class="count">(<?php echo count($completed); ?>)</span>
        </button>
    </div>

    <div class="activities-container">
        <!-- Pendientes -->
        <div class="activities-list" data-status="pending">
            <?php if (empty($pending)): ?>
                <div class="empty-state">
                    <p>🎉 ¡No tienes actividades pendientes!</p>
                </div>
            <?php else: ?>
                <?php foreach ($pending as $activity): ?>
                    <div class="activity-card-wrapper">
                        <?php
                        include OPENMIND_PATH . 'templates/components/activity-card.php';
                        $args = ['activity' => $activity, 'completed' => false];
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Completadas -->
        <div class="activities-list" data-status="completed" style="display: none;">
            <?php if (empty($completed)): ?>
                <div class="empty-state">
                    <p>📋 Aún no has completado ninguna actividad.</p>
                </div>
            <?php else: ?>
                <?php foreach ($completed as $activity): ?>
                    <div class="activity-card-wrapper">
                        <?php
                        include OPENMIND_PATH . 'templates/components/activity-card.php';
                        $args = ['activity' => $activity, 'completed' => true];
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Toggle tabs
    document.querySelectorAll('.activities-tabs .tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.dataset.filter;

            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.activities-list').forEach(list => {
                list.style.display = list.dataset.status === filter ? 'flex' : 'none';
            });
        });
    });
</script>