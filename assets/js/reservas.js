jQuery(document).ready(function($) {
    // Manejar el envío del formulario
    $('.reservas-form').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitButton = form.find('.reservas-submit');
        const originalButtonText = submitButton.text();

        // Deshabilitar el botón y mostrar mensaje de carga
        submitButton.prop('disabled', true).text('Enviando...');

        // Obtener los datos del formulario
        const formData = {
            action: 'reservas_submit',
            nonce: reservas_ajax.nonce,
            nombre: form.find('#nombre').val(),
            email: form.find('#email').val(),
            fecha: form.find('#fecha').val(),
            hora: form.find('#hora').val()
        };

        // Enviar la solicitud AJAX
        $.ajax({
            url: reservas_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Mostrar mensaje de éxito
                    form.html('<div class="reservas-success">' + response.data.message + '</div>');
                } else {
                    // Mostrar mensaje de error
                    form.prepend('<div class="reservas-error">' + response.data.message + '</div>');
                    submitButton.prop('disabled', false).text(originalButtonText);
                }
            },
            error: function() {
                // Mostrar mensaje de error
                form.prepend('<div class="reservas-error">Error al enviar el formulario. Por favor, intente nuevamente.</div>');
                submitButton.prop('disabled', false).text(originalButtonText);
            }
        });
    });

    // Validación en tiempo real
    $('.reservas-form input').on('input', function() {
        const input = $(this);
        const value = input.val();
        let isValid = true;

        if (input.attr('type') === 'email') {
            isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        } else if (input.attr('type') === 'date') {
            const selectedDate = new Date(value);
            const today = new Date();
            isValid = selectedDate >= today;
        }

        if (isValid) {
            input.removeClass('error').addClass('valid');
        } else {
            input.removeClass('valid').addClass('error');
        }
    });
}); 