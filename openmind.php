<?php // openmind.php
/**
 * Plugin Name: OpenMind
 * Description: Gestión de pacientes y psicólogos
 * Version: 1.0.0
 * Author: Pez Web
 * Text Domain: openmind
 */

if (!defined('ABSPATH')) exit;

define('OPENMIND_VERSION', '1.0.0');
define('OPENMIND_PATH', plugin_dir_path(__FILE__));
define('OPENMIND_URL', plugin_dir_url(__FILE__));

require_once OPENMIND_PATH . 'vendor/autoload.php';

use Openmind\Core\Plugin;

register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);

add_action('plugins_loaded', [Plugin::class, 'init']);