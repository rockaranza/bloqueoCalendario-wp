<?php
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'reservas'));
}

// Obtener la acción actual
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$cabana_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Procesar formularios
if (isset($_POST['submit_cabana'])) {
    $nombre = sanitize_text_field($_POST['nombre']);
    $descripcion = sanitize_textarea_field($_POST['descripcion']);
    $capacidad = intval($_POST['capacidad']);
    $precio = floatval($_POST['precio']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservas_cabanas';
    
    if ($cabana_id > 0) {
        $wpdb->update(
            $table_name,
            array(
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'capacidad' => $capacidad,
                'precio' => $precio
            ),
            array('id' => $cabana_id)
        );
        $message = __('Cabaña actualizada correctamente.', 'reservas');
    } else {
        $wpdb->insert(
            $table_name,
            array(
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'capacidad' => $capacidad,
                'precio' => $precio
            )
        );
        $cabana_id = $wpdb->insert_id;
        $message = __('Cabaña creada correctamente.', 'reservas');
        
        // Limpiar el formulario después de crear una nueva cabaña
        $cabana = null;
    }
    
    // Mostrar mensaje de éxito
    echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
}

// Procesar bloqueo de fechas
if (isset($_POST['submit_bloqueo'])) {
    $fecha_inicio = sanitize_text_field($_POST['fecha_inicio']);
    $fecha_fin = sanitize_text_field($_POST['fecha_fin']);
    $motivo = sanitize_text_field($_POST['motivo']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservas_bloqueos';
    
    $wpdb->insert(
        $table_name,
        array(
            'cabana_id' => $cabana_id,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'motivo' => $motivo
        )
    );
    
    echo '<div class="notice notice-success"><p>' . __('Fechas bloqueadas correctamente.', 'reservas') . '</p></div>';
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if ($action === 'list'): ?>
        <div class="reservas-admin-header">
            <a href="<?php echo admin_url('admin.php?page=reservas&action=add'); ?>" class="page-title-action">
                <?php _e('Añadir Cabaña', 'reservas'); ?>
            </a>
        </div>
        
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas_cabanas';
        $cabanas = $wpdb->get_results("SELECT * FROM $table_name ORDER BY nombre ASC");
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Nombre', 'reservas'); ?></th>
                    <th><?php _e('Capacidad', 'reservas'); ?></th>
                    <th><?php _e('Precio', 'reservas'); ?></th>
                    <th><?php _e('Estado', 'reservas'); ?></th>
                    <th><?php _e('Shortcode', 'reservas'); ?></th>
                    <th><?php _e('Acciones', 'reservas'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cabanas as $cabana): ?>
                    <tr>
                        <td><?php echo esc_html($cabana->nombre); ?></td>
                        <td><?php echo esc_html($cabana->capacidad); ?></td>
                        <td><?php echo number_format($cabana->precio, 2); ?> €</td>
                        <td><?php echo esc_html($cabana->estado); ?></td>
                        <td><code>[reservas_calendario cabana_id="<?php echo $cabana->id; ?>"]</code></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=reservas&action=edit&id=' . $cabana->id); ?>" class="button">
                                <?php _e('Editar', 'reservas'); ?>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=reservas&action=calendar&id=' . $cabana->id); ?>" class="button">
                                <?php _e('Calendario', 'reservas'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <?php
        $cabana = null;
        if ($cabana_id > 0) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'reservas_cabanas';
            $cabana = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $cabana_id));
        }
        ?>
        
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="nombre"><?php _e('Nombre', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="nombre" id="nombre" class="regular-text" 
                               value="<?php echo $cabana ? esc_attr($cabana->nombre) : ''; ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="descripcion"><?php _e('Descripción', 'reservas'); ?></label>
                    </th>
                    <td>
                        <textarea name="descripcion" id="descripcion" class="large-text" rows="5"><?php 
                            echo $cabana ? esc_textarea($cabana->descripcion) : ''; 
                        ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="capacidad"><?php _e('Capacidad', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="capacidad" id="capacidad" class="small-text" 
                               value="<?php echo $cabana ? esc_attr($cabana->capacidad) : ''; ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="precio"><?php _e('Precio por noche', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="precio" id="precio" class="small-text" step="0.01" 
                               value="<?php echo $cabana ? esc_attr($cabana->precio) : ''; ?>" required>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_cabana" class="button button-primary" 
                       value="<?php _e('Guardar Cabaña', 'reservas'); ?>">
                <a href="<?php echo admin_url('admin.php?page=reservas'); ?>" class="button">
                    <?php _e('Cancelar', 'reservas'); ?>
                </a>
            </p>
        </form>
        
    <?php elseif ($action === 'calendar' && $cabana_id > 0): ?>
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas_cabanas';
        $cabana = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $cabana_id));
        
        if ($cabana):
        ?>
            <h2><?php printf(__('Calendario de %s', 'reservas'), esc_html($cabana->nombre)); ?></h2>
            
            <div class="reservas-calendar-container">
                <div id="calendar"></div>
                
                <div class="reservas-bloqueo-form">
                    <h3><?php _e('Bloquear Fechas', 'reservas'); ?></h3>
                    <form method="post" action="">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="fecha_inicio"><?php _e('Fecha Inicio', 'reservas'); ?></label>
                                </th>
                                <td>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="fecha_fin"><?php _e('Fecha Fin', 'reservas'); ?></label>
                                </th>
                                <td>
                                    <input type="date" name="fecha_fin" id="fecha_fin" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="motivo"><?php _e('Motivo', 'reservas'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="motivo" id="motivo" class="regular-text">
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="submit_bloqueo" class="button button-primary" 
                                   value="<?php _e('Bloquear Fechas', 'reservas'); ?>">
                        </p>
                    </form>
                </div>
            </div>
            
            <?php
            // Obtener bloqueos de fechas
            $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
            $bloqueos = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_bloqueos WHERE cabana_id = %d ORDER BY fecha_inicio ASC",
                $cabana_id
            ));
            
            if ($bloqueos):
            ?>
                <h3><?php _e('Fechas Bloqueadas', 'reservas'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Fecha Inicio', 'reservas'); ?></th>
                            <th><?php _e('Fecha Fin', 'reservas'); ?></th>
                            <th><?php _e('Motivo', 'reservas'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bloqueos as $bloqueo): ?>
                            <tr>
                                <td><?php echo esc_html($bloqueo->fecha_inicio); ?></td>
                                <td><?php echo esc_html($bloqueo->fecha_fin); ?></td>
                                <td><?php echo esc_html($bloqueo->motivo); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.reservas-admin-header {
    margin-bottom: 20px;
}

.reservas-calendar-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

#calendar {
    flex: 2;
}

.reservas-bloqueo-form {
    flex: 1;
    padding: 20px;
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.fc-event {
    cursor: pointer;
}

.fc-event.blocked {
    background-color: #dc3545;
    border-color: #dc3545;
}
</style>

<script>
jQuery(document).ready(function($) {
    var calendar = $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'month',
        editable: false,
        eventLimit: true,
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'reservas_get_events',
                    cabana_id: <?php echo $cabana_id; ?>,
                    start: start.format(),
                    end: end.format()
                },
                success: function(response) {
                    if (response.success) {
                        callback(response.data);
                    }
                }
            });
        },
        eventRender: function(event, element) {
            if (event.type === 'blocked') {
                element.addClass('blocked');
            }
        }
    });
});
</script> 