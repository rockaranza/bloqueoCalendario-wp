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
    update_option('reservas_admin_email', $admin_email);
    
    echo '<div class="notice notice-success"><p>' . __('Configuración guardada correctamente.', 'reservas') . '</p></div>';
}

// Obtener valores actuales
$admin_email = get_option('reservas_admin_email', get_option('admin_email'));
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
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_config" class="button button-primary" 
                       value="<?php _e('Guardar Configuración', 'reservas'); ?>">
            </p>
        </form>
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
</style> 