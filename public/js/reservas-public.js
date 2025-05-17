jQuery(document).ready(function($) {
    // Debug para verificar los bloqueos recibidos
    console.log('Bloqueos recibidos:', reservas_ajax.bloqueos);
    
    // Convertir las fechas bloqueadas a objetos Date
    var blockedDatesArray = reservas_ajax.bloqueos.map(function(bloqueo) {
        return {
            start: new Date(bloqueo.start),
            end: new Date(bloqueo.end)
        };
    });
    
    console.log('Fechas bloqueadas procesadas:', blockedDatesArray);

    function isDateBlocked(date) {
        var checkDate = new Date(date);
        checkDate.setHours(0, 0, 0, 0);
        
        return blockedDatesArray.some(function(bloqueo) {
            var start = new Date(bloqueo.start);
            start.setHours(0, 0, 0, 0);
            var end = new Date(bloqueo.end);
            end.setHours(0, 0, 0, 0);
            
            return checkDate >= start && checkDate <= end;
        });
    }

    // Configuración del datepicker
    var datepickerOptions = {
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        beforeShowDay: function(date) {
            var isBlocked = isDateBlocked(date);
            return [!isBlocked, isBlocked ? 'ui-datepicker-blocked' : ''];
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
            
            validateDates();
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

    function validateDates() {
        var fechaInicio = $('#fecha_inicio').val();
        var fechaFin = $('#fecha_fin').val();
        var isValid = true;
        
        if (fechaInicio && fechaFin) {
            var inicio = new Date(fechaInicio);
            var fin = new Date(fechaFin);
            inicio.setHours(0, 0, 0, 0);
            fin.setHours(0, 0, 0, 0);
            
            if (fin < inicio) {
                $('#fecha_fin_error').text('La fecha de salida debe ser posterior a la fecha de entrada').show();
                isValid = false;
            } else {
                $('#fecha_fin_error').hide();
            }
            
            // Verificar fechas bloqueadas
            var currentDate = new Date(inicio);
            while (currentDate <= fin) {
                if (isDateBlocked(currentDate)) {
                    $('.form-messages').removeClass('success').addClass('error')
                        .text('Las fechas seleccionadas incluyen días bloqueados')
                        .show();
                    isValid = false;
                    break;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }
        }
        
        return isValid;
    }

    // Manejar el envío del formulario
    $('#reservas-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateDates()) {
            return;
        }
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('.form-messages').removeClass('error').addClass('success')
                        .text(reservas_ajax.messages.request_sent)
                        .show();
                    $('#reservas-form')[0].reset();
                } else {
                    $('.form-messages').removeClass('success').addClass('error')
                        .text(response.data.message || reservas_ajax.messages.request_error)
                        .show();
                }
            }
        });
    });
}); 