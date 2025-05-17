<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="reservas-modal" class="reservas-modal">
    <div class="reservas-modal-content">
        <span class="reservas-modal-close">&times;</span>
        <h3><?php _e('Solicitar Reserva', 'reservas'); ?></h3>
        
        <form id="reservas-form" method="post">
            <input type="hidden" name="cabana_id" value="<?php echo esc_attr($cabana_id); ?>">
            <input type="hidden" name="action" value="reservas_submit_request">
            <?php wp_nonce_field('reservas_nonce', 'reservas_nonce'); ?>
            
            <div class="form-group">
                <label for="reservas-fecha-inicio"><?php _e('Fecha de Entrada:', 'reservas'); ?></label>
                <input type="date" id="reservas-fecha-inicio" name="fecha_inicio" required readonly>
            </div>
            
            <div class="form-group">
                <label for="reservas-fecha-fin"><?php _e('Fecha de Salida:', 'reservas'); ?></label>
                <input type="date" id="reservas-fecha-fin" name="fecha_fin" required readonly>
            </div>
            
            <div class="form-group">
                <label for="reservas-nombre"><?php _e('Nombre:', 'reservas'); ?></label>
                <input type="text" id="reservas-nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="reservas-email"><?php _e('Email:', 'reservas'); ?></label>
                <input type="email" id="reservas-email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="reservas-telefono"><?php _e('Teléfono:', 'reservas'); ?></label>
                <input type="tel" id="reservas-telefono" name="telefono" required>
            </div>
            
            <div class="form-group">
                <label for="reservas-comentarios"><?php _e('Comentarios:', 'reservas'); ?></label>
                <textarea id="reservas-comentarios" name="comentarios"></textarea>
            </div>
            
            <div class="form-messages"></div>
            
            <button type="submit" class="reservas-submit"><?php _e('Enviar Solicitud', 'reservas'); ?></button>
        </form>
    </div>
</div>

<style>
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

.form-group input,
.form-group textarea {
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

.reservas-submit {
    background: #0073aa;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    font-size: 1.1em;
}

.reservas-submit:hover {
    background: #005177;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Cerrar modal
    $('.reservas-modal-close').click(function() {
        $('#reservas-modal').hide();
    });
    
    // Cerrar modal al hacer clic fuera
    $(window).click(function(e) {
        if ($(e.target).is('#reservas-modal')) {
            $('#reservas-modal').hide();
        }
    });
    
    // Enviar formulario
    $('#reservas-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('.form-messages').removeClass('error').addClass('success')
                        .text('<?php _e('Solicitud enviada correctamente. Nos pondremos en contacto con usted pronto.', 'reservas'); ?>')
                        .show();
                    $('#reservas-form')[0].reset();
                    $('#reservas-modal').hide();
                    // Recargar eventos del calendario
                    calendar.refetchEvents();
                } else {
                    $('.form-messages').removeClass('success').addClass('error')
                        .text(response.data.message || '<?php _e('Error al enviar la solicitud. Por favor, inténtelo de nuevo.', 'reservas'); ?>')
                        .show();
                }
            }
        });
    });
});
</script> 