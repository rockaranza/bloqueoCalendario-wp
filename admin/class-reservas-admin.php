<?php
if (!defined('ABSPATH')) {
    exit;
}

class Reservas_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_scripts($hook) {
        // Solo cargar en las páginas del plugin
        if (strpos($hook, 'reservas') === false) {
            return;
        }

        // Estilos y scripts del admin
        wp_enqueue_style(
            'reservas-admin',
            plugin_dir_url(dirname(__FILE__)) . 'admin/css/reservas-admin.css',
            array(),
            $this->version
        );

        // Estilos específicos por página
        if (strpos($hook, 'reservas-config') !== false) {
            wp_enqueue_style(
                'reservas-config',
                plugin_dir_url(dirname(__FILE__)) . 'admin/css/reservas-config.css',
                array(),
                $this->version
            );
        }

        if (strpos($hook, 'reservas-bloqueos') !== false) {
            wp_enqueue_style(
                'reservas-bloqueos',
                plugin_dir_url(dirname(__FILE__)) . 'admin/css/reservas-bloqueos.css',
                array(),
                $this->version
            );
        }

        if (strpos($hook, 'reservas-calendario') !== false) {
            wp_enqueue_style(
                'reservas-calendario',
                plugin_dir_url(dirname(__FILE__)) . 'admin/css/reservas-calendario.css',
                array(),
                $this->version
            );
        }

        if (strpos($hook, 'reservas-instructions') !== false) {
            wp_enqueue_style(
                'reservas-instructions',
                plugin_dir_url(dirname(__FILE__)) . 'admin/css/reservas-instructions.css',
                array(),
                $this->version
            );
        }

        // jQuery UI
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

        wp_enqueue_script(
            'reservas-admin',
            plugin_dir_url(dirname(__FILE__)) . 'admin/js/reservas-admin.js',
            array('jquery', 'jquery-ui-datepicker'),
            $this->version,
            true
        );

        // Localizar script
        wp_localize_script('reservas-admin', 'reservas_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reservas_nonce'),
            'messages' => array(
                'confirm_delete' => '¿Estás seguro de que deseas eliminar este bloqueo?',
                'delete_error' => 'Error al eliminar el bloqueo'
            )
        ));
    }

    public function add_plugin_admin_menu() {
        // Menú principal
        add_menu_page(
            __('Reservas', 'reservas'),
            __('Reservas', 'reservas'),
            'manage_options',
            'reservas',
            array($this, 'render_admin_page'),
            'dashicons-calendar-alt',
            30
        );

        // Submenús
        add_submenu_page(
            'reservas',
            __('Reservas', 'reservas'),
            '<span class="dashicons dashicons-list-view" style="margin-right: 5px;"></span>' . __('Reservas', 'reservas'),
            'manage_options',
            'reservas',
            array($this, 'render_admin_page')
        );

        add_submenu_page(
            'reservas',
            __('Cabañas', 'reservas'),
            '<span class="dashicons dashicons-admin-home" style="margin-right: 5px;"></span>' . __('Cabañas', 'reservas'),
            'manage_options',
            'reservas-cabanas',
            array($this, 'render_cabanas_page')
        );

        add_submenu_page(
            'reservas',
            __('Bloqueos', 'reservas'),
            '<span class="dashicons dashicons-lock" style="margin-right: 5px;"></span>' . __('Bloqueos', 'reservas'),
            'manage_options',
            'reservas-bloqueos',
            array($this, 'render_bloqueos_page')
        );

        add_submenu_page(
            'reservas',
            __('Configuración', 'reservas'),
            '<span class="dashicons dashicons-admin-settings" style="margin-right: 5px;"></span>' . __('Configuración', 'reservas'),
            'manage_options',
            'reservas-config',
            array($this, 'render_config_page')
        );

        add_submenu_page(
            'reservas',
            __('Instrucciones', 'reservas'),
            '<span class="dashicons dashicons-info" style="margin-right: 5px;"></span>' . __('Instrucciones', 'reservas'),
            'manage_options',
            'reservas-instructions',
            array($this, 'render_instructions_page')
        );
    }

    public function register_settings() {
        register_setting('reservas_options', 'reservas_admin_email');
    }

    public function render_admin_page() {
        require_once plugin_dir_path(__FILE__) . 'views/admin-page.php';
    }

    public function render_cabanas_page() {
        require_once plugin_dir_path(__FILE__) . 'views/cabanas-page.php';
    }

    public function render_bloqueos_page() {
        if (isset($_GET['cabana_id'])) {
            require_once plugin_dir_path(__FILE__) . 'views/bloqueos-cabana-page.php';
        } else {
            require_once plugin_dir_path(__FILE__) . 'views/bloqueos-page.php';
        }
    }

    public function render_config_page() {
        require_once plugin_dir_path(__FILE__) . 'views/config-page.php';
    }

    public function render_instructions_page() {
        require_once plugin_dir_path(__FILE__) . 'views/instructions-page.php';
    }
} 