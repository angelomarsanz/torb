"use strict";

$(function() {
    // Verificar si el div específico de esta vista existe en la página.
    const containerId = '#create_experiencia';
    if ($(containerId).length) {
        // Validación del Formulario
        $('#list_experience').validate({
            rules: {
                titulo: {
                    required: true,
                    minlength: 5
                },
            },
            submitHandler: function(form)
            {
                $("#btn_next").attr("disabled", true);
                $(".spinner").removeClass('d-none');
                $("#btn_next-text").text(window.RedaAlojamiento.general.guardando);
                return true;
            },
            messages: {
                titulo: {
                    required: window.RedaAlojamiento.general.el_nombre_del_negocio_es_obligatorio,
                    minlength: window.RedaAlojamiento.general.el_nombre_del_negocio_debe_tener_al_menos_5_caracteres
                },
            },
            errorElement: 'p',
            errorClass: 'error-tag',
        });
    }
});