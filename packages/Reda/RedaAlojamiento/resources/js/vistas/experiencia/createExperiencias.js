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
            messages: {
                titulo: {
                    required: window.RedaAlojamientoJson["El nombre del negocio es obligatorio."] || "El nombre del negocio es obligatorio.",
                    minlength: window.RedaAlojamientoJson["El nombre del negocio debe tener al menos 5 caracteres."] || "El nombre del negocio debe tener al menos 5 caracteres."
                },
            }, 
            submitHandler: function(form)
            {
                $("#btn_next").attr("disabled", true);
                $(".spinner").removeClass('d-none');
                $("#btn_next-text").text(window.RedaAlojamientoJson["Guardando..."] || "Guardando...");
                return true;
            },
            errorElement: 'p',
            errorClass: 'error-tag',
        });
    }
});