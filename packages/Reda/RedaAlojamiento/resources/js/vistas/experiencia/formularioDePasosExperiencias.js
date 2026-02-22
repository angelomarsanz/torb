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

                document.addEventListener('mediaUpdated', function(e) {
                    if (e.detail.origen === 'actividades-experiencias') {
                        const data = e.detail.response;
                        
                        // Usamos el ID que viene del controlador para mayor precisión
                        const actividadId = data.id; 
                        const nuevaUrl = data.path; // Usamos 'path' que es lo que envía tu controlador
                        
                        // Buscamos el contenedor específico de esa actividad
                        // Buscamos el input que tiene el data-id igual al que devolvió el servidor
                        const container = $(`.upload_photos[data-id="${actividadId}"]`).closest('.actividad-foto-container');
                        
                        if (nuevaUrl && container.length) {
                            // Actualizamos solo el contenido interno del contenedor
                            container.html(`
                                <img src="${nuevaUrl}?v=${new Date().getTime()}" alt="Foto">
                
                                <label class="edit-photo-overlay-outline" for="file-{{ $actividad->id }}" title="Cambiar imagen">
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
                            container.removeClass('placeholder-height');
                            
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
                                // Insertamos la fila
                                $('table tbody').append(response.html);
                
                                // Re-aplicamos validaciones a los nuevos campos
                                aplicarReglasDinamicas();
                
                                // Scroll suave
                                $('html, body').animate({
                                    scrollTop: $("table tbody tr:last").offset().top - 100
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

                break;

            // ... resto de los pasos
        }
    }
});