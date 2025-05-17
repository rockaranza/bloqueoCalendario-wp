<?php
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'reservas'));
}

// Obtener todas las cabañas con sus bloqueos y reservas confirmadas
global $wpdb;
$table_cabanas = $wpdb->prefix . 'reservas_cabanas';
$table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
$table_reservas = $wpdb->prefix . 'reservas';

$cabanas = $wpdb->get_results("
    SELECT c.*, 
           COUNT(DISTINCT b.id) + COUNT(DISTINCT r.id) as total_bloqueos
    FROM $table_cabanas c
    LEFT JOIN $table_bloqueos b ON c.id = b.cabana_id
    LEFT JOIN $table_reservas r ON c.id = r.cabana_id AND r.estado = 'confirmada'
    GROUP BY c.id
    ORDER BY c.nombre ASC
");

// Obtener todos los bloqueos y reservas confirmadas
$bloqueos = $wpdb->get_results("
    (SELECT 
        b.id,
        b.cabana_id,
        c.nombre as cabana_nombre,
        b.fecha_inicio,
        b.fecha_fin,
        b.motivo,
        'bloqueo' as tipo
    FROM $table_bloqueos b
    JOIN $table_cabanas c ON b.cabana_id = c.id)
    UNION ALL
    (SELECT 
        r.id,
        r.cabana_id,
        c.nombre as cabana_nombre,
        r.fecha_inicio,
        r.fecha_fin,
        CONCAT('Reserva confirmada - ', r.nombre) as motivo,
        'reserva' as tipo
    FROM $table_reservas r
    JOIN $table_cabanas c ON r.cabana_id = c.id
    WHERE r.estado = 'confirmada')
    ORDER BY fecha_inicio DESC
");
?>

<div class="wrap">
    <h1><?php _e('Gestión de Bloqueos', 'reservas'); ?></h1>
    
    <!-- Resumen de bloqueos por cabaña -->
    <div class="card">
        <h2><?php _e('Resumen de Fechas Bloqueadas por Cabaña', 'reservas'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Cabaña', 'reservas'); ?></th>
                    <th><?php _e('Total de Fechas Bloqueadas', 'reservas'); ?></th>
                    <th><?php _e('Acciones', 'reservas'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cabanas as $cabana): ?>
                    <tr>
                        <td><?php echo esc_html($cabana->nombre); ?></td>
                        <td><?php echo esc_html($cabana->total_bloqueos); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=reservas-bloqueos&cabana_id=' . $cabana->id); ?>" class="button button-small">
                                <?php _e('Gestionar Bloqueos', 'reservas'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Lista de todos los bloqueos y reservas -->
    <div class="card">
        <h2><?php _e('Todas las Fechas Bloqueadas', 'reservas'); ?></h2>
        <?php if (empty($bloqueos)): ?>
            <p><?php _e('No hay fechas bloqueadas registradas.', 'reservas'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Cabaña', 'reservas'); ?></th>
                        <th><?php _e('Fecha de Inicio', 'reservas'); ?></th>
                        <th><?php _e('Fecha de Fin', 'reservas'); ?></th>
                        <th><?php _e('Motivo', 'reservas'); ?></th>
                        <th><?php _e('Tipo', 'reservas'); ?></th>
                        <th><?php _e('Acciones', 'reservas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloqueos as $bloqueo): ?>
                        <tr class="<?php echo $bloqueo->tipo === 'reserva' ? 'reserva-confirmada' : ''; ?>">
                            <td><?php echo esc_html($bloqueo->cabana_nombre); ?></td>
                            <td><?php echo esc_html($bloqueo->fecha_inicio); ?></td>
                            <td><?php echo esc_html($bloqueo->fecha_fin); ?></td>
                            <td><?php echo esc_html($bloqueo->motivo); ?></td>
                            <td>
                                <?php 
                                if ($bloqueo->tipo === 'reserva') {
                                    _e('Reserva Confirmada', 'reservas');
                                } else {
                                    _e('Bloqueo Manual', 'reservas');
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($bloqueo->tipo === 'bloqueo'): ?>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=reservas-bloqueos&action=delete&id=' . $bloqueo->id), 'delete_bloqueo_' . $bloqueo->id); ?>" 
                                       class="button button-small button-link-delete" 
                                       onclick="return confirm('<?php _e('¿Está seguro de eliminar este bloqueo?', 'reservas'); ?>');">
                                        <?php _e('Eliminar', 'reservas'); ?>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo admin_url('admin.php?page=reservas&action=view&id=' . $bloqueo->id); ?>" 
                                       class="button button-small">
                                        <?php _e('Ver Reserva', 'reservas'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.card {
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.wp-list-table {
    margin-top: 10px;
}

.button-link-delete {
    color: #dc3545;
}

.button-link-delete:hover {
    color: #c82333;
}

.reserva-confirmada {
    background-color: #f8f9fa;
}

.reserva-confirmada td {
    color: #28a745;
}
</style> 