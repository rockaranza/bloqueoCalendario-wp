<?php
if (!defined('ABSPATH')) {
    exit;
}

class Reservas_Ajax {
    public function __construct() {
        // Acciones para usuarios no autenticados
        add_action('wp_ajax_nopriv_verificar_fechas', array($this, 'verificar_fechas'));
        add_action('wp_ajax_nopriv_enviar_reserva', array($this, 'enviar_reserva'));
        add_action('wp_ajax_nopriv_obtener_eventos', array($this, 'obtener_eventos'));

        // Acciones para usuarios autenticados
        add_action('wp_ajax_verificar_fechas', array($this, 'verificar_fechas'));
        add_action('wp_ajax_enviar_reserva', array($this, 'enviar_reserva'));
        add_action('wp_ajax_obtener_eventos', array($this, 'obtener_eventos'));
        add_action('wp_ajax_reservas_update_status', array($this, 'handle_update_status'));
    }

    public function verificar_fechas() {
        check_ajax_referer('reservas_nonce', 'nonce');

        $cabana_id = intval($_POST['cabana_id']);
        $fecha_inicio = sanitize_text_field($_POST['fecha_inicio']);
        $fecha_fin = sanitize_text_field($_POST['fecha_fin']);

        global $wpdb;
        $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
        $table_reservas = $wpdb->prefix . 'reservas';

        // Verificar bloqueos
        $bloqueos = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_bloqueos 
            WHERE cabana_id = %d 
            AND (
                (fecha_inicio <= %s AND fecha_fin > %s)
                OR (fecha_inicio < %s AND fecha_fin >= %s)
                OR (fecha_inicio >= %s AND fecha_inicio < %s)
            )",
            $cabana_id,
            $fecha_inicio, $fecha_inicio,
            $fecha_fin, $fecha_fin,
            $fecha_inicio, $fecha_fin
        ));

        // Verificar solo reservas confirmadas
        $reservas = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_reservas 
            WHERE cabana_id = %d 
            AND estado = 'confirmada'
            AND (
                (fecha_inicio <= %s AND fecha_fin > %s)
                OR (fecha_inicio < %s AND fecha_fin >= %s)
                OR (fecha_inicio >= %s AND fecha_inicio < %s)
            )",
            $cabana_id,
            $fecha_inicio, $fecha_inicio,
            $fecha_fin, $fecha_fin,
            $fecha_inicio, $fecha_fin
        ));

        if ($bloqueos > 0 || $reservas > 0) {
            wp_send_json_error(array(
                'mensaje' => __('Las fechas seleccionadas no están disponibles.', 'reservas')
            ));
        }

        wp_send_json_success();
    }

    public function enviar_reserva() {
        check_ajax_referer('reservas_nonce', 'nonce');

        parse_str($_POST['formData'], $form_data);

        $cabana_id = intval($form_data['cabana_id']);
        $nombre = sanitize_text_field($form_data['nombre']);
        $email = sanitize_email($form_data['email']);
        $telefono = sanitize_text_field($form_data['telefono']);
        $fecha_inicio = sanitize_text_field($form_data['fecha_inicio']);
        $fecha_fin = sanitize_text_field($form_data['fecha_fin']);

        global $wpdb;
        $table_reservas = $wpdb->prefix . 'reservas';

        $resultado = $wpdb->insert(
            $table_reservas,
            array(
                'cabana_id' => $cabana_id,
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => $telefono,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => 'pendiente'
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($resultado) {
            // Enviar email de notificación al administrador
            $admin_email = get_option('admin_email');
            $cabana = $wpdb->get_row($wpdb->prepare(
                "SELECT nombre FROM {$wpdb->prefix}reservas_cabanas WHERE id = %d",
                $cabana_id
            ));

            $asunto = sprintf(__('Nueva reserva para %s', 'reservas'), $cabana->nombre);
            $mensaje = sprintf(
                __("Nueva reserva recibida:\n\nCabaña: %s\nNombre: %s\nEmail: %s\nTeléfono: %s\nFechas: %s a %s", 'reservas'),
                $cabana->nombre,
                $nombre,
                $email,
                $telefono,
                $fecha_inicio,
                $fecha_fin
            );

            wp_mail($admin_email, $asunto, $mensaje);

            wp_send_json_success(array(
                'mensaje' => __('Reserva enviada correctamente. Nos pondremos en contacto contigo pronto.', 'reservas')
            ));
        } else {
            wp_send_json_error(array(
                'mensaje' => __('Error al procesar la reserva. Por favor, inténtalo de nuevo.', 'reservas')
            ));
        }
    }

    public function obtener_eventos() {
        check_ajax_referer('reservas_nonce', 'nonce');

        $cabana_id = intval($_POST['cabana_id']);
        $start = sanitize_text_field($_POST['start']);
        $end = sanitize_text_field($_POST['end']);

        global $wpdb;
        $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
        $table_reservas = $wpdb->prefix . 'reservas';

        $eventos = array();

        // Obtener bloqueos
        $bloqueos = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_bloqueos 
            WHERE cabana_id = %d 
            AND fecha_inicio >= %s 
            AND fecha_fin <= %s",
            $cabana_id,
            $start,
            $end
        ));

        foreach ($bloqueos as $bloqueo) {
            $eventos[] = array(
                'title' => $bloqueo->motivo,
                'start' => $bloqueo->fecha_inicio,
                'end' => $bloqueo->fecha_fin,
                'className' => 'blocked',
                'display' => 'background'
            );
        }

        // Obtener solo reservas confirmadas
        $reservas = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_reservas 
            WHERE cabana_id = %d 
            AND estado = 'confirmada'
            AND fecha_inicio >= %s 
            AND fecha_fin <= %s",
            $cabana_id,
            $start,
            $end
        ));

        foreach ($reservas as $reserva) {
            // No incluimos el último día en el bloqueo visual
            $eventos[] = array(
                'title' => sprintf(__('Reservado por %s', 'reservas'), $reserva->nombre),
                'start' => $reserva->fecha_inicio,
                'end' => $reserva->fecha_fin, // Ya no sumamos un día
                'className' => 'reserved',
                'display' => 'background',
                'color' => '#dc3545' // Color rojo para reservas confirmadas
            );
        }

        wp_send_json_success($eventos);
    }

    public function handle_update_status() {
        check_ajax_referer('reservas_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No tienes permisos suficientes para realizar esta acción.', 'reservas')
            ));
        }

        $reserva_id = intval($_POST['reserva_id']);
        $estado = sanitize_text_field($_POST['estado']);

        if (!in_array($estado, array('confirmada', 'rechazada'))) {
            wp_send_json_error(array(
                'message' => __('Estado no válido.', 'reservas')
            ));
        }

        global $wpdb;
        $table_reservas = $wpdb->prefix . 'reservas';

        // Obtener información de la reserva
        $reserva = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_reservas WHERE id = %d",
            $reserva_id
        ));

        if (!$reserva) {
            wp_send_json_error(array(
                'message' => __('Reserva no encontrada.', 'reservas')
            ));
        }

        // Actualizar el estado
        $resultado = $wpdb->update(
            $table_reservas,
            array('estado' => $estado),
            array('id' => $reserva_id),
            array('%s'),
            array('%d')
        );

        if ($resultado === false) {
            wp_send_json_error(array(
                'message' => __('Error al actualizar el estado de la reserva.', 'reservas')
            ));
        }

        // Enviar email de notificación
        $asunto = sprintf(
            __('Tu reserva ha sido %s', 'reservas'),
            $estado === 'confirmada' ? __('confirmada', 'reservas') : __('rechazada', 'reservas')
        );

        $mensaje = sprintf(
            __("Hola %s,\n\nTu reserva ha sido %s.\n\nDetalles de la reserva:\nCabaña: %s\nFechas: %s a %s\n\nGracias por tu interés.", 'reservas'),
            $reserva->nombre,
            $estado === 'confirmada' ? __('confirmada', 'reservas') : __('rechazada', 'reservas'),
            $wpdb->get_var($wpdb->prepare(
                "SELECT nombre FROM {$wpdb->prefix}reservas_cabanas WHERE id = %d",
                $reserva->cabana_id
            )),
            $reserva->fecha_inicio,
            $reserva->fecha_fin
        );

        wp_mail($reserva->email, $asunto, $mensaje);

        wp_send_json_success(array(
            'message' => __('Estado actualizado correctamente.', 'reservas')
        ));
    }
}

new Reservas_Ajax(); 