(function( $ ) {
    "use strict";
    const containerId = '#configuracion_planes_container';
    if ($(containerId).length) {
        console.log('Script para "Configuración de Planes" cargado correctamente.');
        
        // Variables de estado
        let idPlanAEliminar = null;
        let indexOpcionAEliminar = null;
        let idPlanActualVista = null;

        // --- FUNCIONES API (PROMESAS) ---

        const apiCargarPlanes = (url = null) => {
            return new Promise((resolve) => {
                const targetUrl = url || window.RedaRutas.index_planes;
                $.ajax({
                    url: targetUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: (data) => resolve(data),
                    error: (x) => {
                        let res = {}; try { res = JSON.parse(x.responseText); } catch (e) {}
                        resolve({
                            success: false,
                            mensaje_usuario: res.mensaje_usuario || window.RedaAlojamientoJson["Error cargando planes"] || "Error cargando planes",
                            code: x.status
                        });
                    }
                });
            });
        };

        const apiGetPlan = (id) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: window.RedaRutas.get_plan + '/' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: (data) => resolve(data),
                    error: (x) => {
                        let res = {}; try { res = JSON.parse(x.responseText); } catch (e) {}
                        resolve({
                            success: false,
                            mensaje_usuario: res.mensaje_usuario || window.RedaAlojamientoJson["Error obteniendo plan"] || "Error obteniendo plan",
                            code: x.status
                        });
                    }
                });
            });
        };

        const apiStorePlan = (formData) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: window.RedaRutas.store_plan,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: (data) => resolve(data),
                    error: (x) => {
                        let res = {}; try { res = JSON.parse(x.responseText); } catch (e) {}
                        resolve({
                            success: false,
                            mensaje_usuario: res.mensaje_usuario || window.RedaAlojamientoJson["Error guardando plan"] || "Error guardando plan",
                            code: x.status
                        });
                    }
                });
            });
        };

        const apiUpdatePlan = (formData) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: window.RedaRutas.update_plan,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: (data) => resolve(data),
                    error: (x) => {
                        let res = {}; try { res = JSON.parse(x.responseText); } catch (e) {}
                        resolve({
                            success: false,
                            mensaje_usuario: res.mensaje_usuario || window.RedaAlojamientoJson["Error actualizando plan"] || "Error actualizando plan",
                            code: x.status
                        });
                    }
                });
            });
        };

        const apiDestroyPlan = (id, index = null) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: window.RedaRutas.destroy_plan + '/' + id,
                    type: 'DELETE',
                    data: { 
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        "index": index 
                    },
                    success: (data) => resolve(data),
                    error: (x) => {
                        let res = {}; try { res = JSON.parse(x.responseText); } catch (e) {}
                        resolve({
                            success: false,
                            mensaje_usuario: res.mensaje_usuario || window.RedaAlojamientoJson["Error eliminando plan"] || "Error eliminando plan",
                            code: x.status
                        });
                    }
                });
            });
        };

        const apiStoreConfigGenerales = (formData) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: window.RedaRutas.store_config_planes,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: (data) => resolve(data),
                    error: (x) => {
                        let res = {}; try { res = JSON.parse(x.responseText); } catch (e) {}
                        resolve({
                            success: false,
                            mensaje_usuario: res.mensaje_usuario || window.RedaAlojamientoJson["Error guardando configuración"] || "Error guardando configuración",
                            code: x.status
                        });
                    }
                });
            });
        };

        // --- HELPERS VISUALES ---

        /**
         * Carga el listado de planes (Helper visual con animación)
         */
        const cargarPlanes = async (url = null) => {
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }
            
            const res = await apiCargarPlanes(url);
            
            if (res.success) {
                $('#contenedor-tabla-planes').html(res.respuesta);
                // Reinicializar tooltips
                $('[data-toggle="tooltip"]').tooltip();
            } else {
                alert(res.mensaje_usuario);
            }
            
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                window.RedaNotificaciones.ocultar();
            }
        };

        const formatoMoneda = (moneda, valor) => {
            const simbolo = moneda === 'dólar' ? '$' : 'Bs';
            return `${simbolo} ${parseFloat(valor).toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        };

        const htmlFilaPlanPago = (datos = {}) => {
            const index = $('#contenedor-planes-pago .fila-plan-pago').length;
            const precio = datos.precio || '';
            const moneda = datos.moneda || 'dólar';
            const lapso = datos.lapso || 'anual';
            return `
                <div class="row fila-plan-pago mb-2 planes-negocio-fila-plan-pago">
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
                                <option value="semestral" ${lapso === 'semestral' ? 'selected' : ''}>${window.RedaAlojamientoJson["Semestral"] || "Semestral"}</option>
                                <option value="trimestral" ${lapso === 'trimestral' ? 'selected' : ''}>${window.RedaAlojamientoJson["Trimestral"] || "Trimestral"}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 planes-negocio-align-end-pb-15">
                        <button type="button" class="btn btn-danger btn-flat btn-sm btn-eliminar-plan-pago" title="${window.RedaAlojamientoJson["Eliminar"] || "Eliminar"}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        };

        const htmlFilaBeneficio = (valor = '') => {
            return `
                <div class="row fila-beneficio mb-2 planes-negocio-fila-beneficio">
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
         * Abre el modal de edición para un plan específico
         */
        const abrirModalEdicion = async (id) => {
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }

            const res = await apiGetPlan(id);
            
            if (res.success) {
                const plan = res.respuesta;
                $('#form-plan')[0].reset();
                $('#plan_id').val(plan.id);
                $('#nombre_plan').val(plan.nombre);
                $('#orden_plan').val(plan.orden);
                $('#destacado_plan').prop('checked', plan.destacado);
                $('#estatus_plan').prop('checked', plan.estatus);
                
                // Cargar opciones de pago dinámicas
                $('#contenedor-planes-pago').empty();
                let planesPago = plan.planes_pago || [];
                if (planesPago.length > 0) {
                    planesPago.forEach(p => {
                        $('#contenedor-planes-pago').append(htmlFilaPlanPago(p));
                    });
                } else {
                    $('#contenedor-planes-pago').append(htmlFilaPlanPago());
                }

                // Cargar beneficios dinámicos
                $('#contenedor-beneficios').empty();
                let beneficios = plan.beneficios || [];
                if (beneficios.length > 0) {
                    beneficios.forEach(b => {
                        $('#contenedor-beneficios').append(htmlFilaBeneficio(b));
                    });
                } else {
                    $('#contenedor-beneficios').append(htmlFilaBeneficio());
                }

                $('#form-plan').attr('action', window.RedaRutas.update_plan);
                $('#modal-title-plan').text(window.RedaAlojamientoJson["Editar plan"] || "Editar plan");
                $('#modal-plan').modal('show');
            } else {
                alert(res.mensaje_usuario);
            }
            
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                window.RedaNotificaciones.ocultar();
            }
        };

        // --- EVENTOS ---

        $(function() {
            // Manejo de cambio de pestañas (Tabs)
            $('.nav-tabs a').on('click', function(e) {
                e.preventDefault();
                const target = $(this).attr('href');
                
                $('.nav-tabs li').removeClass('active');
                $(this).closest('li').addClass('active');

                // Usamos clases de Bootstrap para ocultar/mostrar en lugar de .hide()/.show() (estilos en línea)
                $('.tab-pane').addClass('d-none').removeClass('active show');
                $(target).removeClass('d-none').addClass('active show');
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

            // Lógica para el selector dinámico de lapsos/precios en el listado
            $(document).on('click', '.planes-negocio-btn-lapso', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const $container = $btn.closest('.planes-negocio-selector-container');
                const price = $btn.data('price');
                const lapso = $btn.data('lapso');

                // Actualizar estado activo de los botones en este grupo
                $btn.siblings().removeClass('active');
                $btn.addClass('active');

                // Actualizar el display del precio
                $container.find('.planes-negocio-price-amount').text(price);
                $container.find('.planes-negocio-price-label').text('/ ' + lapso);
            });

            // Agregar nueva fila de opción de pago
            $(document).on('click', '#btn-agregar-plan-pago', function(e) {
                e.preventDefault();
                $('#contenedor-planes-pago').append(htmlFilaPlanPago());
            });

            // Eliminar fila de opción de pago
            $(document).on('click', '.btn-eliminar-plan-pago', function(e) {
                e.preventDefault();
                $(this).closest('.fila-plan-pago').remove();
                
                $('#contenedor-planes-pago .fila-plan-pago').each(function(index) {
                    $(this).find('.input-precio-plan').attr('name', `planes_pago[${index}][precio]`);
                    $(this).find('.select-moneda-plan').attr('name', `planes_pago[${index}][moneda]`);
                    $(this).find('.select-lapso-plan').attr('name', `planes_pago[${index}][lapso]`);
                });
            });

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

            // Guardar Opciones Generales (Antiguedad y Promedio)
            $('#form-configuracion-planes').on('submit', async function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btn-save-config-planes');

                window.RedaNotificaciones?.esperar();
                $btn.prop('disabled', true).find('.fa-spinner').removeClass('d-none');

                const res = await apiStoreConfigGenerales($form.serialize());

                if (res.success) {
                    window.RedaNotificaciones?.notificar(
                        window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!",
                        res.mensaje_usuario,
                        'success'
                    );
                } else {
                    alert(res.mensaje_usuario);
                }

                $btn.prop('disabled', false).find('.fa-spinner').addClass('d-none');
                window.RedaNotificaciones?.ocultar();
            });

            // Abrir modal para AGREGAR PLAN
            $(document).on('click', '#btn-add-plan', function(e) {
                e.preventDefault();
                $('#form-plan')[0].reset();
                $('#plan_id').val('');
                
                $('#contenedor-planes-pago').empty().append(htmlFilaPlanPago());
                $('#contenedor-beneficios').empty().append(htmlFilaBeneficio());
                
                $('#form-plan').attr('action', window.RedaRutas.store_plan);
                $('#modal-title-plan').text(window.RedaAlojamientoJson["Agregar nuevo plan"] || "Agregar nuevo plan");
                $('#modal-plan').modal('show');
            });

            // Abrir modal para VISUALIZAR PLAN (No modificable)
            $(document).on('click', '.btn-view-plan', async function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                idPlanActualVista = id;

                window.RedaNotificaciones?.esperar();
                const res = await apiGetPlan(id);
                
                if (res.success) {
                    const plan = res.respuesta;
                    $('#ver_nombre_plan').text(plan.nombre);
                    $('#ver_orden_plan').text(plan.orden);
                    
                    const badgeEstatus = plan.estatus 
                        ? `<span class="label label-success">${window.RedaAlojamientoJson["Activo"] || "Activo"}</span>`
                        : `<span class="label label-danger">${window.RedaAlojamientoJson["Inactivo"] || "Inactivo"}</span>`;
                    const badgeDestacado = plan.destacado ? ` <span class="label label-warning">${window.RedaAlojamientoJson["Destacado"] || "Destacado"}</span>` : '';
                    $('#ver_estatus_plan').html(badgeEstatus + badgeDestacado);

                    $('#ver_contenedor_planes_pago').empty();
                    let planesPago = plan.planes_pago || [];
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

                    $('#ver_contenedor_beneficios').empty();
                    let beneficios = plan.beneficios || [];
                    if (beneficios.length > 0) {
                        beneficios.forEach(b => {
                            $('#ver_contenedor_beneficios').append(`
                                <li class="list-group-item border-0 px-0 py-1 bg-transparent">
                                    <i class="fa fa-check text-green mr-2"></i> ${b}
                                </li>
                            `);
                        });
                    } else {
                        $('#ver_contenedor_beneficios').html(`<p class="text-muted">${window.RedaAlojamientoJson["No se definieron beneficios"] || "No se definieron beneficios"}</p>`);
                    }

                    $('#modal-ver-plan').modal('show');
                } else {
                    alert(res.mensaje_usuario);
                }
                window.RedaNotificaciones?.ocultar();
            });

            // Botón flotante de edición dentro del modal de vista
            $(document).on('click', '#btn-flotante-edit-plan', function(e) {
                e.preventDefault();
                if (idPlanActualVista) {
                    $('#modal-ver-plan').modal('hide');
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
            $('#form-plan').on('submit', async function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#btn-save-plan');

                const nombre = $('#nombre_plan').val().trim();
                const planesPagoCount = $('#contenedor-planes-pago .fila-plan-pago').length;
                const beneficiosCount = $('#contenedor-beneficios .fila-beneficio').length;

                if (!nombre || planesPagoCount === 0 || beneficiosCount === 0) {
                    alert(window.RedaAlojamientoJson["Por favor complete todos los campos obligatorios"] || "Por favor complete todos los campos obligatorios");
                    return false;
                }

                window.RedaNotificaciones?.esperar();
                $btn.prop('disabled', true).find('.fa-spinner').removeClass('d-none');
                
                const isUpdate = $('#plan_id').val() !== '';
                const res = isUpdate ? await apiUpdatePlan($form.serialize()) : await apiStorePlan($form.serialize());
                
                if (res.success) {
                    $('#modal-plan').modal('hide');
                    await cargarPlanes();
                    window.RedaNotificaciones?.notificar(
                        window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!",
                        res.mensaje_usuario,
                        'success'
                    );
                } else {
                    alert(res.mensaje_usuario);
                }
                
                $btn.prop('disabled', false).find('.fa-spinner').addClass('d-none');
                window.RedaNotificaciones?.ocultar();
            });

            // Abrir Modal de Confirmación para eliminar
            $(document).on('click', '.btn-delete-plan', function(e) {
                e.preventDefault();
                idPlanAEliminar = $(this).data('id');
                indexOpcionAEliminar = $(this).data('index');

                const mensaje = window.RedaAlojamientoJson["¿Estás seguro de que deseas eliminar este elemento?"] || "¿Estás seguro de que deseas eliminar este elemento?";
                
                $('#confirmacion-mensaje').text(mensaje);
                $('#modal-confirmacion').modal('show');
            });

            // Confirmar eliminación en el modal
            $(document).on('click', '#btn-confirmar-si', async function(e) {
                e.preventDefault();
                const $btn = $(this);

                if (!idPlanAEliminar) return;

                window.RedaNotificaciones?.esperar();
                $btn.prop('disabled', true).find('.fa-spinner').removeClass('d-none');
                
                const res = await apiDestroyPlan(idPlanAEliminar, indexOpcionAEliminar);
                
                if (res.success) {
                    $('#modal-confirmacion').modal('hide');
                    await cargarPlanes();
                    window.RedaNotificaciones?.notificar(
                        window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!",
                        res.mensaje_usuario,
                        'success'
                    );
                } else {
                    alert(res.mensaje_usuario);
                }
                
                $btn.prop('disabled', false).find('.fa-spinner').addClass('d-none');
                idPlanAEliminar = null;
                indexOpcionAEliminar = null;
                window.RedaNotificaciones?.ocultar();
            });
        });
    }
})(jQuery);
