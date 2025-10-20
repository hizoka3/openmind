<?php
// templates/components/bottom-tab-bar.php
if (!defined('ABSPATH')) exit;

$current_view = $_GET['view'] ?? 'inicio';
$base_url = get_permalink();
$user_id = get_current_user_id();

// Determinar rol y menú correspondiente
$is_psychologist = current_user_can('manage_patients');

if ($is_psychologist) {
    // Menú Psicólogo - SOLO ICONOS
    $nav_items = [
            'inicio' => ['icon' => 'fa-home'],
            'pacientes' => ['icon' => 'fa-users'],
            'actividades' => ['icon' => 'fa-clipboard-list'],
            'mensajeria' => ['icon' => 'fa-comments', 'badge' => 'messages'],
            'perfil' => ['icon' => 'fa-user']
    ];
} else {
    // Menú Paciente
    $is_active = get_user_meta($user_id, 'openmind_status', true) === 'active';

    if ($is_active) {
        // Paciente activo - Todos los tabs
        $nav_items = [
                'inicio' => ['icon' => 'fa-home'],
                'actividades' => ['icon' => 'fa-clipboard-list'],
                'mensajeria' => ['icon' => 'fa-comments', 'badge' => 'messages'],
                'bitacora' => ['icon' => 'fa-book'],
                'diario' => ['icon' => 'fa-pen'],
                'perfil' => ['icon' => 'fa-user']
        ];
    } else {
        // Paciente inactivo - Solo Inicio y Perfil
        $nav_items = [
                'inicio' => ['icon' => 'fa-home'],
                'perfil' => ['icon' => 'fa-user']
        ];
    }
}
?>

<nav class="bottom-tab-bar">
    <?php foreach ($nav_items as $view => $item): ?>
        <a href="<?php echo esc_url(add_query_arg('view', $view, $base_url)); ?>"
           class="tab-item <?php echo $current_view === $view ? 'active' : ''; ?>">
            <i class="fa-solid <?php echo esc_attr($item['icon']); ?>"></i>

            <?php if (isset($item['badge']) && $item['badge'] === 'messages'): ?>
                <span class="tab-badge" id="mobile-messages-badge" style="display:none;">0</span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>