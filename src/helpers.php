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
            'color' => 'blue',
            'text' => "{$msg->display_name} te envió un mensaje",
            'time' => openmind_time_ago($msg->created_at),
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
            'color' => 'green',
            'text' => "{$activity->display_name} completó: {$activity->post_title}",
            'time' => openmind_time_ago($activity->completed_at),
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
            'color' => 'purple',
            'text' => "{$entry->display_name} escribió en su bitácora",
            'time' => openmind_time_ago($entry->created_at),
            'timestamp' => strtotime($entry->created_at)
        ];
    }

    // Ordenar por timestamp y limitar
    usort($events, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

    return array_slice($events, 0, $limit);
}

/**
 * Calcula tiempo transcurrido de forma correcta (respetando timezone de WP)
 */
function openmind_time_ago(string $datetime): string {
    // Convertir de GMT a hora local de WordPress
    $local_datetime = get_date_from_gmt($datetime);
    $timestamp = strtotime($local_datetime);
    $current_time = current_time('timestamp');

    $diff = $current_time - $timestamp;

    // Si es negativo (fecha futura), mostrar "hace un momento"
    if ($diff < 0) {
        return 'hace un momento';
    }

    // Calcular unidades de tiempo
    $seconds = $diff;
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);
    $weeks = floor($days / 7);
    $months = floor($days / 30);
    $years = floor($days / 365);

    if ($seconds < 60) {
        return 'hace un momento';
    } elseif ($minutes < 60) {
        return $minutes === 1 ? 'hace 1 minuto' : "hace {$minutes} minutos";
    } elseif ($hours < 24) {
        return $hours === 1 ? 'hace 1 hora' : "hace {$hours} horas";
    } elseif ($days < 7) {
        return $days === 1 ? 'hace 1 día' : "hace {$days} días";
    } elseif ($weeks < 4) {
        return $weeks === 1 ? 'hace 1 semana' : "hace {$weeks} semanas";
    } elseif ($months < 12) {
        return $months === 1 ? 'hace 1 mes' : "hace {$months} meses";
    } else {
        return $years === 1 ? 'hace 1 año' : "hace {$years} años";
    }
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

/**
 * Incluir template del plugin con args
 */
function openmind_template(string $template_path, array $args = []): void {
    $file = OPENMIND_PATH . "templates/{$template_path}.php";

    if (file_exists($file)) {
        extract($args);
        include $file;
    }
}

/**
 * Obtiene eventos recientes para el dashboard del paciente
 */
function openmind_get_patient_recent_events(int $patient_id, int $limit = 10): array {
    global $wpdb;

    $events = [];
    $psychologist_id = get_user_meta($patient_id, 'psychologist_id', true);

    // Mensajes del psicólogo
    if ($psychologist_id) {
        $messages = $wpdb->get_results($wpdb->prepare("
            SELECT m.created_at, u.display_name
            FROM {$wpdb->prefix}openmind_messages m
            JOIN {$wpdb->users} u ON m.sender_id = u.ID
            WHERE m.receiver_id = %d AND m.sender_id = %d
            ORDER BY m.created_at DESC
            LIMIT 5
        ", $patient_id, $psychologist_id));

        foreach ($messages as $msg) {
            $events[] = [
                'type' => 'message',
                'icon' => '💬',
                'color' => 'blue',
                'text' => "{$msg->display_name} te envió un mensaje",
                'time' => openmind_time_ago($msg->created_at),
                'timestamp' => strtotime($msg->created_at)
            ];
        }
    }

    // Actividades asignadas recientemente
    $assigned = $wpdb->get_results($wpdb->prepare("
        SELECT p.post_title, p.post_date
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'assigned_to'
        WHERE pm.meta_value = %d AND p.post_type = 'activity'
        ORDER BY p.post_date DESC
        LIMIT 5
    ", $patient_id));

    foreach ($assigned as $activity) {
        $events[] = [
            'type' => 'assigned',
            'icon' => '📋',
            'color' => 'orange',
            'text' => "Nueva actividad asignada: {$activity->post_title}",
            'time' => openmind_time_ago($activity->post_date),
            'timestamp' => strtotime($activity->post_date)
        ];
    }

    // Actividades completadas
    $completed = $wpdb->get_results($wpdb->prepare("
        SELECT p.post_title, pm.meta_value as completed_at
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'completed_at'
        JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'assigned_to'
        WHERE pm2.meta_value = %d AND p.post_type = 'activity'
        ORDER BY pm.meta_value DESC
        LIMIT 5
    ", $patient_id));

    foreach ($completed as $activity) {
        $events[] = [
            'type' => 'completed',
            'icon' => '✅',
            'color' => 'green',
            'text' => "Completaste: {$activity->post_title}",
            'time' => openmind_time_ago($activity->completed_at),
            'timestamp' => strtotime($activity->completed_at)
        ];
    }

    // Actividades próximas a vencer (dentro de 3 días)
    $due_soon = $wpdb->get_results($wpdb->prepare("
        SELECT p.post_title, pm.meta_value as due_date
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'due_date'
        JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'assigned_to'
        WHERE pm2.meta_value = %d 
        AND p.post_type = 'activity'
        AND pm.meta_value BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)
        AND NOT EXISTS (
            SELECT 1 FROM {$wpdb->postmeta} 
            WHERE post_id = p.ID AND meta_key = 'completed' AND meta_value = '1'
        )
        LIMIT 3
    ", $patient_id));

    foreach ($due_soon as $activity) {
        $events[] = [
            'type' => 'due_soon',
            'icon' => '⏰',
            'color' => 'red',
            'text' => "Próxima a vencer: {$activity->post_title}",
            'time' => openmind_time_ago($activity->due_date),
            'timestamp' => strtotime($activity->due_date)
        ];
    }

    // Ordenar por timestamp y limitar
    usort($events, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

    return array_slice($events, 0, $limit);
}