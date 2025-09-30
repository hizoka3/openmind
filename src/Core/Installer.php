<?php // src/Core/Installer.php
namespace Openmind\Core;

class Installer {

    public static function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Tabla relación psicólogo-paciente
        $sql[] = "CREATE TABLE {$wpdb->prefix}openmind_relationships (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            psychologist_id bigint(20) unsigned NOT NULL,
            patient_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_relationship (psychologist_id, patient_id),
            KEY patient_id (patient_id)
        ) $charset;";

        // Tabla mensajes
        $sql[] = "CREATE TABLE {$wpdb->prefix}openmind_messages (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            sender_id bigint(20) unsigned NOT NULL,
            receiver_id bigint(20) unsigned NOT NULL,
            message text NOT NULL,
            is_read tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY sender_id (sender_id),
            KEY receiver_id (receiver_id),
            KEY created_at (created_at)
        ) $charset;";

        // Tabla bitácora
        $sql[] = "CREATE TABLE {$wpdb->prefix}openmind_diary (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            patient_id bigint(20) unsigned NOT NULL,
            content text NOT NULL,
            mood varchar(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY patient_id (patient_id),
            KEY created_at (created_at)
        ) $charset;";

        foreach ($sql as $query) {
            dbDelta($query);
        }

        update_option('openmind_db_version', OPENMIND_VERSION);

        // Crear páginas de dashboard
        self::createPages();
    }

    private static function createPages(): void {
        // Dashboard Psicólogo
        if (!get_page_by_path('dashboard-psicologo')) {
            wp_insert_post([
                'post_title' => 'Dashboard Psicólogo',
                'post_name' => 'dashboard-psicologo',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '<!-- OpenMind Dashboard -->'
            ]);
        }

        // Dashboard Paciente
        if (!get_page_by_path('dashboard-paciente')) {
            wp_insert_post([
                'post_title' => 'Dashboard Paciente',
                'post_name' => 'dashboard-paciente',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '<!-- OpenMind Dashboard -->'
            ]);
        }
    }
}