<?php
// src/helpers.php
if (!defined('ABSPATH')) exit;

/**
 * Obtiene eventos recientes para el dashboard del psicólogo
 */
function openmind_get_recent_events(int $psychologist_id, int $limit = 10): array {
    global $wpdb;

    $events = [];

    // Mensajes recientes
    $messages = $wpdb->get_results($wpdb->prepare("
        SELECT m.created_at, u.display_name, m.message
        FROM {$wpdb->prefix}openmind_messages m
        JOIN {$wpdb->users} u ON m.sender_id = u.ID
        WHERE m.receiver_id = %d
        ORDER BY m.created_at DESC
        LIMIT 5
    ", $psychologist_id));

    foreach ($messages as $msg) {
        $events[] = [
            'type' => 'message',
            'icon' => '💬',
            'text' => "{$msg->display_name} te envió un mensaje",
            'time' => human_time_diff(strtotime($msg->created_at), current_time('timestamp')) . ' atrás',
            'timestamp' => strtotime($msg->created_at)
        ];
    }

    // Actividades completadas
    $completed = $wpdb->get_results($wpdb->prepare("
        SELECT p.post_title, pm.meta_value as completed_at, u.display_name
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'completed_at'
        JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'assigned_to'
        JOIN {$wpdb->users} u ON pm2.meta_value = u.ID
        WHERE p.post_author = %d AND p.post_type = 'activity'
        ORDER BY pm.meta_value DESC
        LIMIT 5
    ", $psychologist_id));

    foreach ($completed as $activity) {
        $events[] = [
            'type' => 'activity',
            'icon' => '✅',
            'text' => "{$activity->display_name} completó: {$activity->post_title}",
            'time' => human_time_diff(strtotime($activity->completed_at), current_time('timestamp')) . ' atrás',
            'timestamp' => strtotime($activity->completed_at)
        ];
    }

    // Entradas de bitácora recientes
    $diary_entries = $wpdb->get_results($wpdb->prepare("
        SELECT d.created_at, u.display_name
        FROM {$wpdb->prefix}openmind_diary d
        JOIN {$wpdb->users} u ON d.patient_id = u.ID
        JOIN {$wpdb->usermeta} um ON u.ID = um.user_id 
        WHERE um.meta_key = 'psychologist_id' AND um.meta_value = %d
        ORDER BY d.created_at DESC
        LIMIT 3
    ", $psychologist_id));

    foreach ($diary_entries as $entry) {
        $events[] = [
            'type' => 'diary',
            'icon' => '📝',
            'text' => "{$entry->display_name} escribió en su bitácora",
            'time' => human_time_diff(strtotime($entry->created_at), current_time('timestamp')) . ' atrás',
            'timestamp' => strtotime($entry->created_at)
        ];
    }

    // Ordenar por timestamp y limitar
    usort($events, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

    return array_slice($events, 0, $limit);
}

/**
 * Incluye un template del plugin
 */
function openmind_include_template(string $template_path, array $data = []): void {
    extract($data);
    $file = OPENMIND_PATH . "templates/{$template_path}.php";

    if (file_exists($file)) {
        include $file;
    }
}