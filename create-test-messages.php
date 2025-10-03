<?php
// openmind-plugin/create-test-messages.php
require_once '../../../wp-load.php';

if (!current_user_can('manage_options')) {
    die('Sin permisos');
}

global $wpdb;

// 1. Obtener IDs de usuarios
$psychologist = get_user_by('email', 'psicologo@test.com');
$patient1 = get_user_by('email', 'paciente1@test.com');
$patient2 = get_user_by('email', 'paciente2@test.com');

if (!$psychologist || !$patient1 || !$patient2) {
    die('❌ Error: Usuarios de prueba no encontrados. Ejecuta primero test-setup.php');
}

echo "<h2>🔍 IDs Encontrados:</h2>";
echo "Psicólogo: {$psychologist->ID} - {$psychologist->display_name}<br>";
echo "Paciente 1: {$patient1->ID} - {$patient1->display_name}<br>";
echo "Paciente 2: {$patient2->ID} - {$patient2->display_name}<br><br>";

// 2. Limpiar mensajes anteriores (opcional)
$wpdb->query("DELETE FROM {$wpdb->prefix}openmind_messages");
echo "🗑️ Mensajes anteriores eliminados<br><br>";

// 3. Crear conversación Psicólogo <-> Paciente 1
$messages_p1 = [
    [
        'sender' => $psychologist->ID,
        'receiver' => $patient1->ID,
        'message' => 'Hola María, ¿cómo te sientes hoy?',
        'is_read' => 1,
        'days_ago' => 2
    ],
    [
        'sender' => $patient1->ID,
        'receiver' => $psychologist->ID,
        'message' => 'Hola doctor, me siento mejor gracias a los ejercicios',
        'is_read' => 1,
        'days_ago' => 2
    ],
    [
        'sender' => $psychologist->ID,
        'receiver' => $patient1->ID,
        'message' => 'Me alegra mucho escucharlo. ¿Has completado la actividad de respiración?',
        'is_read' => 1,
        'days_ago' => 1
    ],
    [
        'sender' => $patient1->ID,
        'receiver' => $psychologist->ID,
        'message' => 'Sí, la completé ayer. Me ayudó mucho con la ansiedad',
        'is_read' => 1,
        'days_ago' => 1
    ],
    [
        'sender' => $psychologist->ID,
        'receiver' => $patient1->ID,
        'message' => 'Excelente progreso. Te asigné una nueva actividad para esta semana',
        'is_read' => 0,
        'hours_ago' => 2
    ],
    [
        'sender' => $patient1->ID,
        'receiver' => $psychologist->ID,
        'message' => 'Perfecto, la revisaré ahora. ¿A qué hora debería hacerla?',
        'is_read' => 0,
        'hours_ago' => 1
    ],
];

// 4. Crear conversación Psicólogo <-> Paciente 2
$messages_p2 = [
    [
        'sender' => $psychologist->ID,
        'receiver' => $patient2->ID,
        'message' => 'Hola Carlos, ¿cómo fue tu semana?',
        'is_read' => 1,
        'days_ago' => 3
    ],
    [
        'sender' => $patient2->ID,
        'receiver' => $psychologist->ID,
        'message' => 'Hola doctor, tuve algunos días difíciles con el trabajo',
        'is_read' => 1,
        'days_ago' => 3
    ],
    [
        'sender' => $psychologist->ID,
        'receiver' => $patient2->ID,
        'message' => 'Entiendo. ¿Quieres que hablemos de eso en la próxima sesión?',
        'is_read' => 0,
        'hours_ago' => 5
    ],
    [
        'sender' => $patient2->ID,
        'receiver' => $psychologist->ID,
        'message' => 'Sí, me gustaría. ¿Cuándo es nuestra próxima cita?',
        'is_read' => 0,
        'hours_ago' => 3
    ],
];

// 5. Insertar mensajes
$total_inserted = 0;

foreach (array_merge($messages_p1, $messages_p2) as $msg) {
    // Calcular timestamp
    if (isset($msg['days_ago'])) {
        $timestamp = date('Y-m-d H:i:s', strtotime("-{$msg['days_ago']} days"));
    } else {
        $timestamp = date('Y-m-d H:i:s', strtotime("-{$msg['hours_ago']} hours"));
    }

    $inserted = $wpdb->insert(
        $wpdb->prefix . 'openmind_messages',
        [
            'sender_id' => $msg['sender'],
            'receiver_id' => $msg['receiver'],
            'message' => $msg['message'],
            'is_read' => $msg['is_read'],
            'created_at' => $timestamp
        ],
        ['%d', '%d', '%s', '%d', '%s']
    );

    if ($inserted) {
        $total_inserted++;
    }
}

echo "<h2>✅ Resultados:</h2>";
echo "📨 {$total_inserted} mensajes insertados correctamente<br><br>";

// 6. Verificar
$total_messages = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}openmind_messages");
$unread_psychologist = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}openmind_messages WHERE receiver_id = %d AND is_read = 0",
    $psychologist->ID
));
$unread_patient1 = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}openmind_messages WHERE receiver_id = %d AND is_read = 0",
    $patient1->ID
));
$unread_patient2 = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}openmind_messages WHERE receiver_id = %d AND is_read = 0",
    $patient2->ID
));

echo "<h2>📊 Estadísticas:</h2>";
echo "Total mensajes en BD: {$total_messages}<br>";
echo "No leídos psicólogo: {$unread_psychologist}<br>";
echo "No leídos paciente 1: {$unread_patient1}<br>";
echo "No leídos paciente 2: {$unread_patient2}<br><br>";

echo "<h2>🎯 Próximos pasos:</h2>";
echo "1. Vuelve a ejecutar los tests en la consola<br>";
echo "2. Deberías ver mensajes ahora<br>";
echo "3. ⚠️ <strong>Elimina este archivo después de usarlo</strong><br>";