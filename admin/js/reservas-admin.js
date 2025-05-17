jQuery(document).ready(function($) {
    // Inicializar datepicker
    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
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
    });
    
    // Manejar el formulario de bloqueo
    $('#bloqueo_form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Manejar eliminación de bloqueos
    $('.delete-bloqueo').on('click', function() {
        var id = $(this).data('id');
        
        if (confirm(reservas_ajax.messages.confirm_delete)) {
            $.ajax({
                url: reservas_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'reservas_delete_block',
                    nonce: reservas_ajax.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(reservas_ajax.messages.delete_error);
                    }
                }
            });
        }
    });
}); 