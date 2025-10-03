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

        // Tabla bitácora/diario - ACTUALIZADA
        $sql[] = "CREATE TABLE {$wpdb->prefix}openmind_diary (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            patient_id bigint(20) unsigned NOT NULL,
            author_id bigint(20) unsigned NOT NULL,
            content text NOT NULL,
            mood varchar(20),
            is_private tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY patient_id (patient_id),
            KEY author_id (author_id),
            KEY created_at (created_at),
            KEY is_private (is_private)
        ) $charset;";

        foreach ($sql as $query) {
            dbDelta($query);
        }

        // Migrar datos existentes si ya existe la tabla
        self::migrateExistingDiary();

        update_option('openmind_db_version', OPENMIND_VERSION);
        self::createPages();
    }

    private static function migrateExistingDiary(): void {
        global $wpdb;

        // Verificar si la columna author_id ya existe
        $column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$wpdb->prefix}openmind_diary LIKE 'author_id'"
        );

        if (empty($column_exists)) {
            // Agregar columna author_id
            $wpdb->query(
                "ALTER TABLE {$wpdb->prefix}openmind_diary 
                ADD COLUMN author_id bigint(20) unsigned NOT NULL AFTER patient_id"
            );

            // Agregar columna updated_at si no existe
            $wpdb->query(
                "ALTER TABLE {$wpdb->prefix}openmind_diary 
                ADD COLUMN updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
            );

            // Actualizar entradas existentes: author_id = patient_id (asumimos que el paciente las escribió)
            $wpdb->query(
                "UPDATE {$wpdb->prefix}openmind_diary 
                SET author_id = patient_id 
                WHERE author_id = 0"
            );
        }
    }

    private static function createPages(): void {
        if (!get_page_by_path('dashboard-psicologo')) {
            wp_insert_post([
                'post_title' => 'Dashboard Psicólogo',
                'post_name' => 'dashboard-psicologo',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '<!-- OpenMind Dashboard -->'
            ]);
        }

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