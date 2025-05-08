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
        events: reservas_events,
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

    // Inicializar el datepicker para los campos de fecha
    var blockedDates = reservas_events;
    var blockedDatesArray = blockedDates
        .filter(event => event.extendedProps.type === 'blocked')
        .map(event => ({
            start: new Date(event.start),
            end: new Date(event.end)
        }));

    function isDateBlocked(date) {
        return blockedDatesArray.some(blocked => {
            return date >= blocked.start && date <= blocked.end;
        });
    }

    // Configuración del datepicker
    var datepickerOptions = {
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        beforeShowDay: function(date) {
            var isBlocked = isDateBlocked(date);
            return [!isBlocked, isBlocked ? 'blocked' : ''];
        },
        onSelect: function(dateText, inst) {
            var inputId = inst.id;
            var otherInputId = inputId === 'fecha_inicio' ? 'fecha_fin' : 'fecha_inicio';
            var otherDate = $('#' + otherInputId).datepicker('getDate');
            
            if (inputId === 'fecha_inicio' && otherDate && new Date(dateText) > otherDate) {
                $('#' + otherInputId).val('');
            } else if (inputId === 'fecha_fin' && otherDate && new Date(dateText) < otherDate) {
                $('#' + otherInputId).val('');
            }
            
            $('.form-messages').hide();
        },
        showOn: 'both',
        buttonImage: '',
        buttonImageOnly: false,
        buttonText: 'Seleccionar fecha',
        changeMonth: true,
        changeYear: true,
        yearRange: 'c-10:c+10',
        showButtonPanel: true,
        closeText: 'Cerrar',
        currentText: 'Hoy',
        prevText: 'Anterior',
        nextText: 'Siguiente',
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
        firstDay: 1
    };

    // Inicializar datepicker en los campos
    $('#fecha_inicio').datepicker(datepickerOptions);
    $('#fecha_fin').datepicker(datepickerOptions);

    // Manejar el envío del formulario
    $('#reservas-form').on('submit', function(e) {
        e.preventDefault();
        
        // Verificar si se han seleccionado fechas
        if (!$('#fecha_inicio').val() || !$('#fecha_fin').val()) {
            $('.form-messages').removeClass('success').addClass('error')
                .html(reservas_ajax.messages.select_dates)
                .show();
            return;
        }
        
        var fechaInicio = new Date($('#fecha_inicio').val());
        var fechaFin = new Date($('#fecha_fin').val());
        
        // Verificar si las fechas seleccionadas están bloqueadas
        var isBlocked = blockedDatesArray.some(blocked => {
            return (fechaInicio >= blocked.start && fechaInicio <= blocked.end) ||
                   (fechaFin >= blocked.start && fechaFin <= blocked.end);
        });
        
        if (isBlocked) {
            $('.form-messages').removeClass('success').addClass('error')
                .html(reservas_ajax.messages.dates_blocked)
                .show();
            return;
        }
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=reservas_submit_request',
            success: function(response) {
                if (response.success) {
                    $('.form-messages').removeClass('error').addClass('success')
                        .html(reservas_ajax.messages.request_sent)
                        .show();
                    $('#reservas-form')[0].reset();
                    calendar.refetchEvents();
                } else {
                    $('.form-messages').removeClass('success').addClass('error')
                        .html(response.data.message || reservas_ajax.messages.request_error)
                        .show();
                }
            }
        });
    });
}); 