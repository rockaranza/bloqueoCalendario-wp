<?php
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'reservas'));
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="reservas-admin-tabs">
        <a href="<?php echo admin_url('admin.php?page=reservas'); ?>" class="nav-tab">
            <?php _e('Reservas', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-config'); ?>" class="nav-tab">
            <?php _e('Configuración', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-instructions'); ?>" class="nav-tab nav-tab-active">
            <?php _e('Instrucciones', 'reservas'); ?>
        </a>
    </div>
    
    <div class="reservas-admin-content">
        <div class="reservas-admin-section">
            <h2><?php _e('Instrucciones de Uso', 'reservas'); ?></h2>
            
            <h3><?php _e('1. Configuración Inicial', 'reservas'); ?></h3>
            <ol>
                <li>
                    <?php _e('Ve a la pestaña "Configuración" y configura el email de notificaciones.', 'reservas'); ?>
                </li>
                <li>
                    <?php _e('Si deseas sincronizar con Google Calendar, configura las credenciales de la API.', 'reservas'); ?>
                </li>
            </ol>
            
            <h3><?php _e('2. Gestionar Cabañas', 'reservas'); ?></h3>
            <ol>
                <li>
                    <?php _e('En la pestaña "Reservas", encontrarás la sección "Gestionar Cabañas".', 'reservas'); ?>
                </li>
                <li>
                    <?php _e('Para cada cabaña, puedes:', 'reservas'); ?>
                    <ul>
                        <li><?php _e('Agregar bloqueos de fechas', 'reservas'); ?></li>
                        <li><?php _e('Ver los bloqueos existentes', 'reservas'); ?></li>
                        <li><?php _e('Eliminar bloqueos', 'reservas'); ?></li>
                    </ul>
                </li>
            </ol>
            
            <h3><?php _e('3. Gestionar Reservas', 'reservas'); ?></h3>
            <ol>
                <li>
                    <?php _e('En la pestaña "Reservas", encontrarás la sección "Reservas Pendientes".', 'reservas'); ?>
                </li>
                <li>
                    <?php _e('Para cada reserva, puedes:', 'reservas'); ?>
                    <ul>
                        <li><?php _e('Confirmar la reserva', 'reservas'); ?></li>
                        <li><?php _e('Rechazar la reserva', 'reservas'); ?></li>
                    </ul>
                </li>
                <li>
                    <?php _e('Cuando confirmas una reserva:', 'reservas'); ?>
                    <ul>
                        <li><?php _e('Se envía un email de confirmación al cliente', 'reservas'); ?></li>
                        <li><?php _e('Se bloquean las fechas en el calendario', 'reservas'); ?></li>
                        <li><?php _e('Si está configurado, se agrega al calendario de Google', 'reservas'); ?></li>
                    </ul>
                </li>
            </ol>
            
            <h3><?php _e('4. Shortcodes Disponibles', 'reservas'); ?></h3>
            <p><?php _e('Puedes usar los siguientes shortcodes en tus páginas o posts:', 'reservas'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Shortcode', 'reservas'); ?></th>
                        <th><?php _e('Descripción', 'reservas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[reservas_calendario cabana_id="1"]</code></td>
                        <td>
                            <?php _e('Muestra el calendario de reservas para una cabaña específica.', 'reservas'); ?>
                            <br>
                            <small><?php _e('Reemplaza "1" con el ID de la cabaña.', 'reservas'); ?></small>
                        </td>
                    </tr>
                    <tr>
                        <td><code>[reservas_formulario cabana_id="1"]</code></td>
                        <td>
                            <?php _e('Muestra el formulario de reserva para una cabaña específica.', 'reservas'); ?>
                            <br>
                            <small><?php _e('Reemplaza "1" con el ID de la cabaña.', 'reservas'); ?></small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="reservas-admin-section">
            <h2><?php _e('Preguntas Frecuentes', 'reservas'); ?></h2>
            
            <div class="reservas-faq">
                <h4><?php _e('¿Cómo puedo cambiar el diseño del calendario?', 'reservas'); ?></h4>
                <p>
                    <?php _e('Puedes personalizar el diseño del calendario agregando CSS personalizado en tu tema o usando un plugin de personalización de CSS.', 'reservas'); ?>
                </p>
            </div>
            
            <div class="reservas-faq">
                <h4><?php _e('¿Cómo puedo recibir notificaciones de nuevas reservas?', 'reservas'); ?></h4>
                <p>
                    <?php _e('Configura el email de notificaciones en la pestaña "Configuración". Recibirás un email cada vez que se realice una nueva solicitud de reserva.', 'reservas'); ?>
                </p>
            </div>
            
            <div class="reservas-faq">
                <h4><?php _e('¿Cómo funciona la sincronización con Google Calendar?', 'reservas'); ?></h4>
                <p>
                    <?php _e('Cuando confirmas una reserva, se crea automáticamente un evento en el calendario de Google configurado. Esto te permite tener todas tus reservas sincronizadas en un solo lugar.', 'reservas'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.reservas-admin-tabs {
    margin-bottom: 20px;
}

.reservas-admin-tabs .nav-tab {
    margin-right: 10px;
}

.reservas-admin-tabs .nav-tab-active {
    background: #fff;
    border-bottom: 1px solid #fff;
}

.reservas-admin-content {
    background: #fff;
    padding: 20px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.reservas-admin-section {
    margin-bottom: 30px;
}

.reservas-admin-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.reservas-admin-section h3 {
    margin: 20px 0 10px;
}

.reservas-admin-section ol {
    margin-left: 20px;
}

.reservas-admin-section li {
    margin-bottom: 10px;
}

.reservas-admin-section ul {
    margin-left: 20px;
    margin-top: 5px;
}

.reservas-faq {
    margin-bottom: 20px;
}

.reservas-faq h4 {
    margin: 0 0 10px;
    color: #23282d;
}

.reservas-faq p {
    margin: 0;
    color: #666;
}

code {
    background: #f8f9fa;
    padding: 3px 5px;
    border-radius: 3px;
    font-size: 13px;
}
</style> 