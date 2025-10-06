<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$current_view = $_GET['view'] ?? 'actividades';
$base_url = get_permalink();

$menu_items = [
        'actividades' => ['label' => 'Actividades', 'icon' => '📋', 'badge' => false],
        'mensajeria' => ['label' => 'Mensajería', 'icon' => '💬', 'badge' => 'messages'],
        'bitacora' => ['label' => 'Bitácora', 'icon' => '📖', 'badge' => false],
        'diario' => ['label' => 'Diario de vida', 'icon' => '✍️', 'badge' => false],
        'perfil' => ['label' => 'Mi perfil', 'icon' => '👤', 'badge' => false]
];
?>

<aside class="openmind-sidebar">
    <div class="sidebar-header">
        <div class="user-avatar">
            <?php echo get_avatar($current_user->ID, 80); ?>
        </div>
        <h3><?php echo esc_html($current_user->display_name); ?></h3>
        <p class="user-role">Paciente</p>
    </div>

    <nav class="sidebar-menu">
        <?php foreach ($menu_items as $view => $item): ?>
            <a href="<?php echo esc_url(add_query_arg('view', $view, $base_url)); ?>"
               class="menu-item <?php echo $current_view === $view ? 'active' : ''; ?>">
                <!--<span class="menu-icon"><?php /*echo $item['icon']; */?></span>-->
                <span class="menu-label"><?php echo esc_html($item['label']); ?></span>

                <?php if ($item['badge'] === 'messages'): ?>
                    <span class="unread-badge" id="messages-badge" style="display:none;">0</span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn-logout">
            Salir
        </a>
    </div>
</aside>