<?php // templates/components/header.php
$current_user = wp_get_current_user();
$role = $args['role'] ?? 'patient';
$title = $role === 'psychologist' ? 'Panel de Psicólogo' : 'Mi Espacio';
?>

<header class="openmind-header">
    <div class="header-content">
        <h1><?php echo esc_html($title); ?></h1>

        <div class="header-actions">
            <span class="user-name"><?php echo esc_html($current_user->display_name); ?></span>

            <button class="btn-icon" id="notifications" data-count="0">
                <span class="icon">🔔</span>
            </button>

            <button class="btn-icon" id="messages" data-count="0">
                <span class="icon">💬</span>
            </button>

            <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn-text">Salir</a>
        </div>
    </div>
</header>