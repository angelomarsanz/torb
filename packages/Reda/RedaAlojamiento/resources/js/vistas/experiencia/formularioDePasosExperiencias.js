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
                    messages: {
                        titulo: { required: window.RedaTrans.validations.titulo_required },
                        descripcion: { minlength: window.RedaTrans.validations.descripcion_min }
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

                document.addEventListener('mediaUpdated', function(e) {
                    location.reload();
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
                        $('.actividad-foto-card-container').css('border-color', '');
                                    
                        // VALIDACIÓN MANUAL DE FOTOS (Usando la clase de tus cards)
                        $('.fila-actividad-container').each(function() {
                            const fila = $(this);
                            
                            // Buscamos el contenedor de la foto según tu Blade
                            const contenedor = fila.find('.actividad-foto-card-container');
                            const tieneImagen = contenedor.find('img').length > 0;

                            if (!tieneImagen) {
                                tieneErroresDeFoto = true;
                                contenedor.css('border', '2px solid #dc3545');
                                // Insertar el mensaje de error
                                contenedor.after('<div class="text-danger error-foto-js mt-1" style="font-size: 13px; font-weight: 700;"><i class="fa fa-exclamation-circle"></i> La foto es obligatoria</div>');
                            }
                        });
            
                        if (tieneErroresDeFoto) {
                            $('html, body').animate({ 
                                scrollTop: ($('.error-foto-js').first().offset().top - 150) 
                            }, 500);
                            return false; 
                        }
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text("Guardando...");
                        form.submit();
                    }
                });
            
                function aplicarReglasDinamicas() {            
                    // Validación para ORDEN
                    $('input[name*="[orden_actividad]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            number: true,
                            min: 1,
                            messages: {
                                required: "Obligatorio",
                                number: "Solo números",
                                min: "Mínimo 1"
                            }
                        });
                    });

                    // REGLA ADICIONAL: Nombre de la actividad (que también es required en tu Blade)
                    $('input[name*="[nombre_experiencia]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            messages: {
                                required: "El nombre es obligatorio"
                            }
                        });
                    });

                    $('textarea[name*="[descripcion_actividad]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            minlength: 20,
                            messages: {
                                required: "La descripción es obligatoria xxx",
                                minlength: window.RedaTrans.validations.descripcion_min
                            }
                        });
                    });

                    $('.validar-precio').each(function() {
                        $(this).rules('add', {
                            required: true,
                            number: true,
                            min: 0.01,
                            messages: {
                                required: "Ingrese un precio",
                                number: "Formato inválido",
                                min: "Debe ser mayor a 0"
                            }
                        });
                    });
                }

                aplicarReglasDinamicas();

                document.addEventListener('mediaUpdated', function(e) {
                    if (e.detail.origen === 'actividades-experiencias') {
                        const data = e.detail.response;
                        
                        // Usamos el ID que viene del controlador para mayor precisión
                        const actividadId = data.id; 
                        const nuevaUrl = data.path; // Usamos 'path' que es lo que envía tu controlador
                        
                        // Buscamos el contenedor específico de esa actividad
                        // Buscamos el input que tiene el data-id igual al que devolvió el servidor
                        const container = $(`.upload_photos[data-id="${actividadId}"]`).closest('.actividad-foto-card-container');
                        
                        if (nuevaUrl && container.length) {
                            // Actualizamos solo el contenido interno del contenedor
                            container.html(`
                                <img src="${nuevaUrl}?v=${new Date().getTime()}" alt="Foto">
                
                                <label class="edit-photo-overlay-outline" for="file-${actividadId}" title="Cambiar imagen">
                                    <i class="fa fa-pencil-alt"></i>
                                </label>
                                
                                <input id="file-${actividadId}" 
                                       type="file" 
                                       name="actividades[${actividadId}][foto_actividad]" 
                                       data-id="${actividadId}" 
                                       class="upload_photos" 
                                       accept="image/*" 
                                       style="display:none;">
                            `);
                            
                            // Quitamos la clase de placeholder si existía (en caso de ser la primera foto)
                            container.removeClass('no-image');
                            
                            // Limpiamos los mensajes de error de validación previos si los había
                            container.css('border-color', '');
                            container.siblings('.error-foto-js').remove();
                        }
                    }
                });
                
                $('#btn-add-actividad').on('click', function(e) {
                    e.preventDefault();
                    
                    // Obtenemos la URL del atributo data-url que pusimos en el botón
                    const url = $(this).data('url');
                    const btn = $(this);
                
                    btn.prop('disabled', true).css('opacity', '0.5');
                
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            // Enviamos el token CSRF que Laravel necesita para el POST
                            _token: $('input[name="_token"]').val() 
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#actividades-wrapper').append(response.html);
                                aplicarReglasDinamicas();
                                
                                // Efecto visual de entrada
                                const nuevaCard = $('.fila-actividad-container').last();
                                nuevaCard.hide().fadeIn(800);
                                
                                // Scroll suave hacia la nueva card
                                $('html, body').animate({
                                    scrollTop: nuevaCard.offset().top - 100
                                }, 500);
                            }
                        },
                        error: function(jqXHR) {
                            console.error(jqXHR.responseText);
                            alert('Error al agregar la actividad.');
                        },
                        complete: function() {
                            btn.prop('disabled', false).css('opacity', '1');
                        }
                    });
                });

                $(document).on('click', '.btn-delete-actividad-simple', function() {
                    let id = $(this).data('id');
                    let url = $(this).data('url');
                    let fila = $(`#fila-actividad-${id}`);
                
                    if (confirm('¿Estás seguro de que deseas eliminar esta actividad? Esta acción no se puede deshacer.')) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: $('input[name="_token"]').val()
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Animación de desvanecimiento y remoción de la fila
                                    fila.fadeOut(400, function() {
                                        $(this).remove();
                                    });
                                    // Mensaje de éxito (puedes usar un toast o alert)
                                    alert(response.message);
                                }
                            },
                            error: function() {
                                alert('Ocurrió un error al intentar eliminar la actividad.');
                            }
                        });
                    }
                });

                break;

            // ... resto de los pasos
        }
    }
});