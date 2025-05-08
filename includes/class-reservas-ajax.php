<?php

class Reservas_Ajax {
    public function __construct() {
        add_action('wp_ajax_reservas_get_events', array($this, 'get_events'));
        add_action('wp_ajax_reservas_delete_bloqueo', array($this, 'delete_bloqueo'));
        add_action('wp_ajax_reservas_update_estado', array($this, 'update_estado'));
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
            "SELECT * FROM $table_bloqueos 
            WHERE cabana_id = %d 
            AND fecha_inicio <= %s 
            AND fecha_fin >= %s",
            $cabana_id,
            $end,
            $start
        ));

        foreach ($bloqueos as $bloqueo) {
            $events[] = array(
                'id' => 'bloqueo_' . $bloqueo->id,
                'title' => 'Bloqueado',
                'start' => $bloqueo->fecha_inicio,
                'end' => date('Y-m-d', strtotime($bloqueo->fecha_fin . ' +1 day')),
                'type' => 'blocked',
                'motivo' => $bloqueo->motivo,
                'color' => '#dc3545'
            );
        }

        // Obtener reservas
        $table_reservas = $wpdb->prefix . 'reservas';
        $reservas = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_reservas 
            WHERE cabana_id = %d 
            AND fecha_inicio <= %s 
            AND fecha_fin >= %s",
            $cabana_id,
            $end,
            $start
        ));

        foreach ($reservas as $reserva) {
            $events[] = array(
                'id' => 'reserva_' . $reserva->id,
                'title' => 'Reservado: ' . $reserva->nombre,
                'start' => $reserva->fecha_inicio,
                'end' => date('Y-m-d', strtotime($reserva->fecha_fin . ' +1 day')),
                'type' => 'reserved',
                'color' => '#28a745'
            );
        }

        wp_send_json_success($events);
    }

    public function delete_bloqueo() {
        check_ajax_referer('reservas_nonce', 'nonce');

        $bloqueo_id = intval($_POST['bloqueo_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas_bloqueos';

        $result = $wpdb->delete(
            $table_name,
            array('id' => $bloqueo_id),
            array('%d')
        );

        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public function update_estado() {
        check_ajax_referer('reservas_nonce', 'nonce');

        $cabana_id = intval($_POST['cabana_id']);
        $estado = sanitize_text_field($_POST['estado']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas_cabanas';

        $result = $wpdb->update(
            $table_name,
            array('estado' => $estado),
            array('id' => $cabana_id),
            array('%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
}

new Reservas_Ajax(); 