<?php
if (!defined('ABSPATH')) {
    exit;
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

// Obtener bloqueos existentes
$table_bloqueos = $wpdb->prefix . 'reservas_bloqueos';
$bloqueos = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_bloqueos WHERE cabana_id = %d ORDER BY fecha_inicio DESC",
    $cabana_id
));
?>

<div class="wrap">
    <h1>
        <?php printf(__('Bloqueos de Fechas - %s', 'reservas'), esc_html($cabana->nombre)); ?>
        <a href="<?php echo admin_url('admin.php?page=reservas-cabanas'); ?>" class="page-title-action">
            <?php _e('Volver a Cabañas', 'reservas'); ?>
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

    <!-- Lista de bloqueos -->
    <div class="card">
        <h2><?php _e('Bloqueos Existentes', 'reservas'); ?></h2>
        <?php if (empty($bloqueos)) : ?>
            <p><?php _e('No hay bloqueos registrados para esta cabaña.', 'reservas'); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Fecha de Inicio', 'reservas'); ?></th>
                        <th><?php _e('Fecha de Fin', 'reservas'); ?></th>
                        <th><?php _e('Motivo', 'reservas'); ?></th>
                        <th><?php _e('Acciones', 'reservas'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloqueos as $bloqueo) : ?>
                        <tr>
                            <td><?php echo esc_html($bloqueo->fecha_inicio); ?></td>
                            <td><?php echo esc_html($bloqueo->fecha_fin); ?></td>
                            <td><?php echo esc_html($bloqueo->motivo); ?></td>
                            <td>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=reservas-bloqueos&action=delete&id=' . $bloqueo->id . '&cabana_id=' . $cabana_id), 'delete_bloqueo_' . $bloqueo->id); ?>" 
                                   class="button button-small button-link-delete" 
                                   onclick="return confirm('<?php _e('¿Está seguro de eliminar este bloqueo?', 'reservas'); ?>');">
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