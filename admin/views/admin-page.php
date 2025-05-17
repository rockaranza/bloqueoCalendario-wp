<?php
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'reservas'));
}

// Obtener cabañas
global $wpdb;
$table_cabanas = $wpdb->prefix . 'reservas_cabanas';
$cabanas = $wpdb->get_results("SELECT * FROM $table_cabanas ORDER BY nombre ASC");

// Obtener reservas pendientes
$table_reservas = $wpdb->prefix . 'reservas';
$reservas_pendientes = $wpdb->get_results(
    "SELECT r.*, c.nombre as cabana_nombre 
    FROM $table_reservas r 
    JOIN $table_cabanas c ON r.cabana_id = c.id 
    WHERE r.estado = 'pendiente' 
    ORDER BY r.fecha_creacion DESC"
);

// Obtener todas las reservas
$reservas_todas = $wpdb->get_results(
    "SELECT r.*, c.nombre as cabana_nombre 
    FROM $table_reservas r 
    JOIN $table_cabanas c ON r.cabana_id = c.id 
    ORDER BY r.fecha_creacion DESC"
);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="reservas-admin-tabs">
        <a href="<?php echo admin_url('admin.php?page=reservas'); ?>" class="nav-tab nav-tab-active">
            <?php _e('Reservas', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-config'); ?>" class="nav-tab">
            <?php _e('Configuración', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-instructions'); ?>" class="nav-tab">
            <?php _e('Instrucciones', 'reservas'); ?>
        </a>
    </div>

    <div class="reservas-admin-content">
        <div class="reservas-admin-section">
            <h2><?php _e('Reservas Pendientes', 'reservas'); ?></h2>
            
            <?php if (empty($reservas_pendientes)): ?>
                <p><?php _e('No hay reservas pendientes.', 'reservas'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Cabaña', 'reservas'); ?></th>
                            <th><?php _e('Cliente', 'reservas'); ?></th>
                            <th><?php _e('Email', 'reservas'); ?></th>
                            <th><?php _e('Teléfono', 'reservas'); ?></th>
                            <th><?php _e('Fecha Inicio', 'reservas'); ?></th>
                            <th><?php _e('Fecha Fin', 'reservas'); ?></th>
                            <th><?php _e('Comentarios', 'reservas'); ?></th>
                            <th><?php _e('Acciones', 'reservas'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas_pendientes as $reserva): ?>
                            <tr>
                                <td><?php echo esc_html($reserva->cabana_nombre); ?></td>
                                <td><?php echo esc_html($reserva->nombre); ?></td>
                                <td><?php echo esc_html($reserva->email); ?></td>
                                <td><?php echo esc_html($reserva->telefono); ?></td>
                                <td><?php echo esc_html($reserva->fecha_inicio); ?></td>
                                <td><?php echo esc_html($reserva->fecha_fin); ?></td>
                                <td><?php echo esc_html($reserva->comentarios); ?></td>
                                <td>
                                    <button class="button button-primary confirm-reserva" 
                                            data-reserva-id="<?php echo esc_attr($reserva->id); ?>">
                                        <?php _e('Confirmar', 'reservas'); ?>
                                    </button>
                                    <button class="button button-secondary reject-reserva" 
                                            data-reserva-id="<?php echo esc_attr($reserva->id); ?>">
                                        <?php _e('Rechazar', 'reservas'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="reservas-admin-section">
            <h2><?php _e('Todas las Reservas', 'reservas'); ?></h2>
            
            <?php if (empty($reservas_todas)): ?>
                <p><?php _e('No hay reservas registradas.', 'reservas'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Cabaña', 'reservas'); ?></th>
                            <th><?php _e('Cliente', 'reservas'); ?></th>
                            <th><?php _e('Email', 'reservas'); ?></th>
                            <th><?php _e('Teléfono', 'reservas'); ?></th>
                            <th><?php _e('Fecha Inicio', 'reservas'); ?></th>
                            <th><?php _e('Fecha Fin', 'reservas'); ?></th>
                            <th><?php _e('Estado', 'reservas'); ?></th>
                            <th><?php _e('Comentarios', 'reservas'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas_todas as $reserva): ?>
                            <tr>
                                <td><?php echo esc_html($reserva->cabana_nombre); ?></td>
                                <td><?php echo esc_html($reserva->nombre); ?></td>
                                <td><?php echo esc_html($reserva->email); ?></td>
                                <td><?php echo esc_html($reserva->telefono); ?></td>
                                <td><?php echo esc_html($reserva->fecha_inicio); ?></td>
                                <td><?php echo esc_html($reserva->fecha_fin); ?></td>
                                <td>
                                    <span class="reservas-estado reservas-estado-<?php echo esc_attr($reserva->estado); ?>">
                                        <?php 
                                        switch ($reserva->estado) {
                                            case 'pendiente':
                                                _e('Pendiente', 'reservas');
                                                break;
                                            case 'confirmada':
                                                _e('Confirmada', 'reservas');
                                                break;
                                            case 'rechazada':
                                                _e('Rechazada', 'reservas');
                                                break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($reserva->comentarios); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="reservas-admin-section">
            <h2><?php _e('Gestionar Cabañas', 'reservas'); ?></h2>
            
            <?php foreach ($cabanas as $cabana): ?>
                <div class="reservas-cabana-card">
                    <h3><?php echo esc_html($cabana->nombre); ?></h3>
                    
                    <div class="reservas-cabana-actions">
                        <button class="button button-primary add-block" 
                                data-cabana-id="<?php echo esc_attr($cabana->id); ?>">
                            <?php _e('Agregar Bloqueo', 'reservas'); ?>
                        </button>
                        
                        <button class="button button-secondary view-blocks" 
                                data-cabana-id="<?php echo esc_attr($cabana->id); ?>">
                            <?php _e('Ver Bloqueos', 'reservas'); ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal para agregar bloqueo -->
<div id="add-block-modal" class="reservas-modal">
    <div class="reservas-modal-content">
        <span class="reservas-modal-close">&times;</span>
        <h3><?php _e('Agregar Bloqueo', 'reservas'); ?></h3>
        
        <form id="add-block-form">
            <input type="hidden" name="cabana_id" id="block-cabana-id">
            <input type="hidden" name="action" value="reservas_add_block">
            <?php wp_nonce_field('reservas_nonce', 'reservas_nonce'); ?>
            
            <div class="form-group">
                <label for="block-fecha-inicio"><?php _e('Fecha Inicio:', 'reservas'); ?></label>
                <input type="date" id="block-fecha-inicio" name="fecha_inicio" required>
            </div>
            
            <div class="form-group">
                <label for="block-fecha-fin"><?php _e('Fecha Fin:', 'reservas'); ?></label>
                <input type="date" id="block-fecha-fin" name="fecha_fin" required>
            </div>
            
            <div class="form-group">
                <label for="block-motivo"><?php _e('Motivo:', 'reservas'); ?></label>
                <input type="text" id="block-motivo" name="motivo" required>
            </div>
            
            <div class="form-messages"></div>
            
            <button type="submit" class="button button-primary"><?php _e('Agregar Bloqueo', 'reservas'); ?></button>
        </form>
    </div>
</div>

<!-- Modal para ver bloqueos -->
<div id="view-blocks-modal" class="reservas-modal">
    <div class="reservas-modal-content">
        <span class="reservas-modal-close">&times;</span>
        <h3><?php _e('Bloqueos', 'reservas'); ?></h3>
        
        <div id="blocks-list"></div>
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

.reservas-admin-section {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.reservas-cabana-card {
    background: #f8f9fa;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.reservas-cabana-actions {
    margin-top: 10px;
}

.reservas-cabana-actions .button {
    margin-right: 10px;
}

.reservas-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.reservas-modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    max-width: 500px;
    position: relative;
}

.reservas-modal-close {
    position: absolute;
    right: 20px;
    top: 10px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-messages {
    margin: 15px 0;
    padding: 10px;
    border-radius: 4px;
    display: none;
}

.form-messages.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.form-messages.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.reservas-estado {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
}

.reservas-estado-pendiente {
    background-color: #ffc107;
    color: #856404;
}

.reservas-estado-confirmada {
    background-color: #28a745;
    color: #fff;
}

.reservas-estado-rechazada {
    background-color: #dc3545;
    color: #fff;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Cerrar modales
    $('.reservas-modal-close').click(function() {
        $('.reservas-modal').hide();
    });
    
    $(window).click(function(e) {
        if ($(e.target).is('.reservas-modal')) {
            $('.reservas-modal').hide();
        }
    });
    
    // Agregar bloqueo
    $('.add-block').click(function() {
        var cabanaId = $(this).data('cabana-id');
        $('#block-cabana-id').val(cabanaId);
        $('#add-block-modal').show();
    });
    
    // Ver bloqueos
    $('.view-blocks').click(function() {
        var cabanaId = $(this).data('cabana-id');
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'reservas_get_blocks',
                nonce: reservas_ajax.nonce,
                cabana_id: cabanaId
            },
            success: function(response) {
                if (response.success) {
                    var html = '<table class="wp-list-table widefat fixed striped">';
                    html += '<thead><tr>';
                    html += '<th><?php _e('Fecha Inicio', 'reservas'); ?></th>';
                    html += '<th><?php _e('Fecha Fin', 'reservas'); ?></th>';
                    html += '<th><?php _e('Motivo', 'reservas'); ?></th>';
                    html += '<th><?php _e('Acciones', 'reservas'); ?></th>';
                    html += '</tr></thead><tbody>';
                    
                    response.data.forEach(function(block) {
                        html += '<tr>';
                        html += '<td>' + block.fecha_inicio + '</td>';
                        html += '<td>' + block.fecha_fin + '</td>';
                        html += '<td>' + block.motivo + '</td>';
                        html += '<td>';
                        html += '<button class="button button-secondary delete-block" data-block-id="' + block.id + '">';
                        html += '<?php _e('Eliminar', 'reservas'); ?>';
                        html += '</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    
                    $('#blocks-list').html(html);
                    $('#view-blocks-modal').show();
                }
            }
        });
    });
    
    // Enviar formulario de bloqueo
    $('#add-block-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('.form-messages').removeClass('error').addClass('success')
                        .text(response.data.message)
                        .show();
                    $('#add-block-form')[0].reset();
                    setTimeout(function() {
                        $('#add-block-modal').hide();
                        location.reload();
                    }, 2000);
                } else {
                    $('.form-messages').removeClass('success').addClass('error')
                        .text(response.data.message)
                        .show();
                }
            }
        });
    });
    
    // Eliminar bloqueo
    $(document).on('click', '.delete-block', function() {
        if (!confirm('<?php _e('¿Está seguro de que desea eliminar este bloqueo?', 'reservas'); ?>')) {
            return;
        }
        
        var blockId = $(this).data('block-id');
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'reservas_delete_block',
                nonce: reservas_ajax.nonce,
                bloqueo_id: blockId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Confirmar reserva
    $('.confirm-reserva').click(function() {
        if (!confirm('<?php _e('¿Está seguro de que desea confirmar esta reserva?', 'reservas'); ?>')) {
            return;
        }
        
        var reservaId = $(this).data('reserva-id');
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'reservas_update_status',
                nonce: reservas_ajax.nonce,
                reserva_id: reservaId,
                estado: 'confirmada'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Rechazar reserva
    $('.reject-reserva').click(function() {
        if (!confirm('<?php _e('¿Está seguro de que desea rechazar esta reserva?', 'reservas'); ?>')) {
            return;
        }
        
        var reservaId = $(this).data('reserva-id');
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'reservas_update_status',
                nonce: reservas_ajax.nonce,
                reserva_id: reservaId,
                estado: 'rechazada'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script> 