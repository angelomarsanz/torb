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
                // 1. Inicializar el validador
                const validadorActividades = $('#list_des').validate({
                    errorPlacement: function(error, element) {
                        error.addClass('text-danger small font-weight-bold');
                        error.insertAfter(element);
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                    },
                    submitHandler: function(form) {
                        let tieneErroresDeFoto = false;
                        
                        // Limpiar errores previos de fotos
                        $('.error-foto-js').remove();
                        $('.actividad-foto-container').css('border-color', '');
            
                        // 2. VALIDACIÓN MANUAL DE FOTOS (Fila por fila)
                        // Recorremos solo las filas que tienen inputs de actividades
                        $('table tbody tr').each(function() {
                            const fila = $(this);
                            
                            // Ignorar la fila del botón "Agregar nueva actividad"
                            if (fila.hasClass('no-validar')) return;
            
                            // Verificar si existe una imagen dentro del contenedor
                            const contenedor = fila.find('.actividad-foto-container');
                            const tieneImagen = contenedor.find('img').length > 0;
            
                            if (!tieneImagen) {
                                tieneErroresDeFoto = true;
                                contenedor.css('border-color', '#dc3545');
                                // Insertar el mensaje de error justo después del contenedor de la foto
                                contenedor.after('<div class="text-danger error-foto-js mt-1" style="font-size: 11px; font-weight: 700;"><i class="fa fa-exclamation-circle"></i> La foto es obligatoria</div>');
                            }
                        });
            
                        // 3. Si falta alguna foto, detener el envío y hacer scroll al primer error
                        if (tieneErroresDeFoto) {
                            $('html, body').animate({ 
                                scrollTop: ($('.error-foto-js').first().offset().top - 150) 
                            }, 500);
                            return false; // Bloquea el envío del formulario
                        }
            
                        // 4. Si todo está bien, proceder con el envío
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text("Guardando...");
                        form.submit(); // Usar form.submit() explícito
                    }
                });
            
                // 5. Aplicar reglas a los campos de texto y número dinámicamente
                function aplicarReglasDinamicas() {
                    $('input[name*="[orden_actividad]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            min: 1,
                            messages: {
                                required: "Nro. requerido",
                                min: "Mínimo 1"
                            }
                        });
                    });
            
                    $('textarea[name*="[descripcion_actividad]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            minlength: 5,
                            messages: {
                                required: "Falta descripción",
                                minlength: "La descripción es muy corta"
                            }
                        });
                    });
                }
            
                aplicarReglasDinamicas();
                break;

            // ... resto de los pasos
        }
    }
});