(function( $ ) {
    "use strict";
    const containerId = '#configuracion_planes_container';
    if ($(containerId).length) {
        console.log('Script para "Configuración de Planes" cargado correctamente.');
        
        // Variables para el modal de confirmación y vista
        let idPlanAEliminar = null;
        let indexOpcionAEliminar = null;
        let idPlanActualVista = null;

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
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                        window.RedaNotificaciones.ocultar();
                    }
                }
            });
        };

        /**
         * Genera el HTML de una fila de plan de pago
         * @param {object} datos {precio, moneda, lapso}
         * @returns string
         */
        const htmlFilaPlanPago = (datos = {}) => {
            const index = $('#contenedor-planes-pago .fila-plan-pago').length;
            const precio = datos.precio || '';
            const moneda = datos.moneda || 'dólar';
            const lapso = datos.lapso || 'anual';

            return `
                <div class="row fila-plan-pago mb-2" style="margin-bottom: 12px; border-bottom: 1px dashed #eee; padding-bottom: 10px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="f-12">${window.RedaAlojamientoJson["Precio"] || "Precio"}</label>
                            <input type="number" name="planes_pago[${index}][precio]" class="form-control f-14 input-precio-plan" step="0.01" min="0" value="${precio}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="f-12">${window.RedaAlojamientoJson["Moneda"] || "Moneda"}</label>
                            <select name="planes_pago[${index}][moneda]" class="form-control f-14 select-moneda-plan" required>
                                <option value="dólar" ${moneda === 'dólar' ? 'selected' : ''}>Dólar ($)</option>
                                <option value="Bs" ${moneda === 'Bs' ? 'selected' : ''}>Bolívares (Bs)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="f-12">${window.RedaAlojamientoJson["Lapso de pago"] || "Lapso de pago"}</label>
                            <select name="planes_pago[${index}][lapso]" class="form-control f-14 select-lapso-plan" required>
                                <option value="anual" ${lapso === 'anual' ? 'selected' : ''}>${window.RedaAlojamientoJson["Anual"] || "Anual"}</option>
                                <option value="mensual" ${lapso === 'mensual' ? 'selected' : ''}>${window.RedaAlojamientoJson["Mensual"] || "Mensual"}</option>
                                <option value="quincenal" ${lapso === 'quincenal' ? 'selected' : ''}>${window.RedaAlojamientoJson["Quincenal"] || "Quincenal"}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end" style="padding-bottom: 15px;">
                        <button type="button" class="btn btn-danger btn-flat btn-sm btn-eliminar-plan-pago" title="${window.RedaAlojamientoJson["Eliminar"] || "Eliminar"}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        };

        /**
         * Genera el HTML de una fila de beneficio
         * @param {string} valor 
         * @returns string
         */
        const htmlFilaBeneficio = (valor = '') => {
            return `
                <div class="row fila-beneficio mb-2" style="margin-bottom: 8px;">
                    <div class="col-xs-10 col-sm-11">
                        <input type="text" name="beneficios[]" class="form-control f-14 input-beneficio" value="${valor}" placeholder="${window.RedaAlojamientoJson["Ej: Soporte 24/7"] || "Ej: Soporte 24/7"}" required>
                    </div>
                    <div class="col-xs-2 col-sm-1">
                        <button type="button" class="btn btn-danger btn-flat btn-sm btn-eliminar-beneficio" title="${window.RedaAlojamientoJson["Eliminar"] || "Eliminar"}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        };

        /**
         * Formatea un valor numérico a moneda
         */
        const formatoMoneda = (moneda, valor) => {
            const simbolo = moneda === 'dólar' ? '$' : 'Bs';
            return `${simbolo} ${parseFloat(valor).toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        };

        /**
         * Abre el modal de edición para un plan específico
         */
        const abrirModalEdicion = (id) => {
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
                        $('#orden_plan').val(plan.orden);
                        $('#destacado_plan').prop('checked', plan.destacado);
                        $('#estatus_plan').prop('checked', plan.estatus);
                        
                        // Cargar planes de pago dinámicos
                        $('#contenedor-planes-pago').empty();
                        let planesPago = [];
                        try {
                            planesPago = typeof plan.planes_pago === 'string' ? JSON.parse(plan.planes_pago) : (plan.planes_pago || []);
                        } catch (e) { planesPago = []; }

                        if (planesPago && planesPago.length > 0) {
                            planesPago.forEach(p => {
                                $('#contenedor-planes-pago').append(htmlFilaPlanPago(p));
                            });
                        } else {
                            $('#contenedor-planes-pago').append(htmlFilaPlanPago());
                        }

                        // Cargar beneficios dinámicos
                        $('#contenedor-beneficios').empty();
                        let beneficios = [];
                        try {
                            beneficios = typeof plan.beneficios === 'string' ? JSON.parse(plan.beneficios) : (plan.beneficios || []);
                        } catch (e) { beneficios = []; }

                        if (beneficios && beneficios.length > 0) {
                            beneficios.forEach(b => {
                                $('#contenedor-beneficios').append(htmlFilaBeneficio(b));
                            });
                        } else {
                            $('#contenedor-beneficios').append(htmlFilaBeneficio());
                        }

                        $('#form-plan').attr('action', window.RedaRutas.update_plan);
                        $('#modal-title-plan').text(window.RedaAlojamientoJson["Editar plan"] || "Editar plan");
                        $('#modal-plan').modal('show');
                    }
                },
                complete: function() {
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                        window.RedaNotificaciones.ocultar();
                    }
                }
            });
        };

        $(function() {
            // Manejo de cambio de pestañas (Tabs)
            $('.nav-tabs a').on('click', function(e) {
                e.preventDefault();
                const target = $(this).attr('href');
                
                // 1. Manejo de la lista visual de pestañas (clase active en li)
                $('.nav-tabs li').removeClass('active');
                $(this).closest('li').addClass('active');

                // 2. Manejo de los paneles de contenido
                $('.tab-pane').hide().removeClass('active show');
                $(target).show().addClass('active show');
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

            // --- Lógica Dinámica de Planes de Pago ---

            // Agregar nueva fila de plan de pago
            $(document).on('click', '#btn-agregar-plan-pago', function(e) {
                e.preventDefault();
                $('#contenedor-planes-pago').append(htmlFilaPlanPago());
            });

            // Eliminar fila de plan de pago
            $(document).on('click', '.btn-eliminar-plan-pago', function(e) {
                e.preventDefault();
                $(this).closest('.fila-plan-pago').remove();
                
                // Reindexar inputs para que el array en PHP sea correlativo y no tenga huecos
                $('#contenedor-planes-pago .fila-plan-pago').each(function(index) {
                    $(this).find('.input-precio-plan').attr('name', `planes_pago[${index}][precio]`);
                    $(this).find('.select-moneda-plan').attr('name', `planes_pago[${index}][moneda]`);
                    $(this).find('.select-lapso-plan').attr('name', `planes_pago[${index}][lapso]`);
                });
            });

            // --- Lógica Dinámica de Beneficios ---

            // Agregar nueva fila de beneficio
            $(document).on('click', '#btn-agregar-beneficio', function(e) {
                e.preventDefault();
                $('#contenedor-beneficios').append(htmlFilaBeneficio());
            });

            // Eliminar fila de beneficio
            $(document).on('click', '.btn-eliminar-beneficio', function(e) {
                e.preventDefault();
                $(this).closest('.fila-beneficio').remove();
            });

            // Guardar Opciones Generales (Cantidad y Promedio)
            $('#form-configuracion-planes').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $btn = $('#btn-save-config-planes');
                const url = $form.attr('action');

                // Validación básica: mayor o igual a cero
                const cantidad = parseFloat($('#cantidad').val());
                const promedio = parseFloat($('#promedio_calificaciones').val());

                if (isNaN(cantidad) || cantidad < 0 || promedio < 0) {
                    const mensajeValidacion = window.RedaAlojamientoJson["Los valores deben ser mayores o iguales a cero"] || "Los valores deben ser mayores o iguales a cero";
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                        window.RedaNotificaciones.notificar(
                            window.RedaAlojamientoJson["¡Error!"] || "¡Error!",
                            mensajeValidacion,
                            'error'
                        );
                    } else {
                        alert(mensajeValidacion);
                    }
                    return false;
                }

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
                        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                            window.RedaNotificaciones.ocultar();
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
                
                // Iniciar con un plan de pago y un beneficio vacíos
                $('#contenedor-planes-pago').empty().append(htmlFilaPlanPago());
                $('#contenedor-beneficios').empty().append(htmlFilaBeneficio());
                
                $('#form-plan').attr('action', window.RedaRutas.store_plan);
                $('#modal-title-plan').text(window.RedaAlojamientoJson["Agregar nuevo plan"] || "Agregar nuevo plan");
                $('#modal-plan').modal('show');
            });

            // Abrir modal para VISUALIZAR PLAN (No modificable)
            $(document).on('click', '.btn-view-plan', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                idPlanActualVista = id;

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
                            $('#ver_nombre_plan').text(plan.nombre);
                            $('#ver_orden_plan').text(plan.orden);
                            
                            const badgeEstatus = plan.estatus 
                                ? `<span class="label label-success">${window.RedaAlojamientoJson["Activo"] || "Activo"}</span>`
                                : `<span class="label label-danger">${window.RedaAlojamientoJson["Inactivo"] || "Inactivo"}</span>`;
                            const badgeDestacado = plan.destacado ? ` <span class="label label-warning">Destacado</span>` : '';
                            $('#ver_estatus_plan').html(badgeEstatus + badgeDestacado);

                            // Cargar planes de pago
                            $('#ver_contenedor_planes_pago').empty();
                            let planesPago = [];
                            try {
                                planesPago = typeof plan.planes_pago === 'string' ? JSON.parse(plan.planes_pago) : (plan.planes_pago || []);
                            } catch (e) { planesPago = []; }

                            if (planesPago.length > 0) {
                                let htmlPago = '<div class="row">';
                                planesPago.forEach(p => {
                                    htmlPago += `
                                        <div class="col-sm-4 mb-2">
                                            <div class="p-2 border rounded bg-white shadow-xs">
                                                <small class="text-muted d-block f-10">${window.RedaAlojamientoJson["Precio"] || "Precio"}</small>
                                                <strong class="text-blue">${formatoMoneda(p.moneda, p.precio)}</strong>
                                                <small class="text-muted d-block f-10">${window.RedaAlojamientoJson["Lapso de pago"] || "Lapso de pago"}: ${window.RedaAlojamientoJson[p.lapso] || p.lapso}</small>
                                            </div>
                                        </div>
                                    `;
                                });
                                htmlPago += '</div>';
                                $('#ver_contenedor_planes_pago').html(htmlPago);
                            }

                            // Cargar beneficios
                            $('#ver_contenedor_beneficios').empty();
                            let beneficios = [];
                            try {
                                beneficios = typeof plan.beneficios === 'string' ? JSON.parse(plan.beneficios) : (plan.beneficios || []);
                            } catch (e) { beneficios = []; }

                            if (beneficios.length > 0) {
                                beneficios.forEach(b => {
                                    $('#ver_contenedor_beneficios').append(`
                                        <li class="list-group-item border-0 px-0 py-1" style="background: transparent;">
                                            <i class="fa fa-check text-green mr-2"></i> ${b}
                                        </li>
                                    `);
                                });
                            } else {
                                $('#ver_contenedor_beneficios').html(`<p class="text-muted">${window.RedaAlojamientoJson["No se definieron beneficios"] || "No se definieron beneficios"}</p>`);
                            }

                            $('#modal-ver-plan').modal('show');
                        }
                    },
                    complete: function() {
                        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                            window.RedaNotificaciones.ocultar();
                        }
                    }
                });
            });

            // Botón flotante de edición dentro del modal de vista
            $(document).on('click', '#btn-flotante-edit-plan', function(e) {
                e.preventDefault();
                if (idPlanActualVista) {
                    $('#modal-ver-plan').modal('hide');
                    // Pequeño delay para dejar que el modal se cierre antes de abrir el otro
                    setTimeout(() => {
                        abrirModalEdicion(idPlanActualVista);
                    }, 300);
                }
            });

            // Abrir modal para EDITAR PLAN
            $(document).on('click', '.btn-edit-plan', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                abrirModalEdicion(id);
            });

            // Guardar PLAN (Add/Update)
            $('#form-plan').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btn-save-plan');

                // Validaciones obligatorias de negocio
                const nombre = $('#nombre_plan').val().trim();
                const orden = $('#orden_plan').val();
                const planesPagoCount = $('#contenedor-planes-pago .fila-plan-pago').length;
                const beneficiosCount = $('#contenedor-beneficios .fila-beneficio').length;

                if (!nombre || orden === "" || planesPagoCount === 0 || beneficiosCount === 0) {
                    const mensajeValidacion = window.RedaAlojamientoJson["Por favor complete todos los campos obligatorios: Nombre, Orden, al menos un Plan de pago y al menos un Beneficio"] || "Por favor complete todos los campos obligatorios: Nombre, Orden, al menos un Plan de pago y al menos un Beneficio";
                    
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                        window.RedaNotificaciones.notificar(
                            window.RedaAlojamientoJson["¡Atención!"] || "¡Atención!",
                            mensajeValidacion,
                            'warning'
                        );
                    } else {
                        alert(mensajeValidacion);
                    }
                    return false;
                }

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
                        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                            window.RedaNotificaciones.ocultar();
                        }
                    }
                });
            });

            // Abrir Modal de Confirmación para eliminar PLAN (o una opción de pago específica)
            $(document).on('click', '.btn-delete-plan', function(e) {
                e.preventDefault();
                idPlanAEliminar = $(this).data('id');
                indexOpcionAEliminar = $(this).data('index'); // Puede ser undefined si se elimina el plan completo

                const mensaje = window.RedaAlojamientoJson["¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer."] || "¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.";
                
                $('#confirmacion-mensaje').text(mensaje);
                $('#modal-confirmacion').modal('show');
            });

            // Confirmar eliminación en el modal
            $(document).on('click', '#btn-confirmar-si', function(e) {
                e.preventDefault();
                const $btn = $(this);

                if (!idPlanAEliminar) return;

                $btn.prop('disabled', true);
                $btn.find('.fa-spinner').removeClass('d-none');
                $btn.find('.btn-text').addClass('d-none');

                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }

                $.ajax({
                    url: window.RedaRutas.destroy_plan + '/' + idPlanAEliminar,
                    type: 'DELETE',
                    data: { 
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        "index": indexOpcionAEliminar // Enviamos el índice para eliminar solo esa opción si existe
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#modal-confirmacion').modal('hide');
                            cargarPlanes();
                            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                                window.RedaNotificaciones.notificar(
                                    window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!",
                                    response.mensaje_usuario,
                                    'success'
                                );
                            }
                        } else {
                            alert(response.mensaje_usuario || response.message);
                        }
                    },
                    error: function() {
                        const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || "Error en el servidor de Torbian";
                        alert(mensajeErrorBase);
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.find('.fa-spinner').addClass('d-none');
                        $btn.find('.btn-text').removeClass('d-none');
                        
                        idPlanAEliminar = null;
                        indexOpcionAEliminar = null;

                        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                            window.RedaNotificaciones.ocultar();
                        }
                    }
                });
            });
        });
    }
})(jQuery);
