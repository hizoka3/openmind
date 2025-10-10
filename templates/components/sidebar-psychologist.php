<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$current_view = $_GET['view'] ?? 'inicio';
$base_url = get_permalink();

$menu_items = [
        'inicio' => ['label' => 'Inicio', 'icon' => 'fa-home', 'badge' => false],
        'pacientes' => ['label' => 'Mis pacientes', 'icon' => 'fa-users', 'badge' => 'diary'],
        'actividades' => ['label' => 'Actividades', 'icon' => 'fa-clipboard-list', 'badge' => false],
        'mensajeria' => ['label' => 'Mensajería', 'icon' => 'fa-comments', 'badge' => 'messages'],
        'bitacora' => ['label' => 'Bitácora', 'icon' => 'fa-book', 'badge' => false],
        'perfil' => ['label' => 'Mi perfil', 'icon' => 'fa-user', 'badge' => false]
];
?>

<aside class="openmind-sidebar">
    <div class="sidebar-header">
        <div class="user-avatar">
            <?php echo get_avatar($current_user->ID, 150); ?>
        </div>
        <h3><?php echo esc_html($current_user->display_name); ?></h3>
        <p class="user-role">Psicólogo</p>
    </div>

    <nav class="sidebar-menu">
        <?php foreach ($menu_items as $view => $item): ?>
            <a href="<?php echo esc_url(add_query_arg('view', $view, $base_url)); ?>"
               class="menu-item <?php echo $current_view === $view ? 'active' : ''; ?>">
                <!--<span class="menu-icon">
                    <i class="fa-solid <?php /*echo $item['icon']; */?>"></i>
                </span>-->
                <span class="menu-label"><?php echo esc_html($item['label']); ?></span>

                <?php if ($item['badge'] === 'messages'): ?>
                    <span class="unread-badge" id="messages-badge" style="display:none;">0</span>
                <?php elseif ($item['badge'] === 'diary'): ?>
                    <!--<span class="unread-badge" id="diary-badge" style="display:none;">0</span>-->
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo wp_logout_url(home_url() . '/auth'); ?>" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket mr-2"></i>
            Salir
        </a>
    </div>
</aside>