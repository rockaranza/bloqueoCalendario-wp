jQuery(document).ready(function($) {
    // Inicializar el calendario
    if ($('#calendar').length) {
        initCalendar();
    }

    // Validación de fechas en el formulario de bloqueo
    $('form').on('submit', function(e) {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();

        if (fechaInicio && fechaFin) {
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);

            if (fin < inicio) {
                e.preventDefault();
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                return false;
            }
        }
    });

    // Función para inicializar el calendario
    function initCalendar() {
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            defaultView: 'month',
            editable: false,
            eventLimit: true,
            events: function(start, end, timezone, callback) {
                $.ajax({
                    url: reservas_ajax.ajax_url,
                    dataType: 'json',
                    data: {
                        action: 'reservas_get_events',
                        cabana_id: reservas_ajax.cabana_id,
                        start: start.format(),
                        end: end.format(),
                        nonce: reservas_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            callback(response.data);
                        }
                    }
                });
            },
            eventRender: function(event, element) {
                if (event.type === 'blocked') {
                    element.addClass('blocked');
                    element.attr('title', event.motivo || 'Fecha bloqueada');
                }
            },
            dayClick: function(date, jsEvent, view) {
                // Si se hace clic en un día, establecer la fecha de inicio
                $('#fecha_inicio').val(date.format('YYYY-MM-DD'));
            }
        });
    }

    // Manejar la eliminación de bloqueos
    $('.delete-bloqueo').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('¿Estás seguro de que deseas eliminar este bloqueo?')) {
            const bloqueoId = $(this).data('id');
            
            $.ajax({
                url: reservas_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'reservas_delete_bloqueo',
                    bloqueo_id: bloqueoId,
                    nonce: reservas_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error al eliminar el bloqueo');
                    }
                }
            });
        }
    });

    // Manejar la actualización del estado de la cabaña
    $('.update-estado').on('change', function() {
        const cabanaId = $(this).data('id');
        const estado = $(this).val();
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'reservas_update_estado',
                cabana_id: cabanaId,
                estado: estado,
                nonce: reservas_ajax.nonce
            },
            success: function(response) {
                if (!response.success) {
                    alert('Error al actualizar el estado');
                }
            }
        });
    });
}); 