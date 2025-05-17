<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Configuración de Calendario iCal', 'reservas'); ?></h1>
    
    <?php settings_errors(); ?>
    
    <form method="post" action="options.php">
        <?php 
        settings_fields('reservas_options');
        do_settings_sections('reservas_options');
        ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="reservas_ical_url"><?php _e('URL del Calendario iCal', 'reservas'); ?></label>
                </th>
                <td>
                    <input type="url" id="reservas_ical_url" name="reservas_ical_url" 
                           value="<?php echo esc_attr(get_option('reservas_ical_url')); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('La URL del calendario iCal que se usará para las reservas.', 'reservas'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <div class="card">
        <h2><?php _e('Instrucciones', 'reservas'); ?></h2>
        <ol>
            <li><?php _e('Crea una cuenta en un servicio de calendario que soporte iCal (como Google Calendar, Apple Calendar, etc.)', 'reservas'); ?></li>
            <li><?php _e('Crea un nuevo calendario para las reservas', 'reservas'); ?></li>
            <li><?php _e('Obtén la URL pública del calendario en formato iCal', 'reservas'); ?></li>
            <li><?php _e('Pega la URL en el campo de arriba', 'reservas'); ?></li>
            <li><?php _e('Guarda los cambios', 'reservas'); ?></li>
        </ol>
        
        <h3><?php _e('Cómo obtener la URL de iCal', 'reservas'); ?></h3>
        <h4><?php _e('Google Calendar:', 'reservas'); ?></h4>
        <ol>
            <li><?php _e('Ve a la configuración del calendario', 'reservas'); ?></li>
            <li><?php _e('En la sección "Integrar calendario", copia la URL de iCal', 'reservas'); ?></li>
        </ol>
        
        <h4><?php _e('Apple Calendar:', 'reservas'); ?></h4>
        <ol>
            <li><?php _e('Haz clic derecho en el calendario', 'reservas'); ?></li>
            <li><?php _e('Selecciona "Compartir calendario"', 'reservas'); ?></li>
            <li><?php _e('Habilita "Calendario público"', 'reservas'); ?></li>
            <li><?php _e('Copia la URL del calendario', 'reservas'); ?></li>
        </ol>
    </div>
</div> 