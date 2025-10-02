<?php
// templates/pages/psychologist/inicio.php
if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$current_user = wp_get_current_user();

// Stats
$patients = get_users([
    'role' => 'patient',
    'meta_query' => [
        ['key' => 'psychologist_id', 'value' => $user_id, 'compare' => '=']
    ]
]);

$activities = get_posts([
    'post_type' => 'activity',
    'author' => $user_id,
    'posts_per_page' => -1,
    'fields' => 'ids'
]);

$unread_messages = \Openmind\Repositories\MessageRepository::getUnreadCount($user_id);

// Log de eventos recientes
$recent_events = openmind_get_recent_events($user_id);
?>

<div class="page-inicio">
    <div class="welcome-section">
        <h1>Hola <?php echo esc_html($current_user->display_name); ?>
            <span class="wave">👋</span>
        </h1>
        <p class="subtitle">Aquí tienes un resumen de tu actividad</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo count($patients); ?></h3>
                <p>Pacientes</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <h3><?php echo count($activities); ?></h3>
                <p>Actividades totales</p>
            </div>
        </div>

        <div class="stat-card <?php echo $unread_messages > 0 ? 'has-notification' : ''; ?>">
            <div class="stat-icon">💬</div>
            <div class="stat-info">
                <h3><?php echo $unread_messages; ?></h3>
                <p>Mensajes sin leer</p>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <div class="section-header">
            <h2>Actividad Reciente</h2>
            <a href="?view=mensajeria" class="btn-text">Ver todo</a>
        </div>

        <?php if (empty($recent_events)): ?>
            <div class="empty-state">
                <p>No hay actividad reciente.</p>
            </div>
        <?php else: ?>
            <div class="activity-log">
                <?php foreach ($recent_events as $event): ?>
                    <div class="log-item" data-type="<?php echo $event['type']; ?>">
                        <div class="log-icon"><?php echo $event['icon']; ?></div>
                        <div class="log-content">
                            <p class="log-text"><?php echo esc_html($event['text']); ?></p>
                            <span class="log-time"><?php echo $event['time']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>