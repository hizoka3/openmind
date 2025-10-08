<?php
// templates/pages/psychologist/perfil.php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();

// Calcular estadísticas del psicólogo
$patients_count = count(get_users([
        'role' => 'patient',
        'meta_query' => [
                ['key' => 'psychologist_id', 'value' => $current_user->ID, 'compare' => '=']
        ]
]));

$activities_count = count(get_posts([
        'post_type' => 'activity',
        'author' => $current_user->ID,
        'posts_per_page' => -1,
        'fields' => 'ids'
]));

global $wpdb;
$messages_count = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}openmind_messages 
    WHERE sender_id = %d OR receiver_id = %d
", $current_user->ID, $current_user->ID));

// Preparar estadísticas
$stats = [
        [
                'icon' => 'fa-solid fa-users',
                'value' => $patients_count,
                'label' => 'Pacientes activos',
        ],
        [
                'icon' => 'fa-solid fa-clipboard-list',
                'value' => $activities_count,
                'label' => 'Actividades creadas',
        ],
        [
                'icon' => 'fa-solid fa-messages',
                'value' => $messages_count ?: 0,
                'label' => 'Mensajes intercambiados',
        ]
];
?>

<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-normal text-gray-900 mb-8">Mi Perfil</h1>

    <?php
    openmind_template('components/profile-card', [
            'user' => $current_user,
            'role' => 'psychologist',
            'extra_info' => [], // Psicólogo no tiene info extra
            'stats' => $stats
    ]);
    ?>
</div>