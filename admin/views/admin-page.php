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

.button-link-delete {
    color: #dc3545;
}

.button-link-delete:hover {
    color: #c82333;
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