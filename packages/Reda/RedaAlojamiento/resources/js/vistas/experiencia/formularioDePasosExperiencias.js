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
                        placeholder: window.RedaAlojamientoJson["Seleccione una opción"] || "Seleccione una opción",
                        width: '100%'
                    });
                }

                $('#list_des').validate({
                    ignore: [],
                    rules: {
                        titulo: { required: true, minlength: 5 },
                        descripcion: { required: true, minlength: 20 },
                        categoria_negocio: { required: true },
                        logo_exists: { required: true }, // Nueva regla para el logo
                    },
                    messages: {
                        titulo:
                        {
                            required: window.RedaAlojamientoJson["El nombre del negocio es obligatorio."] || "El nombre del negocio es obligatorio.",
                            minlength: window.RedaAlojamientoJson["El nombre del negocio debe tener al menos 5 caracteres."] || "El nombre del negocio debe tener al menos 5 caracteres."
                        },
                        descripcion:
                        {
                            required: window.RedaAlojamientoJson["La descripción es obligatoria."] || "La descripción es obligatoria.",
                            minlength: window.RedaAlojamientoJson["La descripción debe tener al menos 20 caracteres."] || "La descripción debe tener al menos 20 caracteres."
                        },
                        categoria_negocio: {
                            required: window.RedaAlojamientoJson["La categoría del negocio es obligatoria."] || "La categoría del negocio es obligatoria."
                        },
                        logo_exists: {
                            required: window.RedaAlojamientoJson["El logo del negocio es obligatorio."] || "El logo del negocio es obligatorio."
                        }
                    },
                    errorPlacement: function(error, element) {
                        if (element.attr('name') === 'logo_exists') {
                            error.insertAfter('#foto-container-logo');
                        } else if (element.hasClass('select-search') && element.next('.select2-container').length) {
                            error.insertAfter(element.next('.select2-container'));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text(window.RedaAlojamientoJson["Guardando..."] || "Guardando...");
                        return true;
                    }
                });

                // Escuchamos el evento de actualización de media para el logo (dentro del caso descripcion)
                document.addEventListener('mediaUpdated', function(e) {
                    if (e.detail.origen === 'logo-negocio') {
                        // Marcamos que el logo existe para el validador
                        $('#logo_exists').val('1');
                        // Forzamos la re-validación del campo
                        $('#list_des').validate().element('#logo_exists');
                    }
                });

                break;

            case 'fotos':
                $('#img_form').on('submit', function() {
                    $("#btn_next").attr("disabled", true);
                    $(".spinner").removeClass('d-none');
                    $("#btn_next-text").text(window.RedaAlojamientoJson["Continuando..."] || "Continuando...");
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

                            guardarActividadAjax(url, formData).then(response => {
                                if (response.success) {
                                    // Mostrar notificación de éxito
                                    $('#notificacion-icono').html('<i class="fa fa-check-circle fa-4x text-success"></i>');
                                    $('#notificacion-titulo').text(window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!");
                                    $('#notificacion-mensaje').text(response.mensaje_usuario);
                                    $('#modal-notificacion').modal('show');

                                    // Al cerrar el modal, refrescar la página
                                    $('#modal-notificacion').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                                        const baseUrl = window.location.href.split('#')[0].split('?')[0];
                                        window.location.href = baseUrl + '?refresh=' + new Date().getTime() + '#seccion-productos-servicios';
                                    });
                                } else {
                                    // Error reportado por el servidor o red
                                    $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                                    $('#notificacion-titulo').text(window.RedaAlojamientoJson["Error"] || "Error");
                                    $('#notificacion-mensaje').text(response.mensaje_usuario);
                                    $('#modal-notificacion').modal('show');

                                    // Reactivar botón
                                    $("#btn-save-new-producto").attr("disabled", false);
                                    $(".spinner-save").addClass('d-none');
                                    $("#btn-save-new-producto-text").text(window.RedaAlojamientoJson["Guardar producto o servicio"] || "Guardar producto o servicio");
                                }
                            });
                            return false; // Evitar el envío normal
                        }

                        // Comportamiento normal para el botón Siguiente
                        $("#btn_next, #btn-save-new-producto").attr("disabled", true);
                        $(".spinner, .spinner-save").removeClass('d-none');
                        $("#btn_next-text, #btn-save-new-producto-text").text(window.RedaAlojamientoJson["Guardando..."] || "Guardando...");
                        return true;
                    }
                });

                let currentNewActivityId = null;
                let isEditingActividad = false;

                // --- Gestión de Historial para Navegación Móvil ---

                const restaurarVistaLista = () => {
                    const wrapper = $('#actividades-wrapper');
                    if (wrapper.children().length > 0) {
                        wrapper.empty();
                        $('#new-producto-actions').addClass('d-none');
                        $('#productos-servicios-list-container').removeClass('d-none');
                        $('#btn-add-actividad').show();

                        currentNewActivityId = null;
                        isEditingActividad = false;

                        $('html, body').animate({
                            scrollTop: $('#productos-servicios-list-container').offset().top - 100
                        }, 300);
                    }
                };

                $(window).on('popstate', function(event) {
                    // Si el usuario presiona "Atrás" en el celular y estamos en el formulario, restauramos la lista
                    restaurarVistaLista();
                });

                // --- Funciones AJAX con Estructura GEMINI.md ---

                const reordenarActividadesAjax = (orden, url) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: $('input[name="_token"]').val(),
                                orden: orden
                            },
                            success: (data) => resolve(data),
                            error: function (x, xs, xt) {
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                console.log('respuestaServidor', respuestaServidor);

                                const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                                const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                                let respuesta = {
                                    'success': false,
                                    'message' : window.RedaAlojamientoJson["Error al actualizar el orden"] || 'Error al actualizar el orden',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                    'respuesta': respuestaServidor.respuesta || '',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    });
                };

                const agregarActividadAjax = (url) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            data: { _token: $('input[name="_token"]').val() },
                            success: (data) => resolve(data),
                            error: function (x, xs, xt) {
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                console.log('respuestaServidor', respuestaServidor);

                                const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                                const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                                let respuesta = {
                                    'success': false,
                                    'message' : window.RedaAlojamientoJson["Error al intentar agregar la actividad"] || 'Error al intentar agregar la actividad',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                    'respuesta': respuestaServidor.respuesta || '',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    });
                };

                const obtenerFormularioActividadAjax = (url) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: url,
                            type: 'GET',
                            dataType: 'json',
                            success: (data) => resolve(data),
                            error: function (x, xs, xt) {
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                console.log('respuestaServidor', respuestaServidor);

                                const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                                const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                                let respuesta = {
                                    'success': false,
                                    'message' : window.RedaAlojamientoJson["Error al intentar recuperar el formulario"] || 'Error al intentar recuperar el formulario',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                    'respuesta': respuestaServidor.respuesta || '',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    });
                };

                const eliminarActividadAjax = (url) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: { _token: $('input[name="_token"]').val() },
                            success: (data) => resolve(data),
                            error: function (x, xs, xt) {
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                console.log('respuestaServidor', respuestaServidor);

                                const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                                const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                                let respuesta = {
                                    'success': false,
                                    'message' : window.RedaAlojamientoJson["Error al intentar eliminar la actividad"] || 'Error al intentar eliminar la actividad',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                    'respuesta': respuestaServidor.respuesta || '',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    });
                };

                const guardarActividadAjax = (url, formData) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: (data) => resolve(data),
                            error: function (x, xs, xt) {
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                console.log('respuestaServidor', respuestaServidor);

                                const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                                const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                                let respuesta = {
                                    'success': false,
                                    'message' : window.RedaAlojamientoJson["Error al intentar guardar la actividad"] || 'Error al intentar guardar la actividad',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                    'respuesta': respuestaServidor.respuesta || '',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    });
                };

                const actualizarPreciosLoteAjax = (datos) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: APP_URL + '/reda/negocios/experiencias/actividades/actualizar-precios-lote',
                            type: 'POST',
                            data: {
                                _token: $('input[name="_token"]').val(),
                                ...datos
                            },
                            success: (data) => resolve(data),
                            error: function (x, xs, xt) {
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                console.log('respuestaServidor', respuestaServidor);

                                const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                                const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                                let respuesta = {
                                    'success': false,
                                    'message' : window.RedaAlojamientoJson["Error al actualizar los precios en lote"] || 'Error al actualizar los precios en lote',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                    'respuesta': respuestaServidor.respuesta || '',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    });
                };

                // Reordenar actividades en la vista de escritorio
                const el = document.getElementById('actividades-sortable');
                if (el) {
                    Sortable.create(el, {
                        animation: 150,
                        ghostClass: 'bg-light',
                        filter: '.btn, .btn *, .custom-control, .custom-control *', // No arrastrar si se toca un botón o checkbox
                        preventOnFilter: false,
                        onEnd: async function() {
                            const orden = [];
                            $('#actividades-sortable tr').each(function() {
                                const id = $(this).data('id');
                                if (id) orden.push(id);
                            });

                            if (orden.length > 0) {
                                const response = await reordenarActividadesAjax(orden, $(el).data('reorder-url'));
                                if (response.success) {
                                    $('.indice-actividad').each(function(index) {
                                        $(this).text(index + 1);
                                    });
                                }
                            }
                        }
                    });
                }

                // --- Inicialización para la Vista Móvil (Cards) ---
                let elCards = document.getElementById('sortable-cards-mobile');
                if (elCards) {
                    Sortable.create(elCards, {
                        animation: 150,
                        ghostClass: 'bg-light',
                        filter: '.btn, .btn *, .custom-checkbox, .custom-checkbox *', // No arrastrar si se toca un botón o checkbox
                        preventOnFilter: false,
                        onEnd: function () {
                            actualizarOrdenActividades();
                        }
                    });
                }

                // Función para procesar el orden en móvil
                async function actualizarOrdenActividades() {
                    let orden = [];
                    let contenedor = $('#actividades-cards-container');
                    let urlRuta = contenedor.data('reorder-url');

                    // 1. Actualización visual inmediata
                    $('#sortable-cards-mobile .card-actividad-movil').each(function(index) {
                        let id = $(this).data('id');
                        if(id) {
                            orden.push(id);
                            $(this).find('.indice-actividad-movil').text(index + 1);
                        }
                    });

                    // 2. Guardado asíncrono
                    if (orden.length > 0) {
                        const response = await reordenarActividadesAjax(orden, urlRuta);
                        if (!response.success) {
                            alert(response.mensaje_usuario);
                        }
                        console.log('Orden móvil sincronizado');
                    }
                }

                // --- Lógica de Selección y Acciones en Lote ---

                function toggleBulkActions() {
                    const totalSelected = $('.check-actividad:checked').length;
                    if (totalSelected > 0) {
                        $('#bulk-actions-container').removeClass('d-none');
                    } else {
                        $('#bulk-actions-container').addClass('d-none');
                        $('#bulk_action_select').val('');
                    }
                }

                $(document).on('change', '#check-all-actividades', function() {
                    $('.check-actividad').prop('checked', $(this).prop('checked'));
                    $('#check-all-actividades-mobile').prop('checked', $(this).prop('checked'));
                    toggleBulkActions();
                });

                $(document).on('change', '#check-all-actividades-mobile', function() {
                    $('.check-actividad').prop('checked', $(this).prop('checked'));
                    $('#check-all-actividades').prop('checked', $(this).prop('checked'));
                    toggleBulkActions();
                });

                $(document).on('change', '.check-actividad', function() {
                    const totalCheckboxes = $('.check-actividad').length;
                    const totalChecked = $('.check-actividad:checked').length;

                    $('#check-all-actividades, #check-all-actividades-mobile').prop('checked', totalCheckboxes === totalChecked);
                    toggleBulkActions();
                });

                $('#bulk_action_select').on('change', function() {
                    const val = $(this).val();
                    if (val === 'multiplicar_precio') {
                        $('#modalBulkPriceUpdate').modal('show');
                    }
                });

                $('#btn-aceptar-bulk-price').on('click', async function() {
                    const ids = $('.check-actividad:checked').map(function() { return $(this).val(); }).get();
                    const tipoCambio = $('input[name="tipo_cambio"]:checked').val();
                    const porcentaje = $('#bulk_porcentaje').val();
                    const preciosAfectar = $('.check-precio-afectar:checked').map(function() { return $(this).val(); }).get();

                    if (!porcentaje || porcentaje <= 0) {
                        alert(window.RedaAlojamientoJson["Debe ingresar un porcentaje válido"] || "Debe ingresar un porcentaje válido");
                        return;
                    }

                    if (preciosAfectar.length === 0) {
                        alert(window.RedaAlojamientoJson["Debe seleccionar al menos un precio a afectar"] || "Debe seleccionar al menos un precio a afectar");
                        return;
                    }

                    const btn = $(this);
                    btn.prop('disabled', true);
                    btn.find('.spinner-bulk').removeClass('d-none');
                    btn.find('.btn-text').addClass('d-none');

                    const response = await actualizarPreciosLoteAjax({
                        ids: ids,
                        tipo_cambio: tipoCambio,
                        porcentaje: porcentaje,
                        precios_afectar: preciosAfectar
                    });

                    if (response.success) {
                        $('#modalBulkPriceUpdate').modal('hide');
                        // Mostrar notificación de éxito
                        $('#notificacion-icono').html('<i class="fa fa-check-circle fa-4x text-success"></i>');
                        $('#notificacion-titulo').text(window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!");
                        $('#notificacion-mensaje').text(response.mensaje_usuario);
                        $('#modal-notificacion').modal('show');

                        $('#modal-notificacion').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                            location.reload();
                        });
                    } else {
                        alert(response.mensaje_usuario);
                    }

                    btn.prop('disabled', false);
                    btn.find('.spinner-bulk').addClass('d-none');
                    btn.find('.btn-text').removeClass('d-none');
                });

                // Toggle para Precio en Bolívares
                $(document).on('change', '.radio-tipo-carga', function() {
                    const container = $(this).closest('.fila-actividad-container');
                    const divPrecio = container.find('.div-precio-bolivares');
                    const inputPrecio = container.find('.input-precio-bolivares');

                    if ($(this).val() === 'manual') {
                        divPrecio.removeClass('d-none');
                    } else {
                        divPrecio.addClass('d-none');
                    }
                });

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

                    // Validación Precio para pago en bolívares (Manual)
                    $('.input-precio-bolivares').each(function() {
                        $(this).rules('add', {
                            required: function(element) {
                                return $(element).closest('.fila-actividad-container').find('.radio-tipo-carga:checked').val() === 'manual';
                            },
                            number: true,
                            min: 0.01,
                            messages: {
                                required: window.RedaAlojamientoJson["El precio para pago en bolívares es obligatorio"] || "El precio para pago en bolívares es obligatorio",
                                number: window.RedaAlojamiento.general.el_precio_debe_ser_un_numero_valido,
                                min: window.RedaAlojamientoJson["Mínimo 0.01"] || "Mínimo 0.01"
                            }
                        });
                    });

                    // Validación Moneda Complementaria (Manual)
                    $('.select-moneda-complementaria').each(function() {
                        $(this).rules('add', {
                            required: function(element) {
                                const container = $(element).closest('.fila-actividad-container');
                                const isManual = container.find('.radio-tipo-carga:checked').val() === 'manual';
                                const hasPrice = container.find('.input-precio-bolivares').val() !== '';
                                return isManual && hasPrice;
                            },
                            messages: {
                                required: window.RedaAlojamientoJson["Debe seleccionar una moneda"] || "Debe seleccionar una moneda"
                            }
                        });
                    });

                    // Moneda Principal (Solo si hay precio)
                    $('select[name*="[currency_id]"]').each(function() {
                        $(this).rules('add', {
                            required: function(element) {
                                return $(element).closest('.fila-actividad-container').find('.validar-precio').val() !== '';
                            },
                            messages: {
                                required: window.RedaAlojamiento.general.el_tipo_de_moneda_es_obligatorio
                            }
                        });
                    });

                    // Moneda de Promoción (Solo si hay precio de promoción)
                    $('.select-moneda-promocion').each(function() {
                        $(this).rules('add', {
                            required: function(element) {
                                return $(element).closest('.fila-actividad-container').find('.input-precio-promocion').val() !== '';
                            },
                            messages: {
                                required: window.RedaAlojamientoJson["Debe seleccionar una moneda"] || "Debe seleccionar una moneda"
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

                    // Estatus
                    $('select[name*="[estatus_producto_servicio]"]').each(function() {
                        $(this).rules('add', {
                            required: true,
                            messages: {
                                required: window.RedaAlojamientoJson["Debe seleccionar un estatus"] || "Debe seleccionar un estatus"
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

                $('#btn-add-actividad').on('click', async function(e) {
                    e.preventDefault();

                    // Obtenemos la URL del atributo data-add-url que pusimos en el botón
                    const url = $(this).data('add-url');
                    const btn = $(this);

                    btn.prop('disabled', true).css('opacity', '0.5');

                    const response = await agregarActividadAjax(url);

                    if (response.success) {
                        currentNewActivityId = response.respuesta.id;
                        isEditingActividad = false;

                        // Empujamos estado al historial para manejar el botón "Atrás" del celular
                        history.pushState({ view: 'form-actividad' }, '');

                        // Ocultar lista y botón add
                        $('#productos-servicios-list-container').addClass('d-none');
                        btn.hide();

                        // Mostrar formulario y acciones nuevas
                        $('#actividades-wrapper').html(response.respuesta.html);
                        $('#new-producto-actions').removeClass('d-none');

                        aplicarReglasDinamicas();

                        // Scroll suave hacia el inicio del formulario
                        $('html, body').animate({
                            scrollTop: $('#actividades-wrapper').offset().top - 100
                        }, 500);
                    } else {
                        // Mostrar notificación de error
                        $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                        $('#notificacion-titulo').text(window.RedaAlojamientoJson["Error"] || 'Error');
                        $('#notificacion-mensaje').text(response.mensaje_usuario);
                        $('#modal-notificacion').modal('show');
                    }

                    btn.prop('disabled', false).css('opacity', '1');
                });

                $(document).on('click', '.btn-edit-actividad, .btn-modal-actividad', async function(e) {
                    e.preventDefault();
                    const btn = $(this);
                    const id = btn.data('id');
                    const mode = btn.data('mode') || 'edit'; // 'view' o 'edit'
                    const url = btn.data('edit-url') || (APP_URL + '/reda/negocios/experiencias/actividades/get-form/' + id);

                    btn.prop('disabled', true).css('opacity', '0.5');

                    // Añadimos el parámetro mode a la URL
                    const urlWithMode = url + (url.includes('?') ? '&' : '?') + 'mode=' + mode;

                    const response = await obtenerFormularioActividadAjax(urlWithMode);

                    if (response.success) {
                        if (mode === 'view') {
                            // Mostrar en un MODAL (para la vista "Ver")
                            $('#actividad-modal-body').html(response.respuesta.html);
                            $('#actividadModalLabel').text(window.RedaAlojamiento.general.detalle_del_producto_o_servicio);

                            // Ocultamos el botón guardar del modal si es solo lectura
                            if (response.respuesta.readonly) {
                                $('#btn-save-actividad-modal').hide();
                            } else {
                                $('#btn-save-actividad-modal').show();
                            }

                            $('#actividadModal').modal('show');
                        } else {
                            // Mostrar en el WRAPPER (para la vista "Editar")
                            isEditingActividad = true;
                            currentNewActivityId = null;

                            history.pushState({ view: 'form-actividad' }, '');

                            $('#productos-servicios-list-container').addClass('d-none');
                            $('#btn-add-actividad').hide();

                            $('#actividades-wrapper').html(response.respuesta.html);
                            $('#new-producto-actions').removeClass('d-none');

                            aplicarReglasDinamicas();

                            $('html, body').animate({
                                scrollTop: $('#actividades-wrapper').offset().top - 100
                            }, 500);
                        }
                    } else {
                        $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                        $('#notificacion-titulo').text(window.RedaAlojamientoJson["Error"] || 'Error');
                        $('#notificacion-mensaje').text(response.mensaje_usuario);
                        $('#modal-notificacion').modal('show');
                    }

                    btn.prop('disabled', false).css('opacity', '1');
                });

                $('#btn-cancel-new-producto').on('click', function() {
                    if (currentNewActivityId) {
                        const deleteUrl = APP_URL + '/reda/negocios/experiencias/actividades/delete/' + currentNewActivityId;

                        $(this).prop('disabled', true);

                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            data: {
                                _token: $('input[name="_token"]').val()
                            },
                            success: function(response) {
                                // Usamos history.back() si estamos en el estado del formulario
                                if (window.history.state && window.history.state.view === 'form-actividad') {
                                    history.back();
                                } else {
                                    const baseUrl = window.location.href.split('#')[0].split('?')[0];
                                    window.location.href = baseUrl + '?refresh=' + new Date().getTime() + '#seccion-productos-servicios';
                                }
                            },
                            error: function(x, xs, xt) {
                                if (window.history.state && window.history.state.view === 'form-actividad') {
                                    history.back();
                                } else {
                                    const baseUrl = window.location.href.split('#')[0].split('?')[0];
                                    window.location.href = baseUrl + '?refresh=' + new Date().getTime() + '#seccion-productos-servicios';
                                }
                            }
                        });
                    } else {
                        if (window.history.state && window.history.state.view === 'form-actividad') {
                            history.back();
                        } else {
                            const baseUrl = window.location.href.split('#')[0].split('?')[0];
                            window.location.href = baseUrl + '?refresh=' + new Date().getTime() + '#seccion-productos-servicios';
                        }
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
                    $('#btn-confirmar-si').off('click').on('click', async function() {
                        const btnConfirmar = $(this);
                        btnConfirmar.prop('disabled', true);
                        btnConfirmar.find('.btn-text').addClass('d-none');
                        btnConfirmar.find('.fa-spinner').removeClass('d-none');

                        const response = await eliminarActividadAjax(url);

                        if (response.success) {
                            filas.fadeOut(400).promise().done(function() {
                                filas.remove();
                                $('#modal-confirmacion').modal('hide');

                                // Mostrar notificación de éxito
                                $('#notificacion-icono').html('<i class="fa fa-check-circle fa-4x text-success"></i>');
                                $('#notificacion-titulo').text(window.RedaAlojamiento.general.exito || '¡Éxito!');
                                $('#notificacion-mensaje').text(response.mensaje_usuario);
                                $('#modal-notificacion').modal('show');
                            });
                        } else {
                            $('#modal-confirmacion').modal('hide');

                            // Mostrar notificación de error
                            $('#notificacion-icono').html('<i class="fa fa-times-circle fa-4x text-danger"></i>');
                            $('#notificacion-titulo').text(window.RedaAlojamiento.general.error || 'Error');
                            $('#notificacion-mensaje').text(response.mensaje_usuario);
                            $('#modal-notificacion').modal('show');
                        }

                        btnConfirmar.prop('disabled', false);
                        btnConfirmar.find('.btn-text').removeClass('d-none');
                        btnConfirmar.find('.fa-spinner').addClass('d-none');
                    });
                });

                break;

            case 'horario':
                let bloqueIndex = 0;

                const crearBloqueHtml = (index, data = null) => {
                    const horaDesde = data ? data.hora_desde : '';
                    const ampmDesde = data ? data.ampm_desde : 'am';
                    const horaHasta = data ? data.hora_hasta : '';
                    const ampmHasta = data ? data.ampm_hasta : 'pm';

                    return `
                        <div class="row m-0 align-items-center mb-3 bloque-hora" data-index="${index}">
                            <div class="col-md-5 col-5 p-0">
                                <div class="input-group">
                                    <input type="text" class="form-control hora-desde" name="bloques[${index}][hora_desde]" value="${horaDesde}" placeholder="00:00" required>
                                    <div class="input-group-append">
                                        <select class="form-control ampm-desde select-compact-ampm" name="bloques[${index}][ampm_desde]">
                                            <option value="am" ${ampmDesde === 'am' ? 'selected' : ''}>AM</option>
                                            <option value="pm" ${ampmDesde === 'pm' ? 'selected' : ''}>PM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1 col-1 text-center font-weight-700 p-0"> - </div>
                            <div class="col-md-5 col-5 p-0">
                                <div class="input-group">
                                    <input type="text" class="form-control hora-hasta" name="bloques[${index}][hora_hasta]" value="${horaHasta}" placeholder="00:00" required>
                                    <div class="input-group-append">
                                        <select class="form-control ampm-hasta select-compact-ampm" name="bloques[${index}][ampm_hasta]">
                                            <option value="am" ${ampmDesde === 'am' ? 'selected' : ''}>AM</option>
                                            <option value="pm" ${ampmDesde === 'pm' ? 'selected' : ''}>PM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1 col-1 text-right p-0">
                                <button type="button" class="btn btn-sm btn-link text-danger btn-remove-bloque">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `;
                };

                const resetModal = () => {
                    $('#horario-index').val('');
                    $('#form-modal-horario')[0].reset();
                    $('#bloques-container').empty();
                    $('.check-dia').prop('disabled', false).closest('.custom-control').css('opacity', '1');
                    $('#btn-add-bloque').show();
                    $('#btn-guardar-horario-modal').show();
                    $('#form-modal-horario input, #form-modal-horario select').prop('disabled', false);
                    bloqueIndex = 0;
                    $('#bloques-container').append(crearBloqueHtml(bloqueIndex++));
                };

                const guardarHorarioAjax = (formData) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: APP_URL + '/reda/negocios/experiencias/' + EXPERIENCIA_ID + '/guardar-horario',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: (data) => resolve(data),
                            error: function (x, xs, xt) {
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                let respuesta = {
                                    'success': false,
                                    'message' : 'Error en el servidor',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario || 'Error al guardar el horario',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    });
                };

                const eliminarHorarioAjax = (index) => {
                    return new Promise((resolve) => {
                        $.ajax({
                            url: APP_URL + '/reda/negocios/experiencias/' + EXPERIENCIA_ID + '/eliminar-horario/' + index,
                            type: 'DELETE',
                            data: { _token: $('input[name="_token"]').val() },
                            success: (data) => resolve(data),
                            error: function (x, xs, xt) {
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                let respuesta = {
                                    'success': false,
                                    'message' : 'Error en el servidor',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario || 'Error al eliminar el horario',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    });
                };

                $('#btn-agregar-horario').on('click', function() {
                    resetModal();
                    $('#modalHorarioLabel').text(window.RedaAlojamientoJson["Configurar Horario"] || "Configurar Horario");
                    $('#modalHorario').modal('show');
                });

                $('#btn-add-bloque').on('click', function() {
                    $('#bloques-container').append(crearBloqueHtml(bloqueIndex++));
                });

                $(document).on('keypress', '.bloque-hora input', function(e) {
                    if (e.which == 13) {
                        e.preventDefault();
                        $('#btn-add-bloque').click();
                        // Opcional: enfocar el nuevo input
                        setTimeout(() => {
                            $('#bloques-container .bloque-hora:last-child input:first').focus();
                        }, 100);
                    }
                });

                $(document).on('click', '.btn-remove-bloque', function() {
                    if ($('.bloque-hora').length > 1) {
                        $(this).closest('.bloque-hora').remove();
                    }
                });

                $('#btn-guardar-horario-modal').on('click', async function() {
                    const form = $('#form-modal-horario');
                    if (!$('.check-dia:checked').length) {
                        alert(window.RedaAlojamientoJson["Debe seleccionar al menos un día"] || "Debe seleccionar al menos un día");
                        return;
                    }

                    let valid = true;
                    $('.bloque-hora input').each(function() {
                        if (!$(this).val()) {
                            valid = false;
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });

                    if (!valid) return;

                    const btn = $(this);
                    btn.prop('disabled', true);
                    btn.find('.spinner-save').removeClass('d-none');
                    btn.find('.btn-text').addClass('d-none');

                    const formData = new FormData(form[0]);
                    const index = $('#horario-index').val();
                    if (index !== '') {
                        formData.append('index', index);
                    }

                    const response = await guardarHorarioAjax(formData);

                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.mensaje_usuario);
                        btn.prop('disabled', false);
                        btn.find('.spinner-save').addClass('d-none');
                        btn.find('.btn-text').removeClass('d-none');
                    }
                });

                $(document).on('click', '.btn-ver-horario, .btn-editar-horario', function() {
                    const isEdit = $(this).hasClass('btn-editar-horario');
                    const index = $(this).data('index');
                    const data = $(this).data('horario');

                    resetModal();
                    $('#horario-index').val(index);
                    $('#bloques-container').empty();

                    // Marcar días
                    if (data.dias) {
                        $.each(data.dias, function(key, dia) {
                            $(`#dia-${dia}`).prop('checked', true);
                        });
                    }

                    // Añadir bloques
                    if (data.bloques) {
                        $.each(data.bloques, function(key, bloque) {
                            $('#bloques-container').append(crearBloqueHtml(bloqueIndex++, bloque));
                        });
                    }

                    if (!isEdit) {
                        $('#modalHorarioLabel').text(window.RedaAlojamientoJson["Ver Horario"] || "Ver Horario");
                        $('.check-dia').prop('disabled', true).closest('.custom-control').css('opacity', '1');
                        $('#form-modal-horario input, #form-modal-horario select').prop('disabled', true);
                        $('.btn-remove-bloque, #btn-add-bloque, #btn-guardar-horario-modal').hide();
                    } else {
                        $('#modalHorarioLabel').text(window.RedaAlojamientoJson["Editar Horario"] || "Editar Horario");
                    }

                    $('#modalHorario').modal('show');
                });

                $(document).on('click', '.btn-eliminar-horario', function() {
                    const index = $(this).data('index');
                    $('#confirmacion-mensaje').text(window.RedaAlojamientoJson["¿Estás seguro de que deseas eliminar este horario?"] || "¿Estás seguro de que deseas eliminar este horario?");
                    $('#modal-confirmacion').modal('show');

                    $('#btn-confirmar-si').off('click').on('click', async function() {
                        const btn = $(this);
                        btn.prop('disabled', true);
                        btn.find('.fa-spinner').removeClass('d-none');

                        const response = await eliminarHorarioAjax(index);
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.mensaje_usuario);
                            btn.prop('disabled', false);
                            btn.find('.fa-spinner').addClass('d-none');
                        }
                    });
                });

                break;

            case 'ubicacion':
                function updateControls(addressComponents) {
                    if (!addressComponents) return;

                    const mapping = {
                        '#address_line_1': addressComponents.addressLine1,
                        '#city': addressComponents.city,
                        '#state': addressComponents.stateOrProvince,
                        '#postal_code': addressComponents.postalCode,
                        '#country': addressComponents.country
                    };

                    $.each(mapping, function(selector, valorGoogle) {
                        const input = $(selector);
                        if (input.length) {
                            const valorActual = (input.val() || '').trim();

                            // Lógica de decisión:
                            // 1. Si el campo está vacío, lo llenamos.
                            // 2. Si el usuario movió el marcador (isMarkerDropped), lo actualizamos SIEMPRE.
                            // 3. Si NO movió el marcador y el campo YA TIENE datos, lo respetamos.

                            if (valorGoogle && (valorActual === '' || window.isMarkerMoving)) {
                                input.val(valorGoogle);
                            }
                        }
                    });

                    if ($.isFunction($.fn.valid)) {
                        $('#list_des').valid();
                    }
                }

                window.isMarkerMoving = false;

                $('#map_view').locationpicker({
                    location: {
                        latitude: latitude,
                        longitude: longitude
                    },
                    radius: 0,
                    addressFormat: '',
                    inputBinding: {
                        latitudeInput: $('#latitude'),
                        longitudeInput: $('#longitude'),
                        locationNameInput: $('#map_search')
                    },
                    enableAutocomplete: true,
                    onchanged: function (currentLocation, radius, isMarkerDropped) {
                        if (isMarkerDropped) {
                            window.isMarkerMoving = true;
                        }
                        var addressComponents = $(this).locationpicker('map').location.addressComponents;
                        updateControls(addressComponents);

                        // Reseteamos el flag después de actualizar
                        window.isMarkerMoving = false;
                    },
                    oninitialized: function (component) {
                        var addressComponents = $(component).locationpicker('map').location.addressComponents;

                        // En la inicialización, NO forzamos la actualización si los campos ya tienen datos
                        // updateControls se encargará de respetar los valores de Blade
                        updateControls(addressComponents);

                        // Solo geolocalizamos si es una experiencia nueva (Caracas por defecto)
                        if (latitude == '10.5061' && longitude == '-66.9145' && navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(function(position) {
                                var userLat = position.coords.latitude;
                                var userLng = position.coords.longitude;
                                window.isMarkerMoving = true;
                                $(component).locationpicker('location', {
                                    latitude: userLat,
                                    longitude: userLng
                                });
                            });
                        }
                    }
                });

                $('#list_des').validate({
                    ignore: [],
                    rules: {
                        map_search: { required: true, maxlength: 255 },
                        address_line_1: { required: true, maxlength: 255 },
                        address_line_2: { maxlength: 255 },
                        city: { required: true },
                        state: { required: true },
                        country: { required: true },
                        latitude: { required: true, min: -90, max: 90 },
                        email_negocio: { required: true, email: true, maxlength: 255 },
                        whatsapp_negocio: { required: true, maxlength: 255 }
                    },
                    submitHandler: function(form) {
                        $('#btn_next').attr('disabled', true);
                        $('.spinner').removeClass('d-none');
                        $('#btn_next-text').text(window.RedaAlojamientoJson['Guardando...'] || 'Guardando...');
                        return true;
                    },
                    messages: {
                        map_search: {
                            required: window.RedaAlojamientoJson['Búsqueda en el mapa obligatoria'] || 'Búsqueda en el mapa obligatoria',
                            maxlength: window.RedaAlojamientoJson['Por favor, no introduzcas más de 255 caracteres.'] || 'Por favor, no introduzcas más de 255 caracteres.',
                        },
                        address_line_1: {
                            required: window.RedaAlojamientoJson['Dirección obligatoria'] || 'Dirección obligatoria',
                            maxlength: window.RedaAlojamientoJson['Por favor, no introduzcas más de 255 caracteres.'] || 'Por favor, no introduzcas más de 255 caracteres.',
                        },
                        address_line_2: {
                            maxlength: window.RedaAlojamientoJson['Por favor, no introduzcas más de 255 caracteres.'] || 'Por favor, no introduzcas más de 255 caracteres.',
                        },
                        city: {
                            required: window.RedaAlojamientoJson['Ciudad obligatoria'] || 'Ciudad obligatoria',
                        },
                        state: {
                            required: window.RedaAlojamientoJson['Estado obligatorio'] || 'Estado obligatorio',
                        },
                        country: {
                            required: window.RedaAlojamientoJson['País obligatorio'] || 'País obligatorio',
                        },
                        latitude: {
                            required: window.RedaAlojamientoJson['Debe fijar la posición en el mapa'] || 'Debe fijar la posición en el mapa',
                            min: window.RedaAlojamientoJson['Debe fijar la posición en el mapa'] || 'Debe fijar la posición en el mapa',
                            max: window.RedaAlojamientoJson['Debe fijar la posición en el mapa'] || 'Debe fijar la posición en el mapa'
                        },
                        email_negocio: {
                            required: window.RedaAlojamientoJson['Correo electrónico obligatorio'] || 'Correo electrónico obligatorio',
                            email: window.RedaAlojamientoJson['Ingrese un correo válido'] || 'Ingrese un correo válido'
                        },
                        whatsapp_negocio: {
                            required: window.RedaAlojamientoJson['WhatsApp obligatorio'] || 'WhatsApp obligatorio'
                        }
                    },
                    errorPlacement: function(error, element) {
                        if (element.attr('name') == 'latitude') {
                            error.insertAfter('.map-view-location');
                        } else {
                            error.insertAfter(element);
                        }
                    }
                });

                break;

            case 'anfitrion':
                $('#list_des').validate({
                    rules: {
                        trayectoria_profesional: { required: true },
                    },
                    messages: {
                        trayectoria_profesional: {
                            required: window.RedaAlojamientoJson["La trayectoria profesional es obligatoria."] || "La trayectoria profesional es obligatoria.",
                        },
                    },
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text(window.RedaAlojamientoJson["Guardando..."] || "Guardando...");
                        return true;
                    }
                });

                document.addEventListener('mediaUpdated', function(e) {
                    if (e.detail.origen === 'anfitrion-experiencia') {
                        const data = e.detail.response;
                        const container = $('#foto-container-anfitrion');
                        const anfitrionId = data.id;
                        const nuevaUrl = data.path;

                        if (nuevaUrl && container.length) {
                            container.html(`
                                <img src="${nuevaUrl}?v=${new Date().getTime()}" class="img-fluid rounded-3 shadow-sm" alt="Foto">
                                <label class="edit-photo-overlay-outline" for="file-anfitrion" title="Cambiar imagen">
                                    <i class="fa fa-pencil-alt"></i>
                                </label>
                                <input id="file-anfitrion" type="file" name="foto_anfitrion"
                                       data-id="${anfitrionId}" class="upload_photos" accept="image/*" style="display:none;">
                            `);
                            container.removeClass('no-image');
                        }
                    }
                });

                break;

            case 'informacion_adicional':
                $('#list_des').validate({
                    submitHandler: function(form) {
                        $("#btn_next").attr("disabled", true);
                        $(".spinner").removeClass('d-none');
                        $("#btn_next-text").text(window.RedaAlojamientoJson["Guardando..."] || "Guardando...");
                        return true;
                    }
                });

                break;
            // ... resto de los pasos
        }
    }
});
