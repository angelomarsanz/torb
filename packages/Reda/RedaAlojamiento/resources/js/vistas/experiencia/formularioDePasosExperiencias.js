"use strict";

$(function() {
    const container = $('.formulario-de-pasos-experiencias');
    if (container.length) {
        const currentStep = container.data('step');
        console.log('Cargando las validaciones para el paso:', currentStep);

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
                        $("#btn_next-text").text("Guardando...");
                        return true;
                    }
                });
                break;

            case 'fotos':
                // CONFIGURACIÓN DE VALIDACIÓN
                $('#img_form').validate({
                    rules: {
                        'photo': { // Cambiado de 'photos[]' a 'photo'
                            required: function() {
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

            case 'actividades':
                // Configuramos el validador de JQuery para los campos de texto
                $('#list_des').validate({
                    rules: {
                        // Aquí puedes agregar reglas para los campos de texto si lo deseas
                    },
                    submitHandler: function(form) {
                        let errorEncontrado = false;
                        
                        // 1. Limpiar mensajes de error previos
                        $('.error-foto-js').remove();
                        $('.actividad-foto-container').css('border-color', '#ced4da');
            
                        // 2. Recorrer cada fila de la tabla de actividades
                        $('table tbody tr:not(.no-validar)').each(function() {
                            const fila = $(this);
                            // Buscamos si existe la imagen dentro del contenedor
                            const tieneFoto = fila.find('.actividad-foto-container img').length > 0;
                            // Obtenemos el ID de la actividad para identificar la fila en el mensaje (opcional)
                            const orden = fila.find('input[type="number"]').val();
            
                            if (!tieneFoto) {
                                errorEncontrado = true;
                                const contenedorFoto = fila.find('.actividad-foto-container');
                                
                                // Resaltar el contenedor con error
                                contenedorFoto.css('border-color', '#dc3545');
                                
                                // Agregar mensaje de error visual debajo del contenedor
                                contenedorFoto.after('<div class="text-danger error-foto-js mt-1" style="font-size: 12px; font-weight: 700;"><i class="fa fa-exclamation-circle"></i> La actividad ' + orden + ' requiere una foto.</div>');
                            }
                        });
            
                        // 3. Si hay errores, detener el envío
                        if (errorEncontrado) {
                            // Hacemos scroll hacia el primer error encontrado
                            $('html, body').animate({
                                scrollTop: ($('.error-foto-js').first().offset().top - 150)
                            }, 500);
                            return false; 
                        }
            
                        // 4. Si todo está bien, mostrar spinner y enviar
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text("Guardando...");
                        return true; // Permite el envío del formulario
                    }
                });
                break;

            // ... resto de los pasos
        }
    }
});