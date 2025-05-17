<?php
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'reservas'));
}

// Obtener ID de la cabaña
$cabana_id = isset($_GET['cabana_id']) ? intval($_GET['cabana_id']) : 0;

// Obtener información de la cabaña
global $wpdb;
$table_cabanas = $wpdb->prefix . 'reservas_cabanas';
$cabana = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_cabanas WHERE id = %d", $cabana_id));

if (!$cabana) {
    echo '<div class="notice notice-error"><p>' . __('Cabaña no encontrada.', 'reservas') . '</p></div>';
    return;
}

// Procesar formulario de nuevo bloqueo
if (isset($_POST['submit_bloqueo']) && check_admin_referer('nuevo_bloqueo_nonce')) {
    $fecha_inicio = sanitize_text_field($_POST['fecha_inicio']);
    $fecha_fin = sanitize_text_field($_POST['fecha_fin']);
    $motivo = sanitize_text_field($_POST['motivo']);
    
    $table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
    
    $wpdb->insert(
        $table_bloqueos,
        array(
            'cabana_id' => $cabana_id,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'motivo' => $motivo
        ),
        array('%d', '%s', '%s', '%s')
    );
    
    echo '<div class="notice notice-success"><p>' . __('Bloqueo agregado correctamente.', 'reservas') . '</p></div>';
}

// Obtener bloqueos y reservas confirmadas
$table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
$table_reservas = $wpdb->prefix . 'reservas';

$fechas_bloqueadas = $wpdb->get_results($wpdb->prepare("
    (SELECT 
        b.id,
        b.fecha_inicio,
        b.fecha_fin,
        b.motivo,
        'bloqueo' as tipo
    FROM $table_bloqueos b
    WHERE b.cabana_id = %d)
    UNION ALL
    (SELECT 
        r.id,
        r.fecha_inicio,
        r.fecha_fin,
        CONCAT('Reserva confirmada - ', r.nombre) as motivo,
        'reserva' as tipo
    FROM $table_reservas r
    WHERE r.cabana_id = %d AND r.estado = 'confirmada')
    ORDER BY fecha_inicio DESC
", $cabana_id, $cabana_id));
?>

<div class="wrap">
    <h1>
        <?php printf(__('Fechas Bloqueadas - %s', 'reservas'), esc_html($cabana->nombre)); ?>
        <a href="<?php echo admin_url('admin.php?page=reservas-bloqueos'); ?>" class="page-title-action">
            <?php _e('Volver a Bloqueos', 'reservas'); ?>
        </a>
    </h1>
    
    <!-- Formulario para nuevo bloqueo -->
    <div class="card">
        <h2><?php _e('Agregar Nuevo Bloqueo', 'reservas'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('nuevo_bloqueo_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="fecha_inicio"><?php _e('Fecha de Inicio', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="fecha_fin"><?php _e('Fecha de Fin', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="motivo"><?php _e('Motivo', 'reservas'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="motivo" id="motivo" class="regular-text" required>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Agregar Bloqueo', 'reservas'), 'primary', 'submit_bloqueo'); ?>
        </form>
    </div>

    <!-- Lista de bloqueos y reservas -->
    <div class="card">
        <h2><?php _e('Fechas Bloqueadas Existentes', 'reservas'); ?></h2>
        <?php if (empty($fechas_bloqueadas)) : ?>
            <p><?php _e('No hay fechas bloqueadas registradas para esta cabaña.', 'reservas'); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Fecha de Inicio', 'reservas'); ?></th>
                        <th><?php _e('Fecha de Fin', 'reservas'); ?></th>
                        <th><?php _e('Motivo', 'reservas'); ?></th>
                        <th><?php _e('Tipo', 'reservas'); ?></th>
                        <th><?php _e('Acciones', 'reservas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fechas_bloqueadas as $fecha) : ?>
                        <tr class="<?php echo $fecha->tipo === 'reserva' ? 'reserva-confirmada' : ''; ?>">
                            <td><?php echo esc_html($fecha->fecha_inicio); ?></td>
                            <td><?php echo esc_html($fecha->fecha_fin); ?></td>
                            <td><?php echo esc_html($fecha->motivo); ?></td>
                            <td>
                                <?php 
                                if ($fecha->tipo === 'reserva') {
                                    _e('Reserva Confirmada', 'reservas');
                                } else {
                                    _e('Bloqueo Manual', 'reservas');
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($fecha->tipo === 'bloqueo'): ?>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=reservas-bloqueos&action=delete&id=' . $fecha->id), 'delete_bloqueo_' . $fecha->id); ?>" 
                                       class="button button-small button-link-delete" 
                                       onclick="return confirm('<?php _e('¿Está seguro de eliminar este bloqueo?', 'reservas'); ?>');">
                                        <?php _e('Eliminar', 'reservas'); ?>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo admin_url('admin.php?page=reservas&action=view&id=' . $fecha->id); ?>" 
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

<script>
jQuery(document).ready(function($) {
    // Validar que la fecha de fin no sea anterior a la fecha de inicio
    $('#fecha_fin').on('change', function() {
        var fechaInicio = $('#fecha_inicio').val();
        var fechaFin = $(this).val();
        
        if (fechaInicio && fechaFin && fechaFin < fechaInicio) {
            alert('<?php _e('La fecha de fin no puede ser anterior a la fecha de inicio.', 'reservas'); ?>');
            $(this).val('');
        }
    });
});
</script> 