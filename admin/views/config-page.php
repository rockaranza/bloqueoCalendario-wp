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
    $admin_email = sanitize_email($_POST['admin_email']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservas_config';
    
    $wpdb->update(
        $table_name,
        array('admin_email' => $admin_email),
        array('id' => 1)
    );
    
    echo '<div class="notice notice-success"><p>' . __('Configuración actualizada correctamente.', 'reservas') . '</p></div>';
}

// Obtener configuración actual
global $wpdb;
$table_name = $wpdb->prefix . 'reservas_config';
$config = $wpdb->get_row("SELECT * FROM $table_name WHERE id = 1");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="reservas-admin-tabs">
        <a href="<?php echo admin_url('admin.php?page=reservas'); ?>" class="nav-tab">
            <?php _e('Cabañas', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-config'); ?>" class="nav-tab nav-tab-active">
            <?php _e('Configuración', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-instructions'); ?>" class="nav-tab">
            <?php _e('Instrucciones', 'reservas'); ?>
        </a>
    </div>
    
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="admin_email"><?php _e('Correo del administrador', 'reservas'); ?></label>
                </th>
                <td>
                    <input type="email" name="admin_email" id="admin_email" class="regular-text" 
                           value="<?php echo esc_attr($config->admin_email); ?>" required>
                    <p class="description">
                        <?php _e('Este correo recibirá las solicitudes de reserva.', 'reservas'); ?>
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
</style> 