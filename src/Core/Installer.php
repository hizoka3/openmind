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

        // Tabla diario de vida (paciente)
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

        // NUEVA: Tabla session notes (bitácora psicólogo)
        $sql[] = "CREATE TABLE {$wpdb->prefix}openmind_session_notes (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            psychologist_id bigint(20) unsigned NOT NULL,
            patient_id bigint(20) unsigned NOT NULL,
            session_number int unsigned NOT NULL DEFAULT 0,
            content text NOT NULL,
            mood_assessment varchar(50),
            next_steps text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY psychologist_id (psychologist_id),
            KEY patient_id (patient_id),
            KEY session_number (patient_id, session_number),
            KEY created_at (created_at)
        ) $charset;";

        // NUEVA: Tabla attachments (imágenes para ambos)
        $sql[] = "CREATE TABLE {$wpdb->prefix}openmind_attachments (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            entry_type varchar(20) NOT NULL,
            entry_id bigint(20) unsigned NOT NULL,
            file_name varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_type varchar(50),
            file_size bigint(20),
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY entry_lookup (entry_type, entry_id),
            KEY uploaded_at (uploaded_at)
        ) $charset;";

        foreach ($sql as $query) {
            dbDelta($query);
        }

        // Migrar datos existentes
        self::migrateToSessionNotes();

        update_option('openmind_db_version', OPENMIND_VERSION);
        self::createPages();
    }

    private static function migrateToSessionNotes(): void {
        global $wpdb;

        // Verificar si ya se migró
        if (get_option('openmind_session_notes_migrated')) {
            return;
        }

        // Migrar bitácoras (author_id != patient_id) → session_notes
        $wpdb->query("
            INSERT INTO {$wpdb->prefix}openmind_session_notes 
            (psychologist_id, patient_id, session_number, content, mood_assessment, created_at, updated_at)
            SELECT 
                author_id as psychologist_id,
                patient_id,
                0 as session_number,
                content,
                mood as mood_assessment,
                created_at,
                updated_at
            FROM {$wpdb->prefix}openmind_diary
            WHERE author_id != patient_id
        ");

        // Recalcular session_number para cada paciente
        $patients = $wpdb->get_col("
            SELECT DISTINCT patient_id 
            FROM {$wpdb->prefix}openmind_session_notes
        ");

        foreach ($patients as $patient_id) {
            $notes = $wpdb->get_results($wpdb->prepare("
                SELECT id 
                FROM {$wpdb->prefix}openmind_session_notes
                WHERE patient_id = %d
                ORDER BY created_at ASC
            ", $patient_id));

            $session_num = 1;
            foreach ($notes as $note) {
                $wpdb->update(
                    $wpdb->prefix . 'openmind_session_notes',
                    ['session_number' => $session_num++],
                    ['id' => $note->id],
                    ['%d'],
                    ['%d']
                );
            }
        }

        // Eliminar bitácoras de wp_openmind_diary
        $wpdb->query("
            DELETE FROM {$wpdb->prefix}openmind_diary
            WHERE author_id != patient_id
        ");

        update_option('openmind_session_notes_migrated', true);
    }

    private static function createPages(): void {
        $pages = [
            'dashboard-psicologo' => 'Dashboard Psicólogo',
            'dashboard-paciente' => 'Dashboard Paciente'
        ];

        foreach ($pages as $slug => $title) {
            if (!get_page_by_path($slug)) {
                wp_insert_post([
                    'post_title' => $title,
                    'post_name' => $slug,
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_content' => ''
                ]);
            }
        }
    }
}