<?php
if (!defined('ABSPATH')) {
    exit;
}

// Verificar permisos
if (!current_user_can('manage_options')) {
    wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'reservas'));
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="reservas-admin-tabs">
        <a href="<?php echo admin_url('admin.php?page=reservas'); ?>" class="nav-tab">
            <?php _e('Cabañas', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-config'); ?>" class="nav-tab">
            <?php _e('Configuración', 'reservas'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=reservas-instructions'); ?>" class="nav-tab nav-tab-active">
            <?php _e('Instrucciones', 'reservas'); ?>
        </a>
    </div>
    
    <div class="reservas-instructions">
        <h2><?php _e('Instrucciones de Uso', 'reservas'); ?></h2>
        
        <div class="reservas-instruction-section">
            <h3><?php _e('1. Configuración Inicial', 'reservas'); ?></h3>
            <ol>
                <li><?php _e('Ve a la pestaña "Configuración" y establece el correo electrónico del administrador.', 'reservas'); ?></li>
                <li><?php _e('Este correo recibirá las solicitudes de reserva de los clientes.', 'reservas'); ?></li>
            </ol>
        </div>
        
        <div class="reservas-instruction-section">
            <h3><?php _e('2. Gestión de Cabañas', 'reservas'); ?></h3>
            <ol>
                <li><?php _e('En la pestaña "Cabañas", puedes agregar, editar o eliminar cabañas.', 'reservas'); ?></li>
                <li><?php _e('Para cada cabaña, especifica:', 'reservas'); ?>
                    <ul>
                        <li><?php _e('Nombre', 'reservas'); ?></li>
                        <li><?php _e('Descripción', 'reservas'); ?></li>
                        <li><?php _e('Capacidad', 'reservas'); ?></li>
                        <li><?php _e('Precio por noche', 'reservas'); ?></li>
                        <li><?php _e('Estado (activo/inactivo)', 'reservas'); ?></li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div class="reservas-instruction-section">
            <h3><?php _e('3. Gestión de Calendario', 'reservas'); ?></h3>
            <ol>
                <li><?php _e('Para cada cabaña, puedes ver su calendario haciendo clic en el botón "Calendario".', 'reservas'); ?></li>
                <li><?php _e('En el calendario puedes:', 'reservas'); ?>
                    <ul>
                        <li><?php _e('Ver las fechas bloqueadas (en rojo)', 'reservas'); ?></li>
                        <li><?php _e('Ver las fechas reservadas (en verde)', 'reservas'); ?></li>
                        <li><?php _e('Bloquear nuevas fechas usando el formulario', 'reservas'); ?></li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div class="reservas-instruction-section">
            <h3><?php _e('4. Mostrar el Calendario en el Frontend', 'reservas'); ?></h3>
            <ol>
                <li><?php _e('Usa el shortcode [reservas_calendario cabana_id="X"] donde X es el ID de la cabaña.', 'reservas'); ?></li>
                <li><?php _e('Puedes encontrar el ID de la cabaña en la lista de cabañas.', 'reservas'); ?></li>
                <li><?php _e('Los visitantes podrán ver el calendario y enviar solicitudes de reserva.', 'reservas'); ?></li>
            </ol>
        </div>
        
        <div class="reservas-instruction-section">
            <h3><?php _e('5. Proceso de Reserva', 'reservas'); ?></h3>
            <ol>
                <li><?php _e('Los clientes seleccionan las fechas en el calendario.', 'reservas'); ?></li>
                <li><?php _e('Completan el formulario con sus datos.', 'reservas'); ?></li>
                <li><?php _e('El administrador recibe la solicitud por correo.', 'reservas'); ?></li>
                <li><?php _e('El administrador puede aprobar o rechazar la reserva desde el panel.', 'reservas'); ?></li>
            </ol>
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

.reservas-instructions {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.reservas-instruction-section {
    margin-bottom: 30px;
}

.reservas-instruction-section h3 {
    margin-bottom: 15px;
    color: #23282d;
}

.reservas-instruction-section ol {
    margin-left: 20px;
}

.reservas-instruction-section ul {
    margin-left: 20px;
    list-style-type: disc;
}

.reservas-instruction-section li {
    margin-bottom: 10px;
}
</style> 