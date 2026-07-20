import { 
    todosSvg, 
    abiertosSvg, 
    enRevisionSvg, 
    esperandoRespuestaSvg, 
    resueltosSvg, 
    cerradosSvg 
} from '../../../general/iconos';

(function($) {
    "use strict";

    const containerId = '#indexDisputas';

    /**
     * Inyecta dinámicamente las pestañas de estatus en la cabecera del listado.
     */
    const inyectarPestanasEstatus = () => {
        const header = $('#disputas-tabs-header');
        if (!header.length) return;

        // Seguridad para traducciones
        const trans = window.RedaAlojamientoJson || {};

        const estatus = [
            { id: 'todos', nombre: trans["Todos"] || "Todos", icono: todosSvg, contador: 0 },
            { id: 'abiertos', nombre: trans["Abiertos"] || "Abiertos", icono: abiertosSvg, contador: 0 },
            { id: 'revision', nombre: trans["En revisión"] || "En revisión", icono: enRevisionSvg, contador: 0 },
            { id: 'espera', nombre: trans["Esperando respuesta"] || "Esperando respuesta", icono: esperandoRespuestaSvg, contador: 0 },
            { id: 'resueltos', nombre: trans["Resueltos"] || "Resueltos", icono: resueltosSvg, contador: 0 },
            { id: 'cerrados', nombre: trans["Cerrados"] || "Cerrados", icono: cerradosSvg, contador: 0 }
        ];

        let html = `<div class="d-flex flex-wrap border-bottom pb-2 reda-tabs-nav">`;
        
        estatus.forEach((e, index) => {
            const isActive = index === 0 ? 'active' : '';
            html += `
                <div class="disputa-tab-item px-3 py-2 mr-2 pointer text-center ${isActive}" data-status="${e.id}">
                    <div class="d-flex align-items-center justify-content-center mb-1 status-icon">
                        ${e.icono}
                    </div>
                    <div class="d-flex align-items-center justify-content-center">
                        <span class="text-14 font-weight-600">${e.nombre}</span>
                        <span class="badge badge-pill badge-success ml-2 text-10 d-none status-counter" id="counter-${e.id}">${e.contador}</span>
                    </div>
                </div>
            `;
        });

        html += `</div>`;
        header.html(html);
    };

    /**
     * Renderiza el listado de mediaciones con el diseño de cuatro columnas.
     */
    const renderizarLista = (items) => {
        const container = $('#disputas-list-container');
        const trans = window.RedaAlojamientoJson || {};

        if (!items || !items.length) {
            container.html(`
                <div class="row justify-content-center w-100 p-4 mt-4">
                    <div class="text-center w-100">
                        <img src="${APP_URL}/public/img/unnamed.png" class="img-fluid" alt="No encontrado" style="max-width: 150px;">
                        <p class="text-center mt-3">${trans["No se encontraron mediaciones."] || "No se encontraron mediaciones."}</p>
                    </div>
                </div>
            `);
            return;
        }

        let html = '<div class="row mt-4">';
        items.forEach(item => {
            // Agente: Foto y nombre o Pendiente
            const agenteFoto = item.agente ? item.agente.foto : `${APP_URL}/public/img/unnamed.png`;
            const agenteNombre = item.agente ? item.agente.nombre : trans["Pendiente de asignación"] || "Pendiente de asignación";
            const agenteIcono = item.agente ? 'fas fa-user-tie' : 'fas fa-user-clock';

            html += `
                <div class="col-md-12 p-0 mb-4">
                    <div class="card h-100 border rounded-3 card-mediacion pointer shadow-sm-hover" data-id="${item.id}">
                        <div class="card-body p-0">
                            <div class="row m-0">
                                <div class="col-md-3 p-0">
                                    <div class="img-container h-100 bg-light d-flex align-items-center justify-content-center border-right">
                                        <img src="${item.propiedad_foto}" 
                                             class="img-fluid w-100 h-100 object-fit-cover rounded-start img-min-150" 
                                             alt="Propiedad"
                                             style="max-height: 200px;">
                                    </div>
                                </div>

                                <div class="col-md-4 col-xl-4 p-4 border-right">
                                    <div class="mb-2">
                                        <span class="badge bg-orange text-white text-uppercase">${item.estado}</span>
                                        <span class="text-muted small ml-2">ID: #${item.id}</span>
                                    </div>

                                    <h5 class="text-18 font-weight-700 text-color mb-1">${item.motivo}</h5>
                                    
                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-bookmark mr-1"></i> ${trans["Reservación"] || "Reservación"}: <span class="font-weight-700 text-dark">#${item.booking_id}</span>
                                    </div>

                                    <div class="d-flex flex-column mt-3">
                                        <div class="text-muted small mb-1">
                                            <i class="far fa-calendar-alt mr-1"></i> ${trans["Creado el"] || "Creado el"}: <span class="text-dark">${item.fecha_apertura}</span>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="far fa-clock mr-1"></i> ${trans["Actualizado"] || "Actualizado"}: <span class="font-weight-700 text-dark">${item.actualizado_hace}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-xl-3 p-4 d-flex flex-column justify-content-center border-right bg-light-soft">
                                    <div class="text-center">
                                        <div class="mb-2 position-relative d-inline-block">
                                            <img src="${agenteFoto}" 
                                                 class="rounded-circle border" 
                                                 style="width: 65px; height: 65px; object-fit: cover;"
                                                 alt="Agente">
                                            <div class="position-absolute" style="bottom: 0; right: 0;">
                                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px; border: 1px solid #ddd;">
                                                    <i class="${agenteIcono} text-10 text-success"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block mb-1">${trans["Mediador"] || "Mediador"}</span>
                                            <span class="font-weight-700 text-dark text-14">${agenteNombre}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2 col-xl-2 p-4 d-flex flex-row flex-md-column justify-content-center align-items-center">
                                    <a href="javascript:void(0)" class="btn-list-action btn-edit m-2 btn-ver-detalle" data-id="${item.id}">
                                        <span class="btn-list-text">${trans["Ver Detalle"] || "Ver Detalle"}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);
    };

    /**
     * Carga las mediaciones vía Ajax según el estatus y página seleccionada.
     */
    const cargarMediaciones = async (status, page = 1) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }

        console.log(`Cargando mediaciones para el estatus: ${status}, página: ${page}`);

        $.ajax({
            url: APP_URL + '/reda/disputas/paginadas',
            type: 'GET',
            data: { status, page },
            success: function(data) {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                    window.RedaNotificaciones.ocultar();
                }

                if (data.success) {
                    renderizarLista(data.respuesta.data);
                    $('#disputas-pagination-container').html(data.respuesta.pagination);
                }
            },
            error: function() {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                    window.RedaNotificaciones.ocultar();
                }
                const trans = window.RedaAlojamientoJson || {};
                $('#disputas-list-container').html(`
                    <div class="alert alert-danger mt-4">
                        ${trans["Error al cargar las mediaciones. Intente de nuevo."] || "Error al cargar las mediaciones. Intente de nuevo."}
                    </div>
                `);
            }
        });
    };

    /**
     * Abre el modal de detalle de la mediación.
     */
    const abrirDetalleMediacion = (id) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }

        $.ajax({
            url: APP_URL + '/reda/disputas/get-detail-modal/' + id,
            type: 'GET',
            success: function(html) {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                    window.RedaNotificaciones.ocultar();
                }

                // Remover modal previo si existe
                $('#modal-detalle-mediacion-reda').remove();
                $('body').append(html);
                $('#modal-detalle-mediacion-reda').modal('show');
            },
            error: function() {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                    window.RedaNotificaciones.ocultar();
                }
            }
        });
    };

    $(function() {
        if ($(containerId).length) {
            console.log('Script para "Index Disputas" (Dashboard) cargado correctamente.');
            inyectarPestanasEstatus();

            // Manejo de clicks en las pestañas
            $(document).on('click', '.disputa-tab-item', function() {
                const item = $(this);
                if (item.hasClass('active')) return;

                $('.disputa-tab-item').removeClass('active');
                item.addClass('active');

                const status = item.attr('data-status');
                cargarMediaciones(status);
            });

            // Manejo de clicks en los items de la lista para ver detalle
            $(document).on('click', '.card-mediacion, .btn-ver-detalle', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                abrirDetalleMediacion(id);
            });

            // Manejo de paginación (si se inyecta vía Ajax)
            $(document).on('click', '#disputas-pagination-container .pagination a', function(e) {
                e.preventDefault();
                const pageUrl = $(this).attr('href');
                if (!pageUrl) return;

                const urlParams = new URLSearchParams(new URL(pageUrl).search);
                const page = urlParams.get('page');
                const status = $('.disputa-tab-item.active').attr('data-status') || 'todos';

                cargarMediaciones(status, page);
            });

            // Carga inicial (Todos)
            cargarMediaciones('todos');
        }
    });

})(jQuery);
