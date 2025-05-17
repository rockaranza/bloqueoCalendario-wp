<?php
if (!defined('ABSPATH')) {
    exit;
}

class Reservas_Public {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_scripts() {
        // FullCalendar
        wp_enqueue_style(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css',
            array(),
            '5.11.3'
        );

        wp_enqueue_script(
            'fullcalendar',
            'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js',
            array('jquery'),
            '5.11.3',
            true
        );

        // Estilos y scripts del plugin
        wp_enqueue_style(
            'reservas-public',
            plugin_dir_url(dirname(__FILE__)) . 'public/css/reservas-public.css',
            array(),
            $this->version
        );

        wp_enqueue_style(
            'reservas-modal',
            plugin_dir_url(dirname(__FILE__)) . 'public/css/reservas-modal.css',
            array(),
            $this->version
        );

        wp_enqueue_script(
            'reservas-public',
            plugin_dir_url(dirname(__FILE__)) . 'public/js/reservas-public.js',
            array('jquery', 'fullcalendar'),
            $this->version,
            true
        );

        // Localizar script
        wp_localize_script('reservas-public', 'reservas_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reservas_nonce')
        ));
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'reservas-public',
            plugin_dir_url(dirname(__FILE__)) . 'public/css/reservas-public.css',
            array(),
            $this->version
        );
    }

    public function render_calendario($atts) {
        // Asegurarse de que los scripts y estilos estén cargados
        $this->enqueue_scripts();
        $this->enqueue_styles();

        // Obtener el ID de la cabaña
        $cabana_id = isset($atts['cabana_id']) ? intval($atts['cabana_id']) : 0;

        if (!$cabana_id) {
            return '<p>' . __('ID de cabaña no válido', 'reservas') . '</p>';
        }

        // Obtener información de la cabaña
        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas_cabanas';
        $cabana = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $cabana_id));

        if (!$cabana) {
            return '<p>' . __('Cabaña no encontrada', 'reservas') . '</p>';
        }

        ob_start();
        require_once plugin_dir_path(__FILE__) . 'views/calendario.php';
        return ob_get_clean();
    }

    public function render_formulario($atts) {
        // Asegurarse de que los scripts y estilos estén cargados
        $this->enqueue_scripts();
        $this->enqueue_styles();

        // Obtener el ID de la cabaña
        $cabana_id = isset($atts['cabana_id']) ? intval($atts['cabana_id']) : 0;

        if (!$cabana_id) {
            return '<p>' . __('ID de cabaña no válido', 'reservas') . '</p>';
        }

        // Obtener información de la cabaña
        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas_cabanas';
        $cabana = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $cabana_id));

        if (!$cabana) {
            return '<p>' . __('Cabaña no encontrada', 'reservas') . '</p>';
        }

        ob_start();
        require_once plugin_dir_path(__FILE__) . 'views/formulario-modal.php';
        return ob_get_clean();
    }
} 