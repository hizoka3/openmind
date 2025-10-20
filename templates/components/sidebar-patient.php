<?php // templates/components/sidebar-patient.php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$current_view = $_GET['view'] ?? 'inicio';
$base_url = get_permalink();

// Verificar status del paciente
$is_active = get_user_meta($current_user->ID, 'openmind_status', true) === 'active';

// Menú completo
$all_menu_items = [
        'inicio' => ['label' => 'Inicio', 'icon' => '🏠', 'badge' => false, 'always_show' => true],
        'actividades' => ['label' => 'Actividades', 'icon' => '📋', 'badge' => false, 'always_show' => false],
        'mensajeria' => ['label' => 'Mensajería', 'icon' => '💬', 'badge' => 'messages', 'always_show' => false],
        'bitacora' => ['label' => 'Bitácora', 'icon' => '📖', 'badge' => false, 'always_show' => false],
        'diario' => ['label' => 'Diario de vida', 'icon' => '✍️', 'badge' => false, 'always_show' => false],
        'perfil' => ['label' => 'Mi perfil', 'icon' => '👤', 'badge' => false, 'always_show' => true],
];

// Filtrar items según status
$menu_items = array_filter($all_menu_items, function($item) use ($is_active) {
    return $is_active || $item['always_show'];
});
?>

<aside class="openmind-sidebar hidden md:flex">
    <div class="sidebar-header">
        <div class="user-avatar">
            <img id="avatar-preview"
                 src="<?php echo esc_url(get_avatar_url($current_user->ID, ['size' => 80])); ?>"
                 alt="Avatar"
                 class="w-32 h-32 rounded-full border-4 border-primary-100 object-cover">
        </div>
        <h3><?php echo esc_html($current_user->display_name); ?></h3>
        <p class="user-role">Paciente</p>

        <?php if (!$is_active): ?>
            <div class="mt-3 px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full inline-block">
                Cuenta inactiva
            </div>
        <?php endif; ?>
    </div>

    <nav class="sidebar-menu">
        <?php foreach ($menu_items as $view => $item): ?>
            <a href="<?php echo esc_url(add_query_arg('view', $view, $base_url)); ?>"
               class="menu-item <?php echo $current_view === $view ? 'active' : ''; ?>">
                <span class="menu-label"><?php echo esc_html($item['label']); ?></span>

                <?php if ($item['badge'] === 'messages'): ?>
                    <span class="unread-badge" id="messages-badge" style="display:none;">0</span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo wp_logout_url(home_url() . '/auth'); ?>" class="btn-logout">
            Salir
        </a>
    </div>
</aside>