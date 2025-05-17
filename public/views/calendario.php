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
    <!-- Formulario de reserva -->
    <div class="reservas-form-container">
        <h2><?php printf(__('Reservar %s', 'reservas'), esc_html($cabana->nombre)); ?></h2>
        
        <form id="form-reserva-<?php echo esc_attr($cabana->id); ?>" class="reservas-form">
            <input type="hidden" name="cabana_id" value="<?php echo esc_attr($cabana->id); ?>">
            
            <div class="form-group">
                <label for="fecha_inicio"><?php _e('Fecha de llegada', 'reservas'); ?> *</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" required>
            </div>
            
            <div class="form-group">
                <label for="fecha_fin"><?php _e('Fecha de salida', 'reservas'); ?> *</label>
                <input type="date" name="fecha_fin" id="fecha_fin" required>
            </div>
            
            <div class="form-group">
                <label for="nombre"><?php _e('Nombre', 'reservas'); ?> *</label>
                <input type="text" name="nombre" id="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="email"><?php _e('Email', 'reservas'); ?> *</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="telefono"><?php _e('Teléfono', 'reservas'); ?></label>
                <input type="tel" name="telefono" id="telefono">
            </div>
            
            <div class="form-group">
                <button type="submit" class="button button-primary"><?php _e('Enviar Reserva', 'reservas'); ?></button>
            </div>
        </form>
    </div>

    <!-- Calendario -->
    <div class="reservas-calendario-container">
        <div id="calendario-<?php echo esc_attr($cabana->id); ?>" class="reservas-calendario"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var calendarEl = document.getElementById('calendario-<?php echo esc_attr($cabana->id); ?>');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: function(info, successCallback, failureCallback) {
            $.ajax({
                url: reservas_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'obtener_eventos',
                    nonce: reservas_ajax.nonce,
                    cabana_id: <?php echo esc_attr($cabana->id); ?>,
                    start: info.startStr,
                    end: info.endStr
                },
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        failureCallback(response.data);
                    }
                }
            });
        }
    });
    calendar.render();

    // Enviar formulario de reserva
    $('#form-reserva-<?php echo esc_attr($cabana->id); ?>').submit(function(e) {
        e.preventDefault();
        
        var fechaInicio = $('#fecha_inicio').val();
        var fechaFin = $('#fecha_fin').val();
        
        if (!fechaInicio || !fechaFin) {
            alert('<?php _e('Por favor, selecciona las fechas de llegada y salida.', 'reservas'); ?>');
            return;
        }
        
        // Verificar disponibilidad
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'verificar_fechas',
                nonce: reservas_ajax.nonce,
                cabana_id: <?php echo esc_attr($cabana->id); ?>,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            },
            success: function(response) {
                if (response.success) {
                    // Si las fechas están disponibles, enviar la reserva
                    $.ajax({
                        url: reservas_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'enviar_reserva',
                            nonce: reservas_ajax.nonce,
                            formData: $(e.target).serialize()
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.data.mensaje);
                                e.target.reset();
                                calendar.refetchEvents();
                            } else {
                                alert(response.data.mensaje);
                            }
                        }
                    });
                } else {
                    alert(response.data.mensaje);
                }
            }
        });
    });
});
</script>

<style>
.reservas-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    display: flex;
    gap: 40px;
}

.reservas-form-container {
    flex: 0 0 300px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.reservas-calendario-container {
    flex: 1;
}

.reservas-calendario {
    background: white;
    padding: 20px;
    border-radius: 8px;
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

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
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

@media (max-width: 768px) {
    .reservas-container {
        flex-direction: column;
    }
    
    .reservas-form-container {
        flex: none;
        width: 100%;
    }
}
</style> 