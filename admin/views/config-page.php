<?php
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'reservas'));
}

// Procesar formulario
if (isset($_POST['submit_config'])) {
    check_admin_referer('reservas_config_nonce');
    
    $admin_email = sanitize_email($_POST['admin_email']);
    $google_client_id = sanitize_text_field($_POST['google_client_id']);
    $google_client_secret = sanitize_text_field($_POST['google_client_secret']);
    
    update_option('reservas_admin_email', $admin_email);
    update_option('reservas_google_client_id', $google_client_id);
    update_option('reservas_google_client_secret', $google_client_secret);
    
    echo '<div class="notice notice-success"><p>' . __('Configuración guardada correctamente.', 'reservas') . '</p></div>';
}

// Obtener valores actuales
$admin_email = get_option('reservas_admin_email', get_option('admin_email'));
$google_client_id = get_option('reservas_google_client_id', '');
$google_client_secret = get_option('reservas_google_client_secret', '');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="reservas-admin-tabs">
        <a href="<?php echo admin_url('admin.php?page=reservas'); ?>" class="nav-tab">
            <?php _e('Reservas', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-config'); ?>" class="nav-tab nav-tab-active">
            <?php _e('Configuración', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-instructions'); ?>" class="nav-tab">
            <?php _e('Instrucciones', 'reservas'); ?>
        </a>
    </div>
    
    <div class="reservas-admin-content">
        <form method="post" action="">
            <?php wp_nonce_field('reservas_config_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="admin_email"><?php _e('Email de notificaciones', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="admin_email" id="admin_email" 
                               value="<?php echo esc_attr($admin_email); ?>" class="regular-text" required>
                        <p class="description">
                            <?php _e('Email donde se enviarán las notificaciones de nuevas reservas.', 'reservas'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="google_client_id"><?php _e('Google Client ID', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="google_client_id" id="google_client_id" 
                               value="<?php echo esc_attr($google_client_id); ?>" class="regular-text">
                        <p class="description">
                            <?php _e('ID de cliente de Google Calendar API.', 'reservas'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="google_client_secret"><?php _e('Google Client Secret', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="google_client_secret" id="google_client_secret" 
                               value="<?php echo esc_attr($google_client_secret); ?>" class="regular-text">
                        <p class="description">
                            <?php _e('Secret de cliente de Google Calendar API.', 'reservas'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_config" class="button button-primary" 
                       value="<?php _e('Guardar Configuración', 'reservas'); ?>">
            </p>
        </form>
        
        <div class="reservas-admin-section">
            <h2><?php _e('Configuración de Google Calendar', 'reservas'); ?></h2>
            
            <p>
                <?php _e('Para integrar el calendario de reservas con Google Calendar, sigue estos pasos:', 'reservas'); ?>
            </p>
            
            <ol>
                <li>
                    <?php _e('Ve a la', 'reservas'); ?> 
                    <a href="https://console.developers.google.com/" target="_blank">
                        <?php _e('Google Cloud Console', 'reservas'); ?>
                    </a>
                </li>
                <li>
                    <?php _e('Crea un nuevo proyecto o selecciona uno existente.', 'reservas'); ?>
                </li>
                <li>
                    <?php _e('Habilita la API de Google Calendar.', 'reservas'); ?>
                </li>
                <li>
                    <?php _e('Crea credenciales OAuth 2.0 y obtén el Client ID y Client Secret.', 'reservas'); ?>
                </li>
                <li>
                    <?php _e('Ingresa las credenciales en los campos anteriores.', 'reservas'); ?>
                </li>
            </ol>
            
            <p>
                <?php _e('Una vez configurado, las reservas confirmadas se sincronizarán automáticamente con Google Calendar.', 'reservas'); ?>
            </p>
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
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.reservas-admin-section h2 {
    margin-top: 0;
}

.reservas-admin-section ol {
    margin-left: 20px;
}

.reservas-admin-section li {
    margin-bottom: 10px;
}
</style> 