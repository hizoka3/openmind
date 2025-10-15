<?php // src/Core/Migration.php
namespace Openmind\Core;

/**
 * Migraciones de base de datos
 *
 * USAR: /wp-admin/?openmind_migrate=activate_patients&confirm=yes
 */
class Migration {

    public static function register(): void {
        add_action('admin_init', [self::class, 'handleMigrations']);
    }

    public static function handleMigrations(): void {
        // Solo admin puede ejecutar migraciones
        if (!current_user_can('manage_options')) {
            return;
        }

        // Verificar si hay una migración pendiente
        $migration = $_GET['openmind_migrate'] ?? null;
        $confirm = $_GET['confirm'] ?? null;

        if (!$migration) {
            return;
        }

        // Seguridad: requiere confirmación
        if ($confirm !== 'yes') {
            wp_die(
                '<h1>⚠️ Confirmar Migración</h1>' .
                '<p>Estás a punto de ejecutar la migración: <strong>' . esc_html($migration) . '</strong></p>' .
                '<p><a href="' . esc_url(add_query_arg(['openmind_migrate' => $migration, 'confirm' => 'yes'])) . '" class="button button-primary">Confirmar y Ejecutar</a></p>' .
                '<p><a href="' . esc_url(admin_url()) . '" class="button">Cancelar</a></p>',
                'Confirmar Migración'
            );
        }

        // Ejecutar migración según el tipo
        switch ($migration) {
            case 'activate_patients':
                self::activateExistingPatients();
                break;

            default:
                wp_die('Migración no encontrada');
        }
    }

    /**
     * Migración: Activar pacientes existentes que tienen psicólogo
     */
    private static function activateExistingPatients(): void {
        echo '<div style="max-width: 800px; margin: 50px auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
        echo '<h1 style="color: #2271b1;">🔄 Migración: Activar Pacientes</h1>';
        echo '<p style="color: #666;">Activando pacientes que tienen psicólogo asignado...</p>';
        echo '<hr style="margin: 30px 0;">';

        // Obtener todos los pacientes
        $patients = get_users(['role' => 'patient']);

        $activated = 0;
        $skipped = 0;

        foreach ($patients as $patient) {
            // Verificar si tiene psicólogo asignado
            $has_psychologist = get_user_meta($patient->ID, 'psychologist_id', true);

            // Verificar status actual
            $current_status = get_user_meta($patient->ID, 'openmind_status', true);

            if ($has_psychologist && $current_status !== 'active') {
                // Activar paciente
                update_user_meta($patient->ID, 'openmind_status', 'active');
                update_user_meta($patient->ID, 'openmind_activation_date', time());

                echo "<p style='color: #00a32a; padding: 8px; background: #f0f6fc; border-left: 4px solid #00a32a;'>";
                echo "✅ <strong>Activado:</strong> {$patient->display_name} ({$patient->user_email})";
                echo "</p>";
                $activated++;
            } elseif (!$has_psychologist) {
                echo "<p style='color: #999; padding: 8px; background: #f6f7f7; border-left: 4px solid #ddd;'>";
                echo "⏭️ <strong>Sin psicólogo:</strong> {$patient->display_name} ({$patient->user_email})";
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
        echo "<p style='margin: 5px 0;'><strong>⏭️ Omitidos:</strong> {$skipped}</p>";
        echo "<p style='margin: 5px 0;'><strong>📋 Total procesados:</strong> " . count($patients) . "</p>";
        echo '</div>';

        echo '<div style="margin-top: 30px; text-align: center;">';
        echo '<a href="' . esc_url(admin_url()) . '" class="button button-primary" style="padding: 10px 30px; height: auto; font-size: 14px;">Volver al Dashboard</a>';
        echo '</div>';

        echo '</div>';
        exit;
    }
}