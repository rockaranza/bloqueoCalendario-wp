<?php
if (!defined('ABSPATH')) {
    exit;
}

class Reservas {
    private static $instance = null;
    private $version;
    private $plugin_name;
    private $ajax;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->version = '1.0.0';
        $this->plugin_name = 'reservas';
        
        $this->load_dependencies();
        $this->init();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reservas-activator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reservas-deactivator.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-reservas-ajax.php';
    }

    private function init() {
        $this->register_hooks();
        $this->init_ajax();
    }

    private function register_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('reservas_calendario', array($this, 'render_calendario_shortcode'));
        add_action('wp_ajax_reservas_get_events', array($this, 'get_events'));
        add_action('wp_ajax_nopriv_reservas_get_events', array($this, 'get_events'));
        add_action('wp_ajax_reservas_submit_request', array($this, 'submit_request'));
        add_action('wp_ajax_nopriv_reservas_submit_request', array($this, 'submit_request'));
    }

    private function init_ajax() {
        $this->ajax = new Reservas_Ajax();
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Reservas', 'reservas'),
            __('Reservas', 'reservas'),
            'manage_options',
            'reservas',
            array($this, 'render_admin_page'),
            'dashicons-calendar-alt',
            30
        );

        add_submenu_page(
            'reservas',
            __('Configuración', 'reservas'),
            __('Configuración', 'reservas'),
            'manage_options',
            'reservas-config',
            array($this, 'render_config_page')
        );

        add_submenu_page(
            'reservas',
            __('Instrucciones', 'reservas'),
            __('Instrucciones', 'reservas'),
            'manage_options',
            'reservas-instructions',
            array($this, 'render_instructions_page')
        );
    }

    public function render_admin_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/admin-page.php';
    }

    public function render_config_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/config-page.php';
    }

    public function render_instructions_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/views/instructions-page.php';
    }

    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'reservas') === false) {
            return;
        }

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

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/reservas-admin.css',
            array(),
            $this->version
        );

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/reservas-admin.js',
            array('jquery', 'fullcalendar'),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name . '-admin',
            'reservas_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('reservas_nonce')
            )
        );
    }

    public function enqueue_scripts() {
        // Cargar los scripts y estilos siempre que se use el shortcode
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

        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/reservas.css',
            array(),
            $this->version
        );

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/reservas.js',
            array('jquery', 'fullcalendar'),
            $this->version,
            true
        );

        wp_localize_script(
            $this->plugin_name,
            'reservas_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('reservas_nonce')
            )
        );
    }

    public function render_calendario_shortcode($atts) {
        // Asegurarse de que los scripts y estilos estén cargados
        $this->enqueue_scripts();
        
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

        // Obtener eventos (bloqueos y reservas)
        $events = $this->get_events_for_calendar($cabana_id);
        
        // Pasar los eventos al JavaScript
        wp_localize_script(
            $this->plugin_name,
            'reservas_events',
            $events
        );
        
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'public/views/calendario.php';
        $output = ob_get_clean();
        
        return $output;
    }

    private function get_events_for_calendar($cabana_id) {
        global $wpdb;
        $events = array();

        // Obtener bloqueos
        $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
        $bloqueos = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_bloqueos WHERE cabana_id = %d",
            $cabana_id
        ));

        foreach ($bloqueos as $bloqueo) {
            $events[] = array(
                'id' => 'bloqueo_' . $bloqueo->id,
                'title' => $bloqueo->motivo ?: __('Fecha bloqueada', 'reservas'),
                'start' => $bloqueo->fecha_inicio,
                'end' => $bloqueo->fecha_fin,
                'type' => 'blocked',
                'color' => '#dc3545'
            );
        }

        // Obtener reservas
        $table_reservas = $wpdb->prefix . 'reservas';
        $reservas = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_reservas WHERE cabana_id = %d",
            $cabana_id
        ));

        foreach ($reservas as $reserva) {
            $events[] = array(
                'id' => 'reserva_' . $reserva->id,
                'title' => __('Reservado', 'reservas'),
                'start' => $reserva->fecha_inicio,
                'end' => $reserva->fecha_fin,
                'type' => 'reserved',
                'color' => '#28a745'
            );
        }

        return $events;
    }

    public function get_events() {
        check_ajax_referer('reservas_nonce', 'nonce');

        $cabana_id = intval($_POST['cabana_id']);
        $start = sanitize_text_field($_POST['start']);
        $end = sanitize_text_field($_POST['end']);

        global $wpdb;
        $events = array();

        // Obtener bloqueos
        $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
        $bloqueos = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_bloqueos WHERE cabana_id = %d AND fecha_inicio <= %s AND fecha_fin >= %s",
            $cabana_id,
            $end,
            $start
        ));

        foreach ($bloqueos as $bloqueo) {
            $events[] = array(
                'id' => 'bloqueo_' . $bloqueo->id,
                'title' => $bloqueo->motivo ?: __('Fecha bloqueada', 'reservas'),
                'start' => $bloqueo->fecha_inicio,
                'end' => $bloqueo->fecha_fin,
                'type' => 'blocked',
                'color' => '#dc3545'
            );
        }

        // Obtener reservas
        $table_reservas = $wpdb->prefix . 'reservas';
        $reservas = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_reservas WHERE cabana_id = %d AND fecha_inicio <= %s AND fecha_fin >= %s",
            $cabana_id,
            $end,
            $start
        ));

        foreach ($reservas as $reserva) {
            $events[] = array(
                'id' => 'reserva_' . $reserva->id,
                'title' => __('Reservado', 'reservas'),
                'start' => $reserva->fecha_inicio,
                'end' => $reserva->fecha_fin,
                'type' => 'reserved',
                'color' => '#28a745'
            );
        }

        wp_send_json_success($events);
    }

    public function submit_request() {
        check_ajax_referer('reservas_nonce', 'nonce');

        $cabana_id = intval($_POST['cabana_id']);
        $nombre = sanitize_text_field($_POST['nombre']);
        $email = sanitize_email($_POST['email']);
        $telefono = sanitize_text_field($_POST['telefono']);
        $fecha_inicio = sanitize_text_field($_POST['fecha_inicio']);
        $fecha_fin = sanitize_text_field($_POST['fecha_fin']);
        $comentarios = sanitize_textarea_field($_POST['comentarios']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas';

        $result = $wpdb->insert(
            $table_name,
            array(
                'cabana_id' => $cabana_id,
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'comentarios' => $comentarios,
                'estado' => 'pendiente'
            )
        );

        if ($result) {
            // Obtener correo del administrador
            $table_config = $wpdb->prefix . 'reservas_config';
            $config = $wpdb->get_row("SELECT admin_email FROM $table_config WHERE id = 1");
            
            if ($config) {
                $admin_email = $config->admin_email;
                
                // Obtener información de la cabaña
                $table_cabanas = $wpdb->prefix . 'reservas_cabanas';
                $cabana = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_cabanas WHERE id = %d", $cabana_id));
                
                if ($cabana) {
                    $subject = sprintf(__('Nueva solicitud de reserva - %s', 'reservas'), $cabana->nombre);
                    
                    $message = sprintf(
                        __("Se ha recibido una nueva solicitud de reserva:\n\nCabaña: %s\nCliente: %s\nEmail: %s\nTeléfono: %s\nFecha de inicio: %s\nFecha de fin: %s\nComentarios: %s\n\nPor favor, revisa la solicitud en el panel de administración.", 'reservas'),
                        $cabana->nombre,
                        $nombre,
                        $email,
                        $telefono,
                        $fecha_inicio,
                        $fecha_fin,
                        $comentarios
                    );
                    
                    wp_mail($admin_email, $subject, $message);
                }
            }
            
            wp_send_json_success(__('Solicitud enviada correctamente.', 'reservas'));
        } else {
            wp_send_json_error(__('Error al enviar la solicitud.', 'reservas'));
        }
    }
} 