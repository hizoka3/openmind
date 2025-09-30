<?php // test-setup.php
/**
 * Script temporal para crear usuarios de prueba
 * Ejecutar desde: wp-admin/admin.php?page=openmind-setup
 * ELIMINAR después de testing
 */

add_action('admin_menu', function() {
    add_menu_page(
        'OpenMind Setup',
        'Setup Test',
        'manage_options',
        'openmind-setup',
        'openmind_setup_page'
    );
});

function openmind_setup_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Sin permisos');
    }

    if (isset($_POST['create_test_users'])) {
        $results = openmind_create_test_users();
        echo '<div class="notice notice-success"><p>' . $results . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>🧪 OpenMind - Setup de Testing</h1>

        <div class="card">
            <h2>Crear Usuarios de Prueba</h2>
            <p>Esto creará:</p>
            <ul>
                <li>✅ 1 Psicólogo: <code>psicologo@test.com</code> / <code>test123</code></li>
                <li>✅ 2 Pacientes: <code>paciente1@test.com</code> y <code>paciente2@test.com</code> / <code>test123</code></li>
                <li>✅ Relaciones psicólogo-paciente</li>
                <li>✅ 3 actividades de ejemplo</li>
            </ul>

            <form method="post">
                <?php wp_nonce_field('openmind_setup'); ?>
                <input type="hidden" name="create_test_users" value="1">
                <button type="submit" class="button button-primary button-large">
                    🚀 Crear Usuarios de Prueba
                </button>
            </form>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>📝 URLs para Testing</h2>
            <ul>
                <li><strong>Dashboard Psicólogo:</strong> <a href="<?php echo home_url('/dashboard-psicologo'); ?>" target="_blank"><?php echo home_url('/dashboard-psicologo'); ?></a></li>
                <li><strong>Dashboard Paciente:</strong> <a href="<?php echo home_url('/dashboard-paciente'); ?>" target="_blank"><?php echo home_url('/dashboard-paciente'); ?></a></li>
            </ul>
            <p><em>Nota: Recuerda crear las páginas si no existen.</em></p>
        </div>
    </div>

    <style>
        .card { padding: 20px; background: white; max-width: 800px; }
        .card ul { list-style: none; }
        .card li { padding: 5px 0; }
    </style>
    <?php
}

function openmind_create_test_users() {
    global $wpdb;

    // 0. Crear páginas si no existen
    if (!get_page_by_path('dashboard-psicologo')) {
        wp_insert_post([
            'post_title' => 'Dashboard Psicólogo',
            'post_name' => 'dashboard-psicologo',
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);
    }

    if (!get_page_by_path('dashboard-paciente')) {
        wp_insert_post([
            'post_title' => 'Dashboard Paciente',
            'post_name' => 'dashboard-paciente',
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);
    }

    // 1. Crear Psicólogo
    $psychologist_id = wp_insert_user([
        'user_login' => 'psicologo_test',
        'user_email' => 'psicologo@test.com',
        'user_pass' => 'test123',
        'display_name' => 'Dr. Juan Pérez',
        'role' => 'psychologist'
    ]);

    if (is_wp_error($psychologist_id)) {
        $psychologist_id = get_user_by('email', 'psicologo@test.com')->ID;
    }

    // 2. Crear Pacientes
    $patients = [];

    $patient1_id = wp_insert_user([
        'user_login' => 'paciente1_test',
        'user_email' => 'paciente1@test.com',
        'user_pass' => 'test123',
        'display_name' => 'María González',
        'role' => 'patient'
    ]);

    if (is_wp_error($patient1_id)) {
        $patient1_id = get_user_by('email', 'paciente1@test.com')->ID;
    }
    $patients[] = $patient1_id;

    $patient2_id = wp_insert_user([
        'user_login' => 'paciente2_test',
        'user_email' => 'paciente2@test.com',
        'user_pass' => 'test123',
        'display_name' => 'Carlos Rodríguez',
        'role' => 'patient'
    ]);

    if (is_wp_error($patient2_id)) {
        $patient2_id = get_user_by('email', 'paciente2@test.com')->ID;
    }
    $patients[] = $patient2_id;

    // 3. Crear relaciones
    foreach ($patients as $patient_id) {
        $wpdb->replace(
            $wpdb->prefix . 'openmind_relationships',
            [
                'psychologist_id' => $psychologist_id,
                'patient_id' => $patient_id
            ],
            ['%d', '%d']
        );

        update_user_meta($patient_id, 'psychologist_id', $psychologist_id);
    }

    // 4. Crear actividades de ejemplo
    $activities = [
        [
            'title' => 'Ejercicio de Respiración',
            'content' => 'Practica respiración profunda durante 10 minutos al día. Inhala por 4 segundos, mantén por 4, exhala por 4.',
            'due_date' => date('Y-m-d', strtotime('+3 days'))
        ],
        [
            'title' => 'Diario de Gratitud',
            'content' => 'Escribe 3 cosas por las que estés agradecido cada día antes de dormir.',
            'due_date' => date('Y-m-d', strtotime('+7 days'))
        ],
        [
            'title' => 'Caminata Consciente',
            'content' => 'Sal a caminar 20 minutos prestando atención a tus sentidos: qué ves, oyes, sientes.',
            'due_date' => date('Y-m-d', strtotime('+2 days'))
        ]
    ];

    foreach ($activities as $index => $activity) {
        $activity_id = wp_insert_post([
            'post_type' => 'activity',
            'post_title' => $activity['title'],
            'post_content' => $activity['content'],
            'post_status' => 'publish',
            'post_author' => $psychologist_id
        ]);

        if ($activity_id) {
            update_post_meta($activity_id, 'due_date', $activity['due_date']);
            update_post_meta($activity_id, 'assigned_to', $patients[0]); // Asignar a paciente1
            update_post_meta($activity_id, 'completed', 0);
        }
    }

    // 5. Crear entrada de bitácora de ejemplo
    $wpdb->insert(
        $wpdb->prefix . 'openmind_diary',
        [
            'patient_id' => $patients[0],
            'content' => 'Hoy me sentí mejor. La terapia está ayudando.',
            'mood' => 'feliz'
        ],
        ['%d', '%s', '%s']
    );

    return '✅ Usuarios creados exitosamente! Usa las credenciales: test123';
}