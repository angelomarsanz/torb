"use strict";

$(function() {
    const container = $('.formulario-de-pasos');
    if (container.length) {
        const currentStep = container.data('step');
        console.log('Cargando validaciones para el paso:', currentStep);

        switch (currentStep) {
            case 'descripcion':
                $('#list_des').validate({
                    rules: {
                        titulo: { required: true, maxlength: 255 },
                        descripcion: { required: true, minlength: 20 }
                    },
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        return true;
                    }
                });
                break;

            case 'fotos':
                $('#img_form').validate({
                    rules: {
                        'photos[]': {
                            required: function() {
                                // Solo es requerido si no hay fotos previas en la lista
                                return $('.photo-item').length === 0;
                            },
                            extension: "jpg|jpeg|png|gif"
                        }
                    },
                    messages: {
                        'photos[]': {
                            required: "Por favor, sube al menos una foto.",
                            extension: "Solo se permiten imágenes (jpg, jpeg, png, gif)."
                        }
                    },
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text("Subiendo...");
                        return true;
                    }
                });
                break;    
                        
            // ... resto de los pasos
        }
    }
});