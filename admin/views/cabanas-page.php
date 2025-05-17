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
        <?php if (empty($cabanas)): ?>
            <p><?php _e('No hay cabañas registradas.', 'reservas'); ?></p>
        <?php else: ?>
            <div class="table-container">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 5%;"><?php _e('ID', 'reservas'); ?></th>
                            <th style="width: 15%;"><?php _e('Nombre', 'reservas'); ?></th>
                            <th style="width: 60%;"><?php _e('Shortcodes', 'reservas'); ?></th>
                            <th style="width: 20%;"><?php _e('Acciones', 'reservas'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cabanas as $cabana): ?>
                            <tr>
                                <td><?php echo esc_html($cabana->id); ?></td>
                                <td><?php echo esc_html($cabana->nombre); ?></td>
                                <td>
                                    <div class="shortcode-container">
                                        <div class="shortcode-item">
                                            <code>[reservas_calendario cabana_id="<?php echo esc_attr($cabana->id); ?>"]</code>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo admin_url('admin.php?page=reservas-bloqueos&cabana_id=' . $cabana->id); ?>" class="button button-small">
                                            <?php _e('Gestionar Bloqueos', 'reservas'); ?>
                                        </a>
                                        <button class="button button-small button-link-delete delete-cabana" data-cabana-id="<?php echo esc_attr($cabana->id); ?>">
                                            <?php _e('Eliminar', 'reservas'); ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-top: 20px;
    padding: 20px;
    max-width: 800px;
}

.card:first-child {
    margin-top: 0;
}

/* Estilo específico para la card de Cabañas Existentes */
.card:last-child {
    width: fit-content;
    min-width: auto;
    max-width: none;
}

.table-container {
    overflow-x: auto;
    margin: 0 -20px;
    padding: 0 20px;
    min-width: 640px;
    width: fit-content;
}

.shortcode-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 320px;
}

.shortcode-item {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: nowrap;
    min-width: 320px;
}

.shortcode-item code {
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 13px;
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 240px;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 120px;
}

.action-buttons .button {
    text-align: center;
    justify-content: center;
}

.button-link-delete {
    color: #dc3545;
}

.button-link-delete:hover {
    color: #c82333;
}

@media screen and (max-width: 782px) {
    .table-container {
        min-width: 480px;
    }
    
    .shortcode-container {
        min-width: 240px;
    }
    
    .shortcode-item {
        min-width: 240px;
        flex-wrap: wrap;
    }
    
    .shortcode-item code {
        width: 100%;
        margin: 5px 0;
        min-width: 160px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Eliminar cabaña
    $('.delete-cabana').click(function(e) {
        e.preventDefault();
        
        if (!confirm('<?php _e('¿Está seguro de que desea eliminar esta cabaña? Esta acción no se puede deshacer.', 'reservas'); ?>')) {
            return;
        }
        
        var cabanaId = $(this).data('cabana-id');
        var button = $(this);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'reservas_delete_cabana',
                nonce: '<?php echo wp_create_nonce('reservas_delete_cabana'); ?>',
                cabana_id: cabanaId
            },
            beforeSend: function() {
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('Error al eliminar la cabaña', 'reservas'); ?>');
                    button.prop('disabled', false);
                }
            },
            error: function() {
                alert('<?php _e('Error al eliminar la cabaña', 'reservas'); ?>');
                button.prop('disabled', false);
            }
        });
    });
});
</script> 