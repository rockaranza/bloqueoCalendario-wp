<?php
/**
 * Vista de la página de administración de reservas
 *
 * @package Reservas
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('No tienes permisos suficientes para acceder a esta página.', 'reservas'));
}

// Obtener cabañas
global $wpdb;
$table_cabanas = $wpdb->prefix . 'reservas_cabanas';
$cabanas = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM %i ORDER BY nombre ASC",
        $table_cabanas
    )
);

// Obtener reservas pendientes
$table_reservas = $wpdb->prefix . 'reservas';
$reservas_pendientes = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT r.*, c.nombre as cabana_nombre 
        FROM %i r 
        JOIN %i c ON r.cabana_id = c.id 
        WHERE r.estado = %s 
        ORDER BY r.fecha_creacion DESC",
        $table_reservas,
        $table_cabanas,
        'pendiente'
    )
);

// Obtener todas las reservas
$reservas_todas = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT r.*, c.nombre as cabana_nombre 
        FROM %i r 
        JOIN %i c ON r.cabana_id = c.id 
        ORDER BY r.fecha_creacion DESC",
        $table_reservas,
        $table_cabanas
    )
);

// Verificar nonce para AJAX
$ajax_nonce = wp_create_nonce('reservas_admin_nonce');

// Cargar estilos y scripts
wp_enqueue_style('reservas-admin');
wp_enqueue_script('reservas-admin');

// Localizar script
wp_localize_script('reservas-admin', 'reservasAdmin', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'i18n' => array(
        'confirmReserva' => __('¿Está seguro de que desea confirmar esta reserva?', 'reservas'),
        'rejectReserva' => __('¿Está seguro de que desea rechazar esta reserva?', 'reservas'),
        'errorUpdate' => __('Error al actualizar la reserva', 'reservas'),
        'errorConnection' => __('Error de conexión', 'reservas')
    )
));
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="reservas-admin-content">
        <div class="reservas-admin-section">
            <h2><?php esc_html_e('Reservas Pendientes', 'reservas'); ?></h2>
            
            <?php if (empty($reservas_pendientes)): ?>
                <p><?php esc_html_e('No hay reservas pendientes.', 'reservas'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Cabaña', 'reservas'); ?></th>
                            <th><?php esc_html_e('Cliente', 'reservas'); ?></th>
                            <th><?php esc_html_e('Email', 'reservas'); ?></th>
                            <th><?php esc_html_e('Teléfono', 'reservas'); ?></th>
                            <th><?php esc_html_e('Fecha Inicio', 'reservas'); ?></th>
                            <th><?php esc_html_e('Fecha Fin', 'reservas'); ?></th>
                            <th><?php esc_html_e('Acciones', 'reservas'); ?></th>
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
                                <td>
                                    <button class="button button-primary confirm-reserva" 
                                            data-reserva-id="<?php echo esc_attr($reserva->id); ?>"
                                            data-nonce="<?php echo esc_attr($ajax_nonce); ?>">
                                        <?php esc_html_e('Confirmar', 'reservas'); ?>
                                    </button>
                                    <button class="button button-secondary reject-reserva" 
                                            data-reserva-id="<?php echo esc_attr($reserva->id); ?>"
                                            data-nonce="<?php echo esc_attr($ajax_nonce); ?>">
                                        <?php esc_html_e('Rechazar', 'reservas'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="reservas-admin-section">
            <h2><?php esc_html_e('Todas las Reservas', 'reservas'); ?></h2>
            
            <?php if (empty($reservas_todas)): ?>
                <p><?php esc_html_e('No hay reservas registradas.', 'reservas'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Cabaña', 'reservas'); ?></th>
                            <th><?php esc_html_e('Cliente', 'reservas'); ?></th>
                            <th><?php esc_html_e('Email', 'reservas'); ?></th>
                            <th><?php esc_html_e('Teléfono', 'reservas'); ?></th>
                            <th><?php esc_html_e('Fecha Inicio', 'reservas'); ?></th>
                            <th><?php esc_html_e('Fecha Fin', 'reservas'); ?></th>
                            <th><?php esc_html_e('Estado', 'reservas'); ?></th>
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
                                                esc_html_e('Pendiente', 'reservas');
                                                break;
                                            case 'confirmada':
                                                esc_html_e('Confirmada', 'reservas');
                                                break;
                                            case 'rechazada':
                                                esc_html_e('Rechazada', 'reservas');
                                                break;
                                            default:
                                                esc_html_e('Desconocido', 'reservas');
                                                break;
                                        }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div> 