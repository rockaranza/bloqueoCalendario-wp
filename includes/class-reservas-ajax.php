<?php
/**
 * Clase para manejar las peticiones AJAX
 *
 * @package Reservas
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Reservas_Ajax {
    /**
     * Constructor de la clase
     */
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
        add_action('wp_ajax_reservas_delete_cabana', array($this, 'handle_delete_cabana'));
        add_action('wp_ajax_reservas_confirm_reserva', array($this, 'handle_confirm_reserva'));
        add_action('wp_ajax_reservas_reject_reserva', array($this, 'handle_reject_reserva'));
    }

    /**
     * Verifica la disponibilidad de fechas
     */
    public function verificar_fechas() {
        check_ajax_referer('reservas_nonce', 'nonce');

        $cabana_id = filter_input(INPUT_POST, 'cabana_id', FILTER_VALIDATE_INT);
        $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING);
        $fecha_fin = filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_STRING);

        if (!$cabana_id || !$fecha_inicio || !$fecha_fin) {
            wp_send_json_error(array(
                'mensaje' => __('Datos inválidos.', 'reservas')
            ));
        }

        global $wpdb;
        $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
        $table_reservas = $wpdb->prefix . 'reservas';

        // Verificar bloqueos
        $bloqueos = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM %i 
            WHERE cabana_id = %d 
            AND (
                (fecha_inicio <= %s AND fecha_fin > %s)
                OR (fecha_inicio < %s AND fecha_fin >= %s)
                OR (fecha_inicio >= %s AND fecha_inicio < %s)
            )",
            $table_bloqueos,
            $cabana_id,
            $fecha_inicio, $fecha_inicio,
            $fecha_fin, $fecha_fin,
            $fecha_inicio, $fecha_fin
        ));

        // Verificar solo reservas confirmadas
        $reservas = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM %i 
            WHERE cabana_id = %d 
            AND estado = %s
            AND (
                (fecha_inicio <= %s AND fecha_fin > %s)
                OR (fecha_inicio < %s AND fecha_fin >= %s)
                OR (fecha_inicio >= %s AND fecha_inicio < %s)
            )",
            $table_reservas,
            $cabana_id,
            'confirmada',
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

    /**
     * Procesa el envío de una nueva reserva
     */
    public function enviar_reserva() {
        check_ajax_referer('reservas_nonce', 'nonce');

        parse_str(filter_input(INPUT_POST, 'formData', FILTER_SANITIZE_STRING), $form_data);

        $cabana_id = filter_var($form_data['cabana_id'], FILTER_VALIDATE_INT);
        $nombre = sanitize_text_field($form_data['nombre']);
        $email = sanitize_email($form_data['email']);
        $telefono = sanitize_text_field($form_data['telefono']);
        $fecha_inicio = sanitize_text_field($form_data['fecha_inicio']);
        $fecha_fin = sanitize_text_field($form_data['fecha_fin']);

        if (!$cabana_id || !$nombre || !$email || !$telefono || !$fecha_inicio || !$fecha_fin) {
            wp_send_json_error(array(
                'mensaje' => __('Todos los campos son obligatorios.', 'reservas')
            ));
        }

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
                'estado' => 'pendiente',
                'fecha_creacion' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($resultado) {
            // Enviar email de notificación al administrador
            $admin_email = get_option('reservas_admin_email', get_option('admin_email'));
            $cabana = $wpdb->get_row($wpdb->prepare(
                "SELECT nombre FROM %i WHERE id = %d",
                $wpdb->prefix . 'reservas_cabanas',
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

    /**
     * Obtiene los eventos para el calendario
     */
    public function obtener_eventos() {
        check_ajax_referer('reservas_nonce', 'nonce');

        $cabana_id = filter_input(INPUT_POST, 'cabana_id', FILTER_VALIDATE_INT);
        $start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_STRING);
        $end = filter_input(INPUT_POST, 'end', FILTER_SANITIZE_STRING);

        if (!$cabana_id || !$start || !$end) {
            wp_send_json_error(array(
                'mensaje' => __('Datos inválidos.', 'reservas')
            ));
        }

        global $wpdb;
        $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
        $table_reservas = $wpdb->prefix . 'reservas';

        $eventos = array();

        // Obtener bloqueos
        $bloqueos = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM %i 
            WHERE cabana_id = %d 
            AND fecha_inicio >= %s 
            AND fecha_fin <= %s",
            $table_bloqueos,
            $cabana_id,
            $start,
            $end
        ));

        foreach ($bloqueos as $bloqueo) {
            $eventos[] = array(
                'title' => esc_html($bloqueo->motivo),
                'start' => $bloqueo->fecha_inicio,
                'end' => $bloqueo->fecha_fin,
                'className' => 'blocked',
                'display' => 'background'
            );
        }

        // Obtener solo reservas confirmadas
        $reservas = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM %i 
            WHERE cabana_id = %d 
            AND estado = %s
            AND fecha_inicio >= %s 
            AND fecha_fin <= %s",
            $table_reservas,
            $cabana_id,
            'confirmada',
            $start,
            $end
        ));

        foreach ($reservas as $reserva) {
            $eventos[] = array(
                'title' => sprintf(__('Reservado por %s', 'reservas'), esc_html($reserva->nombre)),
                'start' => $reserva->fecha_inicio,
                'end' => $reserva->fecha_fin,
                'className' => 'reserved',
                'display' => 'background',
                'color' => '#dc3545'
            );
        }

        wp_send_json_success($eventos);
    }

    /**
     * Actualiza el estado de una reserva
     */
    public function handle_update_status() {
        check_ajax_referer('reservas_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No tienes permisos suficientes para realizar esta acción.', 'reservas')
            ));
        }

        $reserva_id = filter_input(INPUT_POST, 'reserva_id', FILTER_VALIDATE_INT);
        $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);

        if (!$reserva_id || !in_array($estado, array('confirmada', 'rechazada'))) {
            wp_send_json_error(array(
                'message' => __('Datos inválidos.', 'reservas')
            ));
        }

        global $wpdb;
        $table_reservas = $wpdb->prefix . 'reservas';

        // Obtener información de la reserva
        $reserva = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM %i WHERE id = %d",
            $table_reservas,
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
                "SELECT nombre FROM %i WHERE id = %d",
                $wpdb->prefix . 'reservas_cabanas',
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

    /**
     * Elimina una cabaña y sus datos relacionados
     */
    public function handle_delete_cabana() {
        check_ajax_referer('reservas_delete_cabana', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No tienes permisos suficientes para realizar esta acción.', 'reservas')
            ));
        }

        $cabana_id = filter_input(INPUT_POST, 'cabana_id', FILTER_VALIDATE_INT);

        if (!$cabana_id) {
            wp_send_json_error(array(
                'message' => __('ID de cabaña inválido.', 'reservas')
            ));
        }

        global $wpdb;
        $table_cabanas = $wpdb->prefix . 'reservas_cabanas';
        $table_reservas = $wpdb->prefix . 'reservas';
        $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';

        // Primero eliminar todas las reservas y bloqueos asociados
        $wpdb->delete($table_reservas, array('cabana_id' => $cabana_id), array('%d'));
        $wpdb->delete($table_bloqueos, array('cabana_id' => $cabana_id), array('%d'));

        // Luego eliminar la cabaña
        $resultado = $wpdb->delete($table_cabanas, array('id' => $cabana_id), array('%d'));

        if ($resultado === false) {
            wp_send_json_error(array(
                'message' => __('Error al eliminar la cabaña.', 'reservas')
            ));
        }

        wp_send_json_success(array(
            'message' => __('Cabaña eliminada correctamente.', 'reservas')
        ));
    }

    /**
     * Maneja la confirmación de una reserva
     */
    public function handle_confirm_reserva() {
        check_ajax_referer('reservas_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No tienes permisos suficientes para realizar esta acción.', 'reservas')
            ));
        }

        $reserva_id = filter_input(INPUT_POST, 'reserva_id', FILTER_VALIDATE_INT);

        if (!$reserva_id) {
            wp_send_json_error(array(
                'message' => __('ID de reserva inválido.', 'reservas')
            ));
        }

        global $wpdb;
        $table_reservas = $wpdb->prefix . 'reservas';

        // Obtener información de la reserva
        $reserva = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM %i WHERE id = %d",
            $table_reservas,
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
            array('estado' => 'confirmada'),
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
        $asunto = __('Tu reserva ha sido confirmada', 'reservas');
        $mensaje = sprintf(
            __("Hola %s,\n\nTu reserva ha sido confirmada.\n\nDetalles de la reserva:\nCabaña: %s\nFechas: %s a %s\n\nGracias por tu interés.", 'reservas'),
            $reserva->nombre,
            $wpdb->get_var($wpdb->prepare(
                "SELECT nombre FROM %i WHERE id = %d",
                $wpdb->prefix . 'reservas_cabanas',
                $reserva->cabana_id
            )),
            $reserva->fecha_inicio,
            $reserva->fecha_fin
        );

        wp_mail($reserva->email, $asunto, $mensaje);

        wp_send_json_success(array(
            'message' => __('Reserva confirmada correctamente.', 'reservas')
        ));
    }

    /**
     * Maneja el rechazo de una reserva
     */
    public function handle_reject_reserva() {
        check_ajax_referer('reservas_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('No tienes permisos suficientes para realizar esta acción.', 'reservas')
            ));
        }

        $reserva_id = filter_input(INPUT_POST, 'reserva_id', FILTER_VALIDATE_INT);

        if (!$reserva_id) {
            wp_send_json_error(array(
                'message' => __('ID de reserva inválido.', 'reservas')
            ));
        }

        global $wpdb;
        $table_reservas = $wpdb->prefix . 'reservas';

        // Obtener información de la reserva
        $reserva = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM %i WHERE id = %d",
            $table_reservas,
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
            array('estado' => 'rechazada'),
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
        $asunto = __('Tu reserva ha sido rechazada', 'reservas');
        $mensaje = sprintf(
            __("Hola %s,\n\nTu reserva ha sido rechazada.\n\nDetalles de la reserva:\nCabaña: %s\nFechas: %s a %s\n\nGracias por tu interés.", 'reservas'),
            $reserva->nombre,
            $wpdb->get_var($wpdb->prepare(
                "SELECT nombre FROM %i WHERE id = %d",
                $wpdb->prefix . 'reservas_cabanas',
                $reserva->cabana_id
            )),
            $reserva->fecha_inicio,
            $reserva->fecha_fin
        );

        wp_mail($reserva->email, $asunto, $mensaje);

        wp_send_json_success(array(
            'message' => __('Reserva rechazada correctamente.', 'reservas')
        ));
    }
}

new Reservas_Ajax(); 