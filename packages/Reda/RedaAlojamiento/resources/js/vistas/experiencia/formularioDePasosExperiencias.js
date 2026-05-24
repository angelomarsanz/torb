"use strict";

$(function() {
    const container = $('.formulario-de-pasos-experiencias');
    if (container.length) {
        const currentStep = container.data('step');

        // Auto-scroll para el menú horizontal en móviles
        const activeStep = document.querySelector('.stepper-menu-container li.is-active');
        if (activeStep && window.innerWidth < 768) {
            setTimeout(() => {
                activeStep.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }, 300);
        }

        switch (currentStep) {
            case 'descripcion':

                if ($.isFunction($.fn.select2)) {
                    $('.select-search').select2({
                        placeholder: window.RedaAlojamiento.general.seleccione_una_opcion,
                        width: '100%'
                    });
                }

                $('#list_des').validate({
                    rules: {
                        titulo: { required: true, minlength: 5 },
                        descripcion: { required: true, minlength: 20 },
                        categoria_negocio: { required: true }, // Nueva regla
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
                        },
                        categoria_negocio: {
                            required: window.RedaAlojamiento.general.la_categoria_del_negocio_es_obligatoria // Nuevo mensaje
                        },
                    },
                    errorPlacement: function(error, element) {
                        // Manejo especial para que el error no quede oculto si usas select2
                        if (element.hasClass('select-search') && element.next('.select2-container').length) {
                            error.insertAfter(element.next('.select2-container'));
                        } else {
                            error.insertAfter(element);
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
                    ignore: [],
                    errorPlacement: function(error, element) {
                        // Si el elemento es el input de la foto (clase upload_photos)
                        if (element.hasClass('upload_photos')) {
                            // Buscamos el contenedor del marco y ponemos el error DESPUÉS de ese div
                            error.insertAfter(element.closest('.actividad-foto-card-container'));
                        } else if (element.parent('.input-group').length) {
                            error.insertAfter(element.parent());
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                    },
                    submitHandler: function(form) {
                        const stayOnStep = $('#stay_on_step').val() === '1';

                        if (stayOnStep) {
                            const $form = $(form);
                            const url = $form.attr('action');
                            const formData = new FormData(form);

                            $("#btn-save-new-producto").attr("disabled", true);
                            $(".spinner-save").removeClass('d-none');
                            $("#btn-save-new-producto-text").text(window.RedaAlojamientoJson["Guardando..."] || "Guardando...");

                            $.ajax({
                                url: url,
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        // Mostrar notificación de éxito
                                        $('#notificacion-icono').html('<i class="fa fa-check-circle fa-4x text-success"></i>');
                                        $('#notificacion-titulo').text(window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!");
                                        const mensajeExito = response.mensaje_usuario || window.RedaAlojamientoJson["Actividad agregada exitosamente"] || "Actividad agregada exitosamente";
                                        $('#notificacion-mensaje').text(mensajeExito);
                                        $('#modal-notificacion').modal('show');

                                        // Al cerrar el modal, refrescar la página para ver los cambios en la lista
                                        $('#modal-notificacion').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                                            const baseUrl = window.location.href.split('#')[0].split('?')[0];
                                            window.location.href = baseUrl + '?refresh=' + new Date().getTime() + '#seccion-productos-servicios';
                                        });
                                    } else {
                                        // Error reportado por el servidor
                                        $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                                        $('#notificacion-titulo').text(window.RedaAlojamientoJson["Error"] || "Error");
                                        $('#notificacion-mensaje').text(response.mensaje_usuario || response.message || window.RedaAlojamientoJson["Ocurrió un error al intentar guardar la actividad."] || "Ocurrió un error al intentar guardar la actividad.");
                                        $('#modal-notificacion').modal('show');

                                        // Reactivar botón
                                        $("#btn-save-new-producto").attr("disabled", false);
                                        $(".spinner-save").addClass('d-none');
                                        $("#btn-save-new-producto-text").text(window.RedaAlojamientoJson["Guardar actividad"] || "Guardar actividad");
                                    }
                                },
                                error: function(jqXHR) {
                                    console.error(jqXHR.responseText);
                                    let mensaje = window.RedaAlojamientoJson["Ocurrió un error al intentar guardar la actividad."] || "Ocurrió un error al intentar guardar la actividad.";
                                    
                                    try {
                                        const resp = JSON.parse(jqXHR.responseText);
                                        if (resp.mensaje_usuario) mensaje = resp.mensaje_usuario;
                                        else if (resp.message) mensaje = resp.message;
                                    } catch (e) {}

                                    $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                                    $('#notificacion-titulo').text(window.RedaAlojamientoJson["Error"] || "Error");
                                    $('#notificacion-mensaje').text(mensaje);
                                    $('#modal-notificacion').modal('show');

                                    // Reactivar botón
                                    $("#btn-save-new-producto").attr("disabled", false);
                                    $(".spinner-save").addClass('d-none');
                                    $("#btn-save-new-producto-text").text(window.RedaAlojamientoJson["Guardar actividad"] || "Guardar actividad");
                                }
                            });
                            return false; // Evitar el envío normal
                        }

                        // Comportamiento normal para el botón Siguiente
                        $("#btn_next, #btn-save-new-producto").attr("disabled", true);
                        $(".spinner, .spinner-save").removeClass('d-none');
                        $("#btn_next-text, #btn-save-new-producto-text").text(window.RedaAlojamiento.general.guardando);
                        return true;
                    }
                });

                let currentNewActivityId = null;

                // Reordenar actividades en la vista de escritorio
                const el = document.getElementById('actividades-sortable');
                if (el) {
                    Sortable.create(el, {
                        handle: '.cursor-move',
                        animation: 150,
                        onEnd: function() {
                            const orden = [];
                            $('#actividades-sortable tr').each(function() {
                                orden.push($(this).data('id'));
                            });

                            // Ajax para guardar el nuevo orden
                            $.post($(el).data('reorder-url'), {
                                _token: $('input[name="_token"]').val(),
                                orden: orden
                            }, function(response) {
                                if (response.success) {
                                    // Actualizar números visualmente
                                    $('.indice-actividad').each(function(index) {
                                        $(this).text(index + 1);
                                    });
                                    console.log('Orden actualizado');
                                }
                            });
                        }
                    });
                }

                // --- Inicialización para la Vista Móvil (Cards) ---
                let elCards = document.getElementById('sortable-cards-mobile');
                if (elCards) {
                    Sortable.create(elCards, {
                        animation: 150,
                        ghostClass: 'bg-light',
                        filter: '.btn, .btn *', // Evita que el arrastre se active al pulsar botones o sus iconos
                        preventOnFilter: false, // Permite que los eventos de clic en los botones sigan funcionando
                        delay: 200,             // Retraso de 200ms para diferenciar "scroll" de "arrastrar"
                        delayOnTouchOnly: true, // El retraso solo se aplica en dispositivos táctiles
                        onEnd: function () {
                            actualizarOrdenActividades();
                        }
                    });
                }

                // Función para procesar el orden en móvil
                function actualizarOrdenActividades() {
                    let orden = [];
                    let contenedor = $('#actividades-cards-container');
                    let urlRuta = contenedor.data('reorder-url');
                    $('#sortable-cards-mobile .card-actividad-movil').each(function(index) {
                        let id = $(this).data('id');
                        if(id) {
                            let nuevoOrden = index + 1;
                            orden.push({ id: id, orden: nuevoOrden });
                            $(this).find('.indice-actividad-movil').text(nuevoOrden);
                        }
                    });
                    enviarNuevoOrden(orden, urlRuta);
                }

                // Función AJAX para guardar el orden en la Base de Datos
                function enviarNuevoOrden(ordenArray, urlRuta) {
                    $.ajax({
                        url: urlRuta,
                        type: 'POST',
                        data: {
                            _token: $('input[name="_token"]').val(),
                            orden: ordenArray
                        },
                        success: function(response) {
                            if (!response.success) {
                                alert('No se pudo guardar el nuevo orden.');
                            }
                        },
                        error: function() {
                            console.error('Error de red al intentar reordenar.');
                        }
                    });
                }

                function aplicarReglasDinamicas() {
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

                    $('select[name*="[tipo_producto_servicio]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            messages: {
                                required: window.RedaAlojamiento.general.el_tipo_producto_o_servicio_es_obligatorio
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

                    // Moneda
                    $('select[name*="[currency_id]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            messages: {
                                required: window.RedaAlojamiento.general.el_tipo_de_moneda_es_obligatorio
                            }
                        });
                    });

                    // Disponibilidad
                    $('select[name*="[disponibilidad]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            messages: {
                                required: window.RedaAlojamiento.general.debe_seleccionar_si_esta_disponible_o_no
                            }
                        });
                    });

                    $('input[name*="[foto_actividad]"]').each(function() {
                        const inputFoto = $(this);
                        // Buscamos el contenedor específico que mencionaste
                        const contenedor = inputFoto.closest('.actividad-foto-card-container');

                        inputFoto.rules('add', {
                            required: function() {
                                // Es requerido SOLO SI NO hay una etiqueta <img> dentro del contenedor
                                // Esto detecta tanto fotos que ya venían de DB como las subidas por AJAX
                                return contenedor.find('img').length === 0;
                            },
                            messages: {
                                required: window.RedaAlojamiento.general.la_foto_es_obligatoria
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

                            // Buscamos el NUEVO input que acabamos de crear en el DOM
                            const nuevoInput = container.find(`input[name="actividades[${actividadId}][foto_actividad]"]`);

                            // Volvemos a aplicarle las reglas de validación (porque al borrar el HTML se perdieron)
                            nuevoInput.rules('add', {
                                required: function() {
                                    return container.find('img').length === 0;
                                },
                                messages: {
                                    required: window.RedaAlojamiento.general.la_foto_es_obligatoria
                                }
                            });

                            // Forzamos la validación. Como ahora SI hay una img, el error desaparecerá.
                            // Usamos un pequeño timeout para asegurar que el DOM esté listo
                            setTimeout(() => {
                                nuevoInput.valid();
                            }, 100);
                        }
                    }
                });

                $('#btn-add-actividad').on('click', function(e) {
                    e.preventDefault();

                    // Obtenemos la URL del atributo data-add-url que pusimos en el botón
                    const url = $(this).data('add-url');
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
                                currentNewActivityId = response.id;

                                // Ocultar lista y botón add
                                $('#productos-servicios-list-container').addClass('d-none');
                                btn.hide();

                                // Mostrar formulario y acciones nuevas
                                $('#actividades-wrapper').html(response.html);
                                $('#new-producto-actions').removeClass('d-none');

                                aplicarReglasDinamicas();

                                // Scroll suave hacia el inicio del formulario
                                $('html, body').animate({
                                    scrollTop: $('#actividades-wrapper').offset().top - 100
                                }, 500);
                            }
                        },
                        error: function(jqXHR) {
                            console.error(jqXHR.responseText);
                            // Mostrar notificación de error
                            $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                            $('#notificacion-titulo').text(window.RedaAlojamiento.general.error || 'Error');
                            $('#notificacion-mensaje').text(window.RedaAlojamiento.general.ocurrio_un_error_al_intentar_agregar_la_actividad || 'Ocurrió un error al intentar agregar la actividad.');
                            $('#modal-notificacion').modal('show');
                        },
                        complete: function() {
                            btn.prop('disabled', false).css('opacity', '1');
                        }
                    });
                });

                $('#btn-cancel-new-producto').on('click', function() {
                    if (currentNewActivityId) {
                        const deleteUrl = APP_URL + '/reda/experiencias/actividades/delete/' + currentNewActivityId;

                        $(this).prop('disabled', true);

                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            data: {
                                _token: $('input[name="_token"]').val()
                            },
                            success: function(response) {
                                // Forzamos una recarga completa con un parámetro aleatorio para limpiar caché/estado
                                const baseUrl = window.location.href.split('#')[0].split('?')[0];
                                window.location.href = baseUrl + '?refresh=' + new Date().getTime() + '#seccion-productos-servicios';
                            },
                            error: function() {
                                const baseUrl = window.location.href.split('#')[0].split('?')[0];
                                window.location.href = baseUrl + '?refresh=' + new Date().getTime() + '#seccion-productos-servicios';
                            }
                        });
                    } else {
                        const baseUrl = window.location.href.split('#')[0].split('?')[0];
                        window.location.href = baseUrl + '?refresh=' + new Date().getTime() + '#seccion-productos-servicios';
                    }
                });

                $('#btn-save-new-producto').on('click', function() {
                    // Marcamos que queremos quedarnos en el mismo paso
                    $('#stay_on_step').val('1');
                    // Disparamos el submit del formulario principal
                    $('#list_des').submit();
                });

                $(document).on('click', '.btn-delete-actividad', function() {
                    const btn = $(this);
                    const id = btn.data('delete-id');
                    const url = btn.data('delete-url');
                    const filas = $(`.fila-actividad-${id}`);

                    // Configurar el modal de confirmación
                    $('#confirmacion-mensaje').text(window.RedaAlojamiento.general.estas_seguro_de_que_deseas_eliminar_esta_actividad_esta_accion_no_se_puede_deshacer);
                    $('#modal-confirmacion').modal('show');

                    // Limpiar eventos previos del botón de confirmación
                    $('#btn-confirmar-si').off('click').on('click', function() {
                        const btnConfirmar = $(this);
                        btnConfirmar.prop('disabled', true);
                        btnConfirmar.find('.btn-text').addClass('d-none');
                        btnConfirmar.find('.fa-spinner').removeClass('d-none');

                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: $('input[name="_token"]').val()
                            },
                            success: function(response) {
                                if (response.success) {
                                    filas.fadeOut(400).promise().done(function() {
                                        filas.remove();
                                        $('#modal-confirmacion').modal('hide');

                                        // Mostrar notificación de éxito
                                        $('#notificacion-icono').html('<i class="fa fa-check-circle fa-4x text-success"></i>');
                                        $('#notificacion-titulo').text(window.RedaAlojamiento.general.exito || '¡Éxito!');
                                        $('#notificacion-mensaje').text(response.mensaje_usuario || response.message);
                                        $('#modal-notificacion').modal('show');
                                    });
                                } else {
                                    $('#modal-confirmacion').modal('hide');

                                    // Mostrar notificación de error
                                    $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                                    $('#notificacion-titulo').text(window.RedaAlojamiento.general.error || 'Error');
                                    $('#notificacion-mensaje').text(window.RedaAlojamiento.general.error || response.mensaje_usuario || response.message);
                                    $('#modal-notificacion').modal('show');
                                }
                            },
                            error: function() {
                                $('#modal-confirmacion').modal('hide');

                                // Mostrar notificación de error
                                $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                                $('#notificacion-titulo').text(window.RedaAlojamiento.general.error || 'Error');
                                $('#notificacion-mensaje').text(window.RedaAlojamiento.general.ocurrio_un_error_al_intentar_eliminar_la_actividad);
                                $('#modal-notificacion').modal('show');
                            },
                            complete: function() {
                                btnConfirmar.prop('disabled', false);
                                btnConfirmar.find('.btn-text').removeClass('d-none');
                                btnConfirmar.find('.fa-spinner').addClass('d-none');
                            }
                        });
                    });
                });

                break;

            // ... resto de los pasos
        }
    }
});
