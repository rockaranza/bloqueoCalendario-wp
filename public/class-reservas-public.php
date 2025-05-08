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

    public function enqueue_styles() {
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css', array(), '1.12.1');
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/reservas-public.css', array(), $this->version);
        wp_enqueue_style($this->plugin_name . '-datepicker', plugin_dir_url(__FILE__) . 'css/datepicker.css', array(), $this->version);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/reservas-public.js', array('jquery', 'jquery-ui-datepicker'), $this->version, true);
        
        wp_localize_script($this->plugin_name, 'reservas_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reservas_nonce'),
            'events' => $this->get_events(),
            'messages' => array(
                'select_dates' => __('Por favor, seleccione las fechas de su reserva.', 'reservas'),
                'dates_blocked' => __('Las fechas seleccionadas están bloqueadas. Por favor, seleccione otras fechas.', 'reservas'),
                'request_sent' => __('Solicitud enviada correctamente. Nos pondremos en contacto con usted pronto.', 'reservas'),
                'request_error' => __('Error al enviar la solicitud. Por favor, inténtelo de nuevo.', 'reservas')
            )
        ));
    }

    private function get_events() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas_bloqueos';
        $events = array();

        // Obtener fechas bloqueadas
        $bloqueos = $wpdb->get_results("SELECT * FROM $table_name");
        foreach ($bloqueos as $bloqueo) {
            $events[] = array(
                'title' => $bloqueo->motivo,
                'start' => $bloqueo->fecha_inicio,
                'end' => $bloqueo->fecha_fin,
                'extendedProps' => array(
                    'type' => 'blocked'
                )
            );
        }

        return $events;
    }

    public function render_calendario($atts) {
        $this->enqueue_styles();
        $this->enqueue_scripts();
        
        ob_start();
        include plugin_dir_path(__FILE__) . 'views/calendario.php';
        return ob_get_clean();
    }
} 