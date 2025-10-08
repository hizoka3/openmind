<?php
// templates/pages/patient/perfil.php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$psychologist_id = get_user_meta($current_user->ID, 'psychologist_id', true);
$psychologist = $psychologist_id ? get_userdata($psychologist_id) : null;

// Calcular estadísticas del paciente
$completed_activities = get_posts([
        'post_type' => 'activity',
        'meta_query' => [
                ['key' => 'assigned_to', 'value' => $current_user->ID],
                ['key' => 'completed', 'value' => '1']
        ],
        'posts_per_page' => -1,
        'fields' => 'ids'
]);

$total_activities = get_posts([
        'post_type' => 'activity',
        'meta_query' => [
                ['key' => 'assigned_to', 'value' => $current_user->ID]
        ],
        'posts_per_page' => -1,
        'fields' => 'ids'
]);

$completion_rate = count($total_activities) > 0
        ? round((count($completed_activities) / count($total_activities)) * 100)
        : 0;

// Preparar info extra del paciente - CON MÁS ÉNFASIS
$extra_info = [];
if ($psychologist) {
    $extra_info[] = [
            'label' => 'Mi psicólogo',
            'content' => '
            <div class="flex items-center gap-3 p-3 bg-primary-50 rounded-lg border border-primary-100">
                <div class="w-12 h-12 border-2 border-primary-200 rounded-full">
                ' . get_avatar($psychologist->ID, 48, '', '', ['class' => 'rounded-full']) . '
                </div>
                <div>
                    <p class="font-semibold text-gray-900 m-0">' . esc_html($psychologist->display_name) . '</p>
                    <p class="text-sm text-gray-600 m-0">Psicólogo asignado</p>
                </div>
            </div>
        '
    ];
}

// Preparar estadísticas
$stats = [
        [
                'icon' => 'fa-solid fa-clipboard-check',
                'value' => count($completed_activities),
                'label' => 'Actividades completadas',
                'color' => 'green'
        ],
        [
                'icon' => 'fa-solid fa-tasks',
                'value' => count($total_activities),
                'label' => 'Actividades totales',
                'color' => 'blue'
        ],
        [
                'icon' => 'fa-solid fa-chart-line',
                'value' => $completion_rate . '%',
                'label' => 'Tasa de completitud',
                'color' => 'purple'
        ]
];
?>

<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-normal text-gray-900 mb-8">Mi Perfil</h1>

    <?php
    openmind_template('components/profile-card', [
            'user' => $current_user,
            'role' => 'patient',
            'extra_info' => $extra_info,
            'stats' => $stats
    ]);
    ?>
</div>
