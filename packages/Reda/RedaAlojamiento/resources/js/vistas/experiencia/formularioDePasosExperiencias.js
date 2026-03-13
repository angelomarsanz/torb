"use strict";

$(function() {
    const container = $('.formulario-de-pasos-experiencias');
    if (container.length) {
        const currentStep = container.data('step');

        switch (currentStep) {
            case 'descripcion':
                
                $('#list_des').validate({
                    rules: {
                        titulo: { required: true, minlength: 5 },
                        descripcion: { required: true, minlength: 20 }
                    },
                    messages: {
                        titulo: 
                        { 
                            required: window.RedaAlojamiento.general.el_nombre_del_negocio_es_obligatorio,
                            minlength: window.RedaAlojamiento.general.el_nombre_del_negocio_debe_tener_al_menos_5_caracteres
                        },
                        descripcion: 
                        {
                            required: window.RedaAlojamiento.general.la_descripcion_es_obligatoria,
                            minlength: window.RedaAlojamiento.general.la_descripcion_debe_tener_al_menos_20_caracteres 
                        }
                    },
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text(window.RedaAlojamiento.general.guardando);
                        return true;
                    }
                });
                
                break;

            case 'fotos':
                $('#img_form').on('submit', function() {
                    $("#btn_next").attr("disabled", true);
                    $(".spinner").removeClass('d-none');
                    $("#btn_next-text").text(window.RedaAlojamiento.general.continuando);
                });

                document.addEventListener('mediaUpdated', function(e) {
                    location.reload();
                });

                break;    

            case 'actividades':
                const validator = $('#list_des').validate({
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text(window.RedaAlojamiento.general.guardando);
                        return true;
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
                                required: window.RedaAlojamiento.general.el_numero_de_la_actividad_es_obligatorio,
                                number: window.RedaAlojamiento.general.el_numero_de_la_actividad_debe_ser_un_numero_valido,
                                min: window.RedaAlojamiento.general.el_numero_de_la_actividad_debe_ser_mayor_a_cero
                            }
                        });
                    });

                    // REGLA ADICIONAL: Nombre de la actividad (que también es required en tu Blade)
                    $('input[name*="[nombre_actividad]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            minlength: 3,
                            messages: {
                                required: window.RedaAlojamiento.general.el_nombre_del_producto_o_servicio_es_obligatorio,
                                minlength: window.RedaAlojamiento.general.el_nombre_del_producto_o_servicio_debe_tener_al_menos_3_caracteres
                            }
                        });
                    });

                    $('textarea[name*="[descripcion_actividad]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            minlength: 20,
                            messages: {
                                required: window.RedaAlojamiento.general.la_descripcion_es_obligatoria,
                                minlength: window.RedaAlojamiento.general.la_descripcion_debe_tener_al_menos_20_caracteres
                            }
                        });
                    });

                    $('.validar-precio').each(function() {
                        $(this).rules('add', {
                            required: true,
                            number: true,
                            min: 0.01,
                            messages: {
                                required: window.RedaAlojamiento.general.el_precio_es_obligatorio,
                                number: window.RedaAlojamiento.general.el_precio_debe_ser_un_numero_valido,
                                min: window.RedaAlojamiento.general.el_precio_debe_ser_mayor_a_cero
                            }
                        });
                    });

                    $('input[name*="[foto_actividad]"]').each(function() {
                        const inputFoto = $(this);
                        // Buscamos el contenedor de la card de esta fila específica
                        const contenedor = inputFoto.closest('.actividad-foto-card-container');
                    
                        inputFoto.rules('add', {
                            // REGLA DE PRESENCIA UNIFORME
                            required: function() {
                                // Es requerido SOLO SI no hay una imagen (img) visible en su contenedor
                                return contenedor.find('img').length === 0;
                            },
                            // REGLA DE FORMATO
                            extension: "jpg|jpeg|png|gif",
                            messages: {
                                required: window.RedaAlojamiento.general.la_foto_es_obligatoria,
                                extension: window.RedaAlojamiento.general.solo_se_permiten_imagenes_jpg_jpeg_png_gif
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
                
                    if (confirm(window.RedaAlojamiento.general.estas_seguro_de_que_deseas_eliminar_esta_actividad_esta_accion_no_se_puede_deshacer)) {
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
                                alert(window.RedaAlojamiento.general.ocurrio_un_error_al_intentar_eliminar_la_actividad);
                            }
                        });
                    }
                });

                break;

            // ... resto de los pasos
        }
    }
});