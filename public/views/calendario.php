<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$cabana_id = isset($atts['cabana_id']) ? intval($atts['cabana_id']) : 0;

if (!$cabana_id) {
    echo '<div class="reservas-error">' . __('ID de cabaña no válido', 'reservas') . '</div>';
    return;
}

$cabana = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}reservas_cabanas WHERE id = %d",
    $cabana_id
));

if (!$cabana) {
    echo '<div class="reservas-error">' . __('Cabaña no encontrada', 'reservas') . '</div>';
    return;
}

// Obtener fechas bloqueadas
$bloqueos = $wpdb->get_results($wpdb->prepare(
    "SELECT fecha_inicio, fecha_fin FROM {$wpdb->prefix}reservas_bloqueos WHERE cabana_id = %d",
    $cabana_id
));

$bloqueos_json = json_encode($bloqueos);
?>

<div class="reservas-container">
    <div class="reservas-cabana-info">
        <h2><?php echo esc_html($cabana->nombre); ?></h2>
        <p><?php echo esc_html($cabana->descripcion); ?></p>
        <p><strong><?php _e('Capacidad:', 'reservas'); ?></strong> <?php echo esc_html($cabana->capacidad); ?></p>
        <p><strong><?php _e('Precio:', 'reservas'); ?></strong> <?php echo esc_html($cabana->precio); ?></p>
    </div>

    <div class="reservas-calendar-container">
        <div id="calendar"></div>
    </div>

    <div class="reservas-form-container">
        <h3><?php _e('Solicitar Reserva', 'reservas'); ?></h3>
        <form id="reservas-form" method="post">
            <input type="hidden" name="cabana_id" value="<?php echo esc_attr($cabana_id); ?>">
            <input type="hidden" name="action" value="reservas_submit_request">
            <?php wp_nonce_field('reservas_nonce', 'reservas_nonce'); ?>

            <div class="form-group">
                <label for="fecha_inicio"><?php _e('Fecha de Entrada:', 'reservas'); ?></label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="date-input" required min="<?php echo date('Y-m-d'); ?>">
                <div class="date-error" id="fecha_inicio_error"></div>
            </div>

            <div class="form-group">
                <label for="fecha_fin"><?php _e('Fecha de Salida:', 'reservas'); ?></label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="date-input" required min="<?php echo date('Y-m-d'); ?>">
                <div class="date-error" id="fecha_fin_error"></div>
            </div>

            <div class="form-group">
                <label for="nombre"><?php _e('Nombre:', 'reservas'); ?></label>
                <input type="text" id="nombre" name="nombre" required>
            </div>

            <div class="form-group">
                <label for="email"><?php _e('Email:', 'reservas'); ?></label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="telefono"><?php _e('Teléfono:', 'reservas'); ?></label>
                <input type="tel" id="telefono" name="telefono" required>
            </div>

            <div class="form-group">
                <label for="comentarios"><?php _e('Comentarios:', 'reservas'); ?></label>
                <textarea id="comentarios" name="comentarios"></textarea>
            </div>

            <div class="form-messages"></div>

            <button type="submit" class="reservas-submit"><?php _e('Enviar Solicitud', 'reservas'); ?></button>
        </form>
    </div>
</div>

<style>
.reservas-calendario-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.reservas-cabana-info {
    margin-bottom: 30px;
    padding: 20px;
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.reservas-calendario-wrapper {
    margin-top: 30px;
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#calendar {
    width: 100%;
    min-height: 600px;
}

.reservas-formulario {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

.date-input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #fff;
}

.date-error {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 5px;
    display: none;
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
    display: block;
}

.form-messages.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    display: block;
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

.fc-event {
    cursor: pointer;
}

.fc-event.blocked {
    background-color: #dc3545;
    border-color: #dc3545;
}

.fc-event.reserved {
    background-color: #28a745;
    border-color: #28a745;
}

/* Estilos del Datepicker */
.ui-datepicker {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 1000 !important;
}

.ui-datepicker-header {
    background: #0073aa;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 10px;
}

.ui-datepicker-prev,
.ui-datepicker-next {
    cursor: pointer;
    color: #fff;
}

.ui-datepicker-prev:hover,
.ui-datepicker-next:hover {
    color: #fff;
}

.ui-datepicker-calendar th {
    background: #f5f5f5;
    padding: 5px;
    text-align: center;
}

.ui-datepicker-calendar td {
    padding: 5px;
    text-align: center;
}

.ui-datepicker-calendar td a {
    display: block;
    padding: 5px;
    text-align: center;
    text-decoration: none;
    color: #333;
}

.ui-datepicker-calendar td a:hover {
    background: #0073aa;
    color: #fff;
    border-radius: 4px;
}

.ui-datepicker-calendar td.ui-datepicker-today a {
    background: #0073aa;
    color: #fff;
    border-radius: 4px;
}

.ui-datepicker-calendar td.ui-datepicker-current-day a {
    background: #005177;
    color: #fff;
    border-radius: 4px;
}

.ui-datepicker-calendar td.blocked a {
    color: #dc3545;
    text-decoration: line-through;
    cursor: not-allowed;
}

.ui-datepicker-calendar td.blocked a:hover {
    background: #f8d7da;
    color: #dc3545;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Inicializar el calendario de disponibilidad
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        initialView: 'dayGridMonth',
        selectable: true,
        events: <?php echo json_encode($events); ?>,
        eventDidMount: function(info) {
            if (info.event.extendedProps.type === 'blocked') {
                info.el.classList.add('blocked');
            } else if (info.event.extendedProps.type === 'reserved') {
                info.el.classList.add('reserved');
            }
        }
    });

    // Renderizar el calendario de disponibilidad
    calendar.render();

    var bloqueos = <?php echo $bloqueos_json; ?>;
    
    function isDateBlocked(date) {
        var fecha = new Date(date);
        return bloqueos.some(function(bloqueo) {
            var inicio = new Date(bloqueo.fecha_inicio);
            var fin = new Date(bloqueo.fecha_fin);
            return fecha >= inicio && fecha <= fin;
        });
    }
    
    function validateDates() {
        var fechaInicio = $('#fecha_inicio').val();
        var fechaFin = $('#fecha_fin').val();
        var isValid = true;
        
        if (fechaInicio && fechaFin) {
            var inicio = new Date(fechaInicio);
            var fin = new Date(fechaFin);
            
            if (fin < inicio) {
                $('#fecha_fin_error').text('<?php _e('La fecha de salida debe ser posterior a la fecha de entrada', 'reservas'); ?>').show();
                isValid = false;
            } else {
                $('#fecha_fin_error').hide();
            }
            
            // Verificar fechas bloqueadas
            var currentDate = new Date(inicio);
            while (currentDate <= fin) {
                if (isDateBlocked(currentDate)) {
                    $('.form-messages').removeClass('success').addClass('error')
                        .text('<?php _e('Las fechas seleccionadas incluyen días bloqueados', 'reservas'); ?>')
                        .show();
                    isValid = false;
                    break;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }
        }
        
        return isValid;
    }
    
    $('#fecha_inicio, #fecha_fin').on('change', function() {
        $('.form-messages').hide();
        validateDates();
    });
    
    $('#reservas-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateDates()) {
            return;
        }
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('.form-messages').removeClass('error').addClass('success')
                        .text('<?php _e('Solicitud enviada correctamente. Nos pondremos en contacto con usted pronto.', 'reservas'); ?>')
                        .show();
                    $('#reservas-form')[0].reset();
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