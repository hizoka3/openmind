<?php // openmind.php
/**
 * Plugin Name: OpenMind
 * Description: Gestión de pacientes y psicólogos
 * Version: 2.3.2
 * Author: Pez web
 * Text Domain: openmind
 */

if (!defined('ABSPATH')) exit;

define('OPENMIND_VERSION', '2.3.2');
define('OPENMIND_PATH', plugin_dir_path(__FILE__));
define('OPENMIND_URL', plugin_dir_url(__FILE__));

// Configuración Reservo
define('OPENMIND_RESERVO_URL', 'https://agendamiento.reservo.cl/makereserva/agenda/L06r9cp0s0IB8X847596yx5547c2Ql');

// Feature flags
define('OPENMIND_SUBSCRIPTION_ENABLED', false); // Cambiar a true cuando se active suscripciones

require_once OPENMIND_PATH . 'vendor/autoload.php';

// Solo para desarrollo - ELIMINAR en producción
if (defined('WP_DEBUG') && WP_DEBUG) {
    require_once OPENMIND_PATH . 'test-setup.php';
}

use Openmind\Core\Plugin;
use Openmind\Core\Cron;
use Openmind\Core\Migration;

register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);

add_action('plugins_loaded', function() {
    Plugin::init();
    Cron::register();
    Migration::register();
});