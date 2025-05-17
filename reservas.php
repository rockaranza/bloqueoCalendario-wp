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
    $plugin = Reservas::get_instance();
    $plugin->run();
}
add_action('plugins_loaded', 'reservas_init');

// Activar el plugin
function reservas_activate() {
    require_once RESERVAS_PLUGIN_DIR . 'includes/class-reservas-activator.php';
    Reservas_Activator::activate();
    
    // Agregar opción por defecto para iCal
    add_option('reservas_ical_url', '');
}
register_activation_hook(__FILE__, 'reservas_activate');

// Desactivar el plugin
function reservas_deactivate() {
    require_once RESERVAS_PLUGIN_DIR . 'includes/class-reservas-deactivator.php';
    Reservas_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'reservas_deactivate');

// Registrar opciones de configuración
add_action('admin_init', function() {
    register_setting(
        'reservas_options',
        'reservas_ical_url',
        array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ''
        )
    );
}); 