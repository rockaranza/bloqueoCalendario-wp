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
    
    <div class="reservas-admin-content">
        <div class="reservas-admin-section">
            <h2><?php _e('Instrucciones de Uso', 'reservas'); ?></h2>
            
            <h3><?php _e('1. Configuración Inicial', 'reservas'); ?></h3>
            <ol>
                <li>
                    <?php _e('Ve al menú "Reservas > Configuración" y configura el email de notificaciones.', 'reservas'); ?>
                </li>
            </ol>
            
            <h3><?php _e('2. Gestionar Cabañas', 'reservas'); ?></h3>
            <ol>
                <li>
                    <?php _e('Ve al menú "Reservas > Cabañas" para gestionar tus cabañas.', 'reservas'); ?>
                </li>
                <li>
                    <?php _e('Para cada cabaña, puedes:', 'reservas'); ?>
                    <ul>
                        <li><?php _e('Agregar una nueva cabaña', 'reservas'); ?></li>
                        <li><?php _e('Ver el shortcode específico para esa cabaña', 'reservas'); ?></li>
                        <li><?php _e('Administrar los bloqueos de esa cabaña', 'reservas'); ?></li>
                        <li><?php _e('Eliminar una cabaña', 'reservas'); ?></li>
                    </ul>
                </li>
            </ol>
            
            <h3><?php _e('3. Gestionar Bloqueos', 'reservas'); ?></h3>
            <ol>
                <li>
                    <?php _e('Ve al menú "Reservas > Bloqueos" para gestionar los bloqueos.', 'reservas'); ?>
                </li>
                <li>
                    <?php _e('Puedes:', 'reservas'); ?>
                    <ul>
                        <li><?php _e('Ver un resumen de bloqueos por cabaña', 'reservas'); ?></li>
                        <li><?php _e('Gestionar bloqueos específicos de cada cabaña', 'reservas'); ?></li>
                        <li><?php _e('Agregar nuevos bloqueos', 'reservas'); ?></li>
                        <li><?php _e('Eliminar bloqueos existentes', 'reservas'); ?></li>
                    </ul>
                </li>
            </ol>
            
            <h3><?php _e('4. Shortcodes Disponibles', 'reservas'); ?></h3>
            <p><?php _e('Cada cabaña tiene su propio shortcode que puedes encontrar en la sección "Cabañas". Los shortcodes disponibles son:', 'reservas'); ?></p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Shortcode', 'reservas'); ?></th>
                        <th><?php _e('Descripción', 'reservas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[reservas_calendario cabana_id="ID"]</code></td>
                        <td>
                            <?php _e('Muestra el calendario de reservas para una cabaña específica.', 'reservas'); ?>
                            <br>
                            <small><?php _e('Reemplaza "ID" con el ID de la cabaña que puedes encontrar en la sección Cabañas.', 'reservas'); ?></small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
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