<?php
if (!defined('ABSPATH')) {
    exit;
}

// Procesar formulario de nueva cabaña
if (isset($_POST['submit_cabana']) && check_admin_referer('nueva_cabana_nonce')) {
    $nombre = sanitize_text_field($_POST['nombre']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservas_cabanas';
    
    $wpdb->insert(
        $table_name,
        array('nombre' => $nombre),
        array('%s')
    );
    
    echo '<div class="notice notice-success"><p>' . __('Cabaña agregada correctamente.', 'reservas') . '</p></div>';
}

// Obtener lista de cabañas
global $wpdb;
$table_name = $wpdb->prefix . 'reservas_cabanas';
$cabanas = $wpdb->get_results("SELECT * FROM $table_name ORDER BY nombre ASC");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Formulario para nueva cabaña -->
    <div class="card">
        <h2><?php _e('Agregar Nueva Cabaña', 'reservas'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('nueva_cabana_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="nombre"><?php _e('Nombre de la Cabaña', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="nombre" id="nombre" class="regular-text" required>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Agregar Cabaña', 'reservas'), 'primary', 'submit_cabana'); ?>
        </form>
    </div>

    <!-- Lista de cabañas -->
    <div class="card">
        <h2><?php _e('Cabañas Existentes', 'reservas'); ?></h2>
        <?php if (empty($cabanas)) : ?>
            <p><?php _e('No hay cabañas registradas.', 'reservas'); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Nombre', 'reservas'); ?></th>
                        <th><?php _e('Acciones', 'reservas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cabanas as $cabana) : ?>
                        <tr>
                            <td><?php echo esc_html($cabana->nombre); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=reservas-bloqueos&cabana_id=' . $cabana->id); ?>" class="button button-small">
                                    <?php _e('Gestionar Bloqueos', 'reservas'); ?>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=reservas-cabanas&action=edit&id=' . $cabana->id); ?>" class="button button-small">
                                    <?php _e('Editar', 'reservas'); ?>
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=reservas-cabanas&action=delete&id=' . $cabana->id), 'delete_cabana_' . $cabana->id); ?>" 
                                   class="button button-small button-link-delete" 
                                   onclick="return confirm('<?php _e('¿Está seguro de eliminar esta cabaña?', 'reservas'); ?>');">
                                    <?php _e('Eliminar', 'reservas'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div> 