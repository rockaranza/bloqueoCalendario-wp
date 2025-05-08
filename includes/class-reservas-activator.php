<?php

class Reservas_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Crear tabla de cabañas
        $table_cabanas = $wpdb->prefix . 'reservas_cabanas';
        $sql_cabanas = "CREATE TABLE IF NOT EXISTS $table_cabanas (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            descripcion text,
            capacidad int NOT NULL,
            precio decimal(10,2) NOT NULL,
            imagen varchar(255),
            estado varchar(20) NOT NULL DEFAULT 'activo',
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

        // Crear tabla de reservas (modificada para incluir cabaña)
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

        // Crear tabla de configuración
        $table_config = $wpdb->prefix . 'reservas_config';
        $sql_config = "CREATE TABLE IF NOT EXISTS $table_config (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            admin_email varchar(100) NOT NULL,
            fecha_actualizacion datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_cabanas);
        dbDelta($sql_bloqueos);
        dbDelta($sql_reservas);
        dbDelta($sql_config);

        // Insertar configuración inicial
        $wpdb->insert(
            $table_config,
            array('admin_email' => get_option('admin_email'))
        );

        // Agregar versión de la base de datos
        add_option('reservas_db_version', '1.0.0');

        // Crear página de administración
        self::create_admin_page();
    }

    private static function create_admin_page() {
        $admin_page = array(
            'post_title'    => 'Reservas',
            'post_content'  => '[reservas_admin]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );

        // Insertar la página
        $page_id = wp_insert_post($admin_page);

        // Guardar el ID de la página en las opciones
        if ($page_id) {
            update_option('reservas_admin_page_id', $page_id);
        }
    }
} 