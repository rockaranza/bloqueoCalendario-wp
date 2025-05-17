<?php
// Si WordPress no llama este archivo, salir
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Eliminar las tablas de la base de datos
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reservas");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reservas_bloqueos");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}reservas_cabanas");

// Eliminar las opciones
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'reservas_%'"); 