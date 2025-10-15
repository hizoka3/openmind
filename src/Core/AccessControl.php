<?php // src/Core/AccessControl.php
namespace Openmind\Core;

class AccessControl {

    private static $allowed_pages_inactive = [
        'dashboard-paciente',
        'perfil-paciente'
    ];

    /**
     * Verifica si un paciente puede acceder a una página
     */
    public static function patientCanAccess(string $page_slug): bool {
        // Si no es paciente, permitir acceso
        if (!current_user_can('patient')) {
            return true;
        }

        $status = get_user_meta(get_current_user_id(), 'openmind_status', true);

        // Si está activo, puede ver todo
        if ($status === 'active') {
            return true;
        }

        // Si está inactivo, solo páginas permitidas
        return in_array($page_slug, self::$allowed_pages_inactive);
    }

    /**
     * Redirige si el paciente no tiene acceso
     * DEBE llamarse ANTES de cualquier output
     */
    public static function redirectIfUnauthorized(string $page_slug): void {
        $can_access = self::patientCanAccess($page_slug);

        if (!$can_access) {
            $user_id = get_current_user_id();

            // Guardar mensaje en transient (expira en 10 segundos)
            set_transient('openmind_toast_' . $user_id, [
                'message' => 'Debes activar tu cuenta para acceder a esta sección',
                'type' => 'error'
            ], 10);

            // Usar wp_redirect en vez de wp_safe_redirect para que funcione antes de headers
            wp_redirect(home_url('/dashboard-paciente/?view=inicio'));
            exit;
        }
    }

    /**
     * Verifica si el paciente está activo
     */
    public static function isPatientActive(int $patient_id = 0): bool {
        $patient_id = $patient_id ?: get_current_user_id();
        $status = get_user_meta($patient_id, 'openmind_status', true);
        return $status === 'active';
    }

    /**
     * Activa un paciente
     */
    public static function activatePatient(int $patient_id, int $days = 0): bool {
        update_user_meta($patient_id, 'openmind_status', 'active');
        update_user_meta($patient_id, 'openmind_activation_date', time());

        // Si se especifican días, calcular expiración
        if ($days > 0 && OPENMIND_SUBSCRIPTION_ENABLED) {
            $expiration = time() + ($days * DAY_IN_SECONDS);
            update_user_meta($patient_id, 'openmind_expiration_date', $expiration);
        }

        return true;
    }

    /**
     * Desactiva un paciente
     */
    public static function deactivatePatient(int $patient_id): bool {
        update_user_meta($patient_id, 'openmind_status', 'inactive');
        return true;
    }
}