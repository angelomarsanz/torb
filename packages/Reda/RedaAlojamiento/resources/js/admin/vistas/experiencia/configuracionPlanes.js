(function( $ ) {
    "use strict";
    const containerId = '#configuracion_planes_container';
    if ($(containerId).length) {
        console.log('Script para "Configuración de Planes" cargado correctamente.');
        
        /**
         * Carga el listado de planes mediante AJAX
         * @param {string} url 
         */
        const cargarPlanes = (url = null) => {
            const targetUrl = url || window.RedaRutas.index_planes;
            
            // Mostrar animación de espera si existe
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }

            $.ajax({
                url: targetUrl,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#contenedor-tabla-planes').html(response.respuesta);
                        // Reinicializar tooltips
                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        alert(response.mensaje_usuario || response.message);
                    }
                },
                error: function() {
                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || "Error en el servidor de Torbian";
                    alert(mensajeErrorBase);
                },
                complete: function() {
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.cerrar === 'function') {
                        window.RedaNotificaciones.cerrar();
                    }
                }
            });
        };

        $(function() {
            // Manejo de cambio de pestañas (Tabs)
            $('.nav-tabs a').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });

            // Al hacer clic en la pestaña "Planes", cargar el listado
            $('#btn-tab-planes').on('click', function() {
                cargarPlanes();
            });

            // Manejo de paginación AJAX
            $(document).on('click', '#contenedor-tabla-planes .pagination a', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                if (url) {
                    cargarPlanes(url);
                }
            });

            // Guardar Opciones Generales (Antigüedad y Promedio)
            $('#form-configuracion-planes').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $btn = $('#btn-save-config-planes');
                const url = $form.attr('action');

                $btn.prop('disabled', true);
                $btn.find('.btn-text').addClass('d-none');
                $btn.find('.fa-spinner').removeClass('d-none');

                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                                window.RedaNotificaciones.notificar(
                                    window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!",
                                    response.mensaje_usuario,
                                    'success'
                                );
                            } else {
                                alert(response.mensaje_usuario);
                            }
                        } else {
                            alert(response.mensaje_usuario || response.message);
                        }
                    },
                    error: function(x) {
                        let respuestaServidor = {};
                        try { respuestaServidor = JSON.parse(x.responseText); } catch (e) {}
                        const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || "Error en el servidor de Torbian";
                        alert(respuestaServidor.mensaje_usuario || mensajeErrorBase);
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.find('.btn-text').removeClass('d-none');
                        $btn.find('.fa-spinner').addClass('d-none');
                        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.cerrar === 'function') {
                            window.RedaNotificaciones.cerrar();
                        }
                    }
                });
            });

            // --- Lógica CRUD de Planes ---

            // Abrir modal para AGREGAR PLAN
            $(document).on('click', '#btn-add-plan', function(e) {
                e.preventDefault();
                $('#form-plan')[0].reset();
                $('#plan_id').val('');
                $('#form-plan').attr('action', window.RedaRutas.store_plan);
                $('#modal-title-plan').text(window.RedaAlojamientoJson["Agregar nuevo plan"] || "Agregar nuevo plan");
                $('#modal-plan').modal('show');
            });

            // Abrir modal para EDITAR PLAN
            $(document).on('click', '.btn-edit-plan', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }

                $.ajax({
                    url: window.RedaRutas.get_plan + '/' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const plan = response.respuesta;
                            $('#form-plan')[0].reset();
                            $('#plan_id').val(plan.id);
                            $('#nombre_plan').val(plan.nombre);
                            $('#precio_plan').val(plan.precio);
                            $('#moneda_plan').val(plan.moneda);
                            $('#lapso_pago_plan').val(plan.lapso_pago);
                            $('#orden_plan').val(plan.orden);
                            $('#destacado_plan').prop('checked', plan.destacado);
                            $('#estatus_plan').prop('checked', plan.estatus);
                            $('#beneficios_plan').val(JSON.stringify(plan.beneficios));

                            $('#form-plan').attr('action', window.RedaRutas.update_plan);
                            $('#modal-title-plan').text(window.RedaAlojamientoJson["Editar plan"] || "Editar plan");
                            $('#modal-plan').modal('show');
                        }
                    },
                    complete: function() {
                        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.cerrar === 'function') {
                            window.RedaNotificaciones.cerrar();
                        }
                    }
                });
            });

            // Guardar PLAN (Add/Update)
            $('#form-plan').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btn-save-plan');

                $btn.prop('disabled', true).find('.fa-spinner').removeClass('d-none');
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modal-plan').modal('hide');
                            cargarPlanes();
                            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                                window.RedaNotificaciones.notificar(
                                    window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!",
                                    response.mensaje_usuario,
                                    'success'
                                );
                            }
                        }
                    },
                    error: function(x) {
                        let res = {}; try { res = JSON.parse(x.responseText); } catch(e){}
                        alert(res.mensaje_usuario || "Error guardando el plan");
                    },
                    complete: function() {
                        $btn.prop('disabled', false).find('.fa-spinner').addClass('d-none');
                        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.cerrar === 'function') {
                            window.RedaNotificaciones.cerrar();
                        }
                    }
                });
            });

            // Eliminar PLAN
            $(document).on('click', '.btn-delete-plan', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                if (confirm(window.RedaAlojamientoJson["¿Estás seguro?"] || "¿Estás seguro?")) {
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                        window.RedaNotificaciones.esperar();
                    }

                    $.ajax({
                        url: window.RedaRutas.destroy_plan + '/' + id,
                        type: 'DELETE',
                        data: { "_token": $('meta[name="csrf-token"]').attr('content') },
                        success: function(response) {
                            if (response.success) {
                                cargarPlanes();
                                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                                    window.RedaNotificaciones.notificar(
                                        window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!",
                                        response.mensaje_usuario,
                                        'success'
                                    );
                                }
                            }
                        },
                        complete: function() {
                            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.cerrar === 'function') {
                                window.RedaNotificaciones.cerrar();
                            }
                        }
                    });
                }
            });
        });
    }
})(jQuery);
