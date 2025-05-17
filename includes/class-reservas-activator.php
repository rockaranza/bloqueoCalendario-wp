<?php
if (!defined('ABSPATH')) {
    exit;
}

class Reservas_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Crear tabla de cabañas
        $table_cabanas = $wpdb->prefix . 'reservas_cabanas';
        $sql_cabanas = "CREATE TABLE IF NOT EXISTS $table_cabanas (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            fecha_creacion datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Crear tabla de bloqueos de fechas
        $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
        $sql_bloqueos = "CREATE TABLE IF NOT EXISTS $table_bloqueos (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cabana_id mediumint(9) NOT NULL,
            fecha_inicio date NOT NULL,
            fecha_fin date NOT NULL,
            motivo varchar(255),
            fecha_creacion datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            FOREIGN KEY (cabana_id) REFERENCES $table_cabanas(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Crear tabla de reservas
        $table_reservas = $wpdb->prefix . 'reservas';
        $sql_reservas = "CREATE TABLE IF NOT EXISTS $table_reservas (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cabana_id mediumint(9) NOT NULL,
            nombre varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            telefono varchar(20),
            fecha_inicio date NOT NULL,
            fecha_fin date NOT NULL,
            estado varchar(20) NOT NULL DEFAULT 'pendiente',
            fecha_creacion datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            FOREIGN KEY (cabana_id) REFERENCES $table_cabanas(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_cabanas);
        dbDelta($sql_bloqueos);
        dbDelta($sql_reservas);

        // Agregar versión de la base de datos
        add_option('reservas_db_version', '1.0.0');
    }
} 