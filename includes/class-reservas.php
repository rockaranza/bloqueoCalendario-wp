<?php
if (!defined('ABSPATH')) {
    exit;
}

class Reservas {
    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $admin;
    protected $public;
    protected $google_calendar;
    protected $ajax;

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->plugin_name = 'reservas';
        $this->version = RESERVAS_VERSION;
        
        $this->load_dependencies();
        $this->init_ajax();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reservas-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reservas-ajax.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reservas-google-calendar.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-reservas-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-reservas-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reservas-ical.php';
        
        $this->loader = new Reservas_Loader();
        $this->admin = new Reservas_Admin($this->get_plugin_name(), $this->get_version());
        $this->public = new Reservas_Public($this->get_plugin_name(), $this->get_version());
        $this->google_calendar = new Reservas_Google_Calendar();
    }

    private function define_admin_hooks() {
        // Hooks de administración
        $this->loader->add_action('admin_enqueue_scripts', $this->admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $this->admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $this->admin, 'register_settings');
    }

    private function define_public_hooks() {
        // Hooks públicos
        $this->loader->add_action('wp_enqueue_scripts', $this->public, 'enqueue_scripts');
        $this->loader->add_action('wp_enqueue_scripts', $this->public, 'enqueue_styles');
        
        // Registrar shortcodes
        add_shortcode('reservas_calendario', array($this->public, 'render_calendario'));
        add_shortcode('reservas_formulario', array($this->public, 'render_formulario'));
    }

    private function init_ajax() {
        if (class_exists('Reservas_Ajax')) {
            $this->ajax = new Reservas_Ajax();
        } else {
            error_log('Error: La clase Reservas_Ajax no está disponible');
        }
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
} 