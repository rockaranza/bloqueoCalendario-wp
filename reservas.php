<?php
/**
 * Plugin Name: Reservas
 * Plugin URI: https://tusitio.com/reservas
 * Description: Plugin de reservas compatible con DIVI para gestionar citas y reservaciones.
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tusitio.com
 * Text Domain: reservas
 * Domain Path: /languages
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('RESERVAS_VERSION', '1.0.0');
define('RESERVAS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RESERVAS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once RESERVAS_PLUGIN_DIR . 'includes/class-reservas.php';

// Inicializar el plugin
function reservas_init() {
    return Reservas::get_instance();
}
add_action('plugins_loaded', 'reservas_init');

// Activar el plugin
function reservas_activate() {
    // Crear tablas necesarias en la base de datos
    require_once RESERVAS_PLUGIN_DIR . 'includes/class-reservas-activator.php';
    Reservas_Activator::activate();
}
register_activation_hook(__FILE__, 'reservas_activate');

// Desactivar el plugin
function reservas_deactivate() {
    // Limpiar datos si es necesario
    require_once RESERVAS_PLUGIN_DIR . 'includes/class-reservas-deactivator.php';
    Reservas_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'reservas_deactivate'); 