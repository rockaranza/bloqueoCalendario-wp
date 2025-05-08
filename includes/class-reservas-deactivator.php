<?php

class Reservas_Deactivator {
    public static function deactivate() {
        // Limpiar la programación de eventos
        wp_clear_scheduled_hook('reservas_daily_cleanup');

        // No eliminamos las tablas ni los datos para preservar la información
        // Si se desea eliminar todo, se debe crear una función de desinstalación
    }
} 