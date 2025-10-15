<?php // src/Core/Cron.php
namespace Openmind\Core;

class Cron {

    /**
     * Registra el cron job
     */
    public static function register(): void {
        add_action('openmind_check_expired_patients', [self::class, 'checkExpiredPatients']);

        // Registrar evento diario si no existe
        if (!wp_next_scheduled('openmind_check_expired_patients')) {
            wp_schedule_event(time(), 'daily', 'openmind_check_expired_patients');
        }
    }

    /**
     * Desactiva pacientes con suscripción expirada
     */
    public static function checkExpiredPatients(): void {
        // Solo ejecutar si las suscripciones están habilitadas
        if (!OPENMIND_SUBSCRIPTION_ENABLED) {
            return;
        }

        global $wpdb;

        $now = time();

        // Obtener pacientes activos con fecha de expiración
        $expired_patients = $wpdb->get_results($wpdb->prepare("
            SELECT user_id, meta_value as expiration_date
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'openmind_expiration_date'
            AND meta_value IS NOT NULL
            AND meta_value < %d
            AND user_id IN (
                SELECT user_id FROM {$wpdb->usermeta}
                WHERE meta_key = 'openmind_status'
                AND meta_value = 'active'
            )
        ", $now));

        foreach ($expired_patients as $patient) {
            AccessControl::deactivatePatient($patient->user_id);

            // Opcional: Enviar email de notificación
            self::notifyExpiration($patient->user_id);
        }
    }

    /**
     * Notifica al paciente que su suscripción expiró
     */
    private static function notifyExpiration(int $patient_id): void {
        $patient = get_userdata($patient_id);

        if (!$patient) return;

        wp_mail(
            $patient->user_email,
            'Tu cuenta OpenMind ha expirado',
            sprintf(
                'Hola %s, tu acceso a la plataforma ha expirado. Contacta a tu psicólogo para renovar.',
                $patient->display_name
            )
        );
    }

    /**
     * Limpia el cron job al desactivar plugin
     */
    public static function unregister(): void {
        $timestamp = wp_next_scheduled('openmind_check_expired_patients');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'openmind_check_expired_patients');
        }
    }
}