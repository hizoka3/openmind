<?php // src/Core/Migration.php
namespace Openmind\Core;

class Migration {

    public static function register(): void {
        add_action('admin_init', [self::class, 'handleMigrations']);
    }

    public static function handleMigrations(): void {
        if (!current_user_can('manage_options')) return;

        $migration = $_GET['openmind_migrate'] ?? null;
        $confirm = $_GET['confirm'] ?? null;

        if (!$migration) return;

        if ($confirm !== 'yes') {
            wp_die(
                '<h1>⚠️ Confirmar Migración</h1>' .
                '<p>Estás a punto de ejecutar la migración: <strong>' . esc_html($migration) . '</strong></p>' .
                '<p><a href="' . esc_url(add_query_arg(['openmind_migrate' => $migration, 'confirm' => 'yes'])) . '" class="button button-primary">Confirmar y Ejecutar</a></p>' .
                '<p><a href="' . esc_url(admin_url()) . '" class="button">Cancelar</a></p>',
                'Confirmar Migración'
            );
        }

        switch ($migration) {
            case 'activate_patients':
                self::activateExistingPatients();
                break;
            case 'add_public_content':
                self::addPublicContentColumn();
                break;
            default:
                wp_die('Migración no encontrada');
        }
    }

    /**
     * Migración: Agregar columna public_content y renombrar content → private_notes
     */
    private static function addPublicContentColumn(): void {
        global $wpdb;

        echo '<div style="max-width: 800px; margin: 50px auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
        echo '<h1 style="color: #2271b1;">🔄 Migración: Agregar Contenido Público</h1>';
        echo '<p style="color: #666;">Modificando estructura de bitácora...</p>';
        echo '<hr style="margin: 30px 0;">';

        $table = $wpdb->prefix . 'openmind_session_notes';

        // 1. Verificar si ya existe public_content
        $columns = $wpdb->get_col("DESCRIBE {$table}");

        if (in_array('public_content', $columns)) {
            echo "<p style='color: #d63638; padding: 8px; background: #fcf0f1; border-left: 4px solid #d63638;'>";
            echo "❌ La columna 'public_content' ya existe. Migración cancelada.";
            echo "</p>";
        } else {
            // 2. Renombrar content → private_notes
            if (in_array('content', $columns)) {
                $wpdb->query("ALTER TABLE {$table} CHANGE COLUMN content private_notes TEXT NOT NULL");
                echo "<p style='color: #00a32a; padding: 8px; background: #f0f6fc; border-left: 4px solid #00a32a;'>";
                echo "✅ Columna 'content' renombrada a 'private_notes'";
                echo "</p>";
            }

            // 3. Agregar nueva columna public_content
            $wpdb->query("ALTER TABLE {$table} ADD COLUMN public_content TEXT NULL AFTER private_notes");
            echo "<p style='color: #00a32a; padding: 8px; background: #f0f6fc; border-left: 4px solid #00a32a;'>";
            echo "✅ Nueva columna 'public_content' agregada";
            echo "</p>";

            // 4. Marcar migración como completada
            update_option('openmind_public_content_migrated', true);
        }

        echo '<hr style="margin: 30px 0;">';
        echo '<div style="background: #2271b1; color: white; padding: 20px; border-radius: 4px;">';
        echo '<h2 style="margin: 0 0 10px 0; color: white;">📊 Resultado</h2>';
        echo "<p style='margin: 5px 0;'>La tabla ahora tiene:</p>";
        echo "<p style='margin: 5px 0;'>- <strong>private_notes</strong> (TEXT NOT NULL) - Solo psicólogo</p>";
        echo "<p style='margin: 5px 0;'>- <strong>public_content</strong> (TEXT NULL) - Visible para paciente</p>";
        echo '</div>';

        echo '<div style="margin-top: 30px; text-align: center;">';
        echo '<a href="' . esc_url(admin_url()) . '" class="button button-primary" style="padding: 10px 30px; height: auto; font-size: 14px;">Volver al Dashboard</a>';
        echo '</div>';

        echo '</div>';
        exit;
    }

    /**
     * Migración: Activar pacientes existentes que tienen psicólogo
     */
    private static function activateExistingPatients(): void {
        echo '<div style="max-width: 800px; margin: 50px auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
        echo '<h1 style="color: #2271b1;">🔄 Migración: Activar Pacientes</h1>';
        echo '<p style="color: #666;">Activando pacientes que tienen psicólogo asignado...</p>';
        echo '<hr style="margin: 30px 0;">';

        $patients = get_users(['role' => 'patient']);
        $activated = 0;
        $skipped = 0;

        foreach ($patients as $patient) {
            $has_psychologist = get_user_meta($patient->ID, 'psychologist_id', true);
            $current_status = get_user_meta($patient->ID, 'openmind_status', true);

            if ($has_psychologist && $current_status !== 'active') {
                update_user_meta($patient->ID, 'openmind_status', 'active');
                update_user_meta($patient->ID, 'openmind_activation_date', time());

                echo "<p style='color: #00a32a; padding: 8px; background: #f0f6fc; border-left: 4px solid #00a32a;'>";
                echo "✅ <strong>Activado:</strong> {$patient->display_name} ({$patient->user_email})";
                echo "</p>";
                $activated++;
            } elseif (!$has_psychologist) {
                echo "<p style='color: #999; padding: 8px; background: #f6f7f7; border-left: 4px solid #ddd;'>";
                echo "⭕️ <strong>Sin psicólogo:</strong> {$patient->display_name} ({$patient->user_email})";
                echo "</p>";
                $skipped++;
            } else {
                echo "<p style='color: #666; padding: 8px; background: #f6f7f7; border-left: 4px solid #ddd;'>";
                echo "✓ <strong>Ya activo:</strong> {$patient->display_name} ({$patient->user_email})";
                echo "</p>";
                $skipped++;
            }
        }

        echo '<hr style="margin: 30px 0;">';
        echo '<div style="background: #2271b1; color: white; padding: 20px; border-radius: 4px;">';
        echo '<h2 style="margin: 0 0 10px 0; color: white;">📊 Resultado</h2>';
        echo "<p style='margin: 5px 0;'><strong>✅ Activados:</strong> {$activated}</p>";
        echo "<p style='margin: 5px 0;'><strong>⭕️ Omitidos:</strong> {$skipped}</p>";
        echo "<p style='margin: 5px 0;'><strong>📋 Total procesados:</strong> " . count($patients) . "</p>";
        echo '</div>';

        echo '<div style="margin-top: 30px; text-align: center;">';
        echo '<a href="' . esc_url(admin_url()) . '" class="button button-primary" style="padding: 10px 30px; height: auto; font-size: 14px;">Volver al Dashboard</a>';
        echo '</div>';

        echo '</div>';
        exit;
    }
}