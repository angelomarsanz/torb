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
    let mediacionesCargadas = [];
    let mediacionSeleccionadaId = null;
    let observadorEnfoque = null;

    /**
     * Formatea el texto del estatus: Inicial mayúscula, resto minúscula.
     */
    const formatStatusText = (text) => {
        if (!text) return '';
        return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
    };

    /**
     * Devuelve la clase de color adecuada para el badge según el estatus.
     */
    const getStatusBadgeClass = (status) => {
        const s = status.toLowerCase();
        if (s.includes('abiert')) return 'bg-orange text-white';
        if (s.includes('revis')) return 'bg-info text-white';
        if (s.includes('espera')) return 'bg-warning text-dark';
        if (s.includes('resuelt') || s.includes('cerrad')) return 'bg-success text-white';
        return 'bg-secondary text-white';
    };

    /**
     * Peticion AJAX para obtener mediaciones paginadas.
     */
    const getMediacionesPaginadas = (status, page) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/reda/disputas/paginadas',
                type: 'GET',
                data: { status, page },
                success: (data) => resolve(data),
                error: (x) => {
                    let respuestaServidor = {};
                    try { respuestaServidor = JSON.parse(x.responseText); } catch (e) { respuestaServidor = {}; }
                    resolve({
                        success: false,
                        mensaje_usuario: respuestaServidor.mensaje_usuario || (window.RedaAlojamientoJson["Error al cargar las mediaciones. Intente de nuevo."] || "Error al cargar las mediaciones. Intente de nuevo."),
                        code: x.status || 500
                    });
                }
            });
        });
    };

    /**
     * Peticion AJAX para obtener el HTML del modal de detalle.
     */
    const getHtmlDetalleMediacion = (id) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/reda/disputas/get-detail-modal/' + id,
                type: 'GET',
                success: (data) => resolve(data),
                error: (x) => {
                    let respuestaServidor = {};
                    try { respuestaServidor = JSON.parse(x.responseText); } catch (e) { respuestaServidor = {}; }
                    resolve({
                        success: false,
                        mensaje_usuario: respuestaServidor.mensaje_usuario || (window.RedaAlojamientoJson["Error al cargar el detalle."] || "Error al cargar el detalle."),
                        code: x.status || 500
                    });
                }
            });
        });
    };

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
     * Genera el HTML de la línea de tiempo.
     */
    const generarTimelineHtml = (pasoActual) => {
        const trans = window.RedaAlojamientoJson || {};
        const pasos = [
            { id: 1, nombre: trans["Caso creado"] || "Caso creado", icono: 'fas fa-plus' },
            { id: 2, nombre: trans["Asignación a agente"] || "Asignación a agente", icono: 'fas fa-user-tie' },
            { id: 3, nombre: trans["En revisión"] || "En revisión", icono: 'fas fa-search' },
            { id: 4, nombre: trans["Solicitud de información adicional"] || "Solicitud de información adicional", icono: 'fas fa-info-circle' },
            { id: 5, nombre: trans["Análisis del caso"] || "Análisis del caso", icono: 'fas fa-balance-scale' },
            { id: 6, nombre: trans["Resuelto o escalado"] || "Resuelto o escalado", icono: 'fas fa-check-double' }
        ];

        const currentIndex = pasos.findIndex(p => p.nombre === pasoActual);
        
        let html = '';
        pasos.forEach((p, index) => {
            let statusClass = '';
            if (index < currentIndex) {
                statusClass = 'completed';
            } else if (index === currentIndex) {
                statusClass = 'active';
            }

            html += `
                <div class="timeline-item ${statusClass}" data-index="${index}">
                    <div class="timeline-icon">
                        <i class="${p.icono}"></i>
                    </div>
                    <span class="timeline-text">${p.nombre}</span>
                </div>
            `;
        });

        return { html, currentIndex };
    };

    /**
     * Renderiza la línea de tiempo en un contenedor específico.
     */
    const renderizarTimeline = (pasoActual, containerSelector = '#reda-timeline-container') => {
        const container = $(containerSelector);
        if (!container.length) return;

        const { html, currentIndex } = generarTimelineHtml(pasoActual);
        container.html(html);

        if (currentIndex !== -1) {
            const activeItem = container.find(`.timeline-item[data-index="${currentIndex}"]`);
            if (activeItem.length) {
                const scrollPos = activeItem.position().left + container.scrollLeft() - (container.width() / 2) + (activeItem.width() / 2);
                container.animate({ scrollLeft: scrollPos }, 500);
            }
        }
    };

    /**
     * Renderiza el bloque unificado de personas relacionadas.
     */
    const generarBloquePersonasHtml = (item) => {
        const trans = window.RedaAlojamientoJson || {};
        const agenteFoto = item.agente ? item.agente.foto : `${APP_URL}/public/img/unnamed.png`;
        const agenteNombre = item.agente ? item.agente.nombre : trans["Pendiente de asignación"] || "Pendiente de asignación";
        const agenteIcono = item.agente ? 'fas fa-user-tie' : 'fas fa-user-clock';
        const agenteClaseNombre = item.agente ? 'text-dark' : 'text-muted italic small leading-tight';

        return `
            <div class="personas-relacionadas-block">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar-mini mr-2">
                        <img src="${item.anfitrion_foto}" class="rounded-circle border reda-avatar-30">
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-muted text-10 leading-tight">${trans["Anfitrión:"] || "Anfitrión:"}</span>
                        <span class="text-dark text-12 text-truncate">${item.anfitrion_nombre}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar-mini mr-2">
                        <img src="${item.turista_foto}" class="rounded-circle border reda-avatar-30">
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-muted text-10 leading-tight">${trans["Turista:"] || "Turista:"}</span>
                        <span class="text-dark text-12 text-truncate">${item.turista_nombre}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="avatar-mini mr-2 position-relative">
                        <img src="${agenteFoto}" class="rounded-circle border reda-avatar-30">
                        <div class="position-absolute reda-status-badge-pos">
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm reda-status-badge-icon">
                                <i class="${agenteIcono} text-6 text-success"></i>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-muted text-10 leading-tight">${trans["Agente:"] || "Agente:"}</span>
                        <span class="${agenteClaseNombre} text-12 text-truncate">${agenteNombre}</span>
                    </div>
                </div>
            </div>
        `;
    };

    /**
     * Genera el HTML de la lista de adjuntos.
     */
    const generarListaAdjuntosHtml = (adjuntos) => {
        const trans = window.RedaAlojamientoJson || {};
        if (!adjuntos || !adjuntos.length) {
            return `<p class="text-11 text-muted italic">${trans["Sin archivos adjuntos"] || "Sin archivos adjuntos"}</p>`;
        }

        let html = '<div class="list-group list-group-flush border-top border-bottom">';
        adjuntos.forEach(file => {
            const icon = file.es_imagen ? 'far fa-image' : 'far fa-file-alt';
            html += `
                <a href="${file.url}" target="_blank" class="list-group-item list-group-item-action py-2 px-0 d-flex align-items-center border-0 bg-transparent">
                    <div class="mr-2 bg-light-soft rounded d-flex align-items-center justify-content-center reda-adjunto-icon-box">
                        <i class="${icon} text-success text-10"></i>
                    </div>
                    <span class="text-11 text-dark text-truncate" title="${file.nombre}">${file.nombre}</span>
                </a>
            `;
        });
        html += '</div>';
        return html;
    };

    /**
     * Funcion auxiliar para detectar si un texto necesita el boton "Mas..."
     */
    const chequearTruncadoTexto = (selectorBox, selectorBtn) => {
        setTimeout(() => {
            const box = document.querySelector(selectorBox);
            const btn = document.querySelector(selectorBtn);
            if (box && btn) {
                if (box.scrollHeight > box.clientHeight + 2) {
                    btn.classList.remove('d-none');
                } else {
                    btn.classList.add('d-none');
                }
            }
        }, 50);
    };

    /**
     * Renderiza la cabecera informativa de la mediación (Estatus, ID, Motivo).
     */
    const renderizarCabeceraMediacion = (item, containerSelector) => {
        const container = $(containerSelector);
        if (!container.length) return;
        const trans = window.RedaAlojamientoJson || {};
        const badgeClass = getStatusBadgeClass(item.estado);
        const statusText = formatStatusText(item.estado);

        let html = `
            <div class="mediacion-cabecera-principal mb-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge ${badgeClass} font-weight-700 py-2 px-3 text-12 shadow-sm">${statusText}</span>
                    <span class="text-muted font-weight-700 text-14">ID: #${item.id}</span>
                </div>
                <h5 class="font-weight-700 text-dark text-20 mb-0 leading-tight reda-motivo-clamped" id="motivo-container-${item.id}" title="${item.motivo}">${item.motivo}</h5>
                <div class="text-right mt-1">
                    <span class="text-success pointer font-weight-700 text-11 btn-toggle-motivo d-none" 
                          id="btn-toggle-motivo-${item.id}" data-id="${item.id}" data-state="less">
                        ${trans["Más..."] || "Más..."}
                    </span>
                </div>
            </div>
        `;
        container.html(html);

        // Chequear truncado del motivo (si es muy largo)
        chequearTruncadoTexto(`#motivo-container-${item.id}`, `#btn-toggle-motivo-${item.id}`);

        // Mostrar el card contenedor si estaba oculto
        if (containerSelector === '#disputas-header-content') {
            $('#disputas-cabecera-lateral').removeClass('d-none');
        }
    };

    /**
     * Renderiza el cuerpo del detalle de la mediación (Sección colapsable).
     */
    const renderizarResumenMediacion = (item, containerSelector) => {
        const container = $(containerSelector);
        if (!container.length) return;
        const trans = window.RedaAlojamientoJson || {};

        // Prioridad con color
        let prioridadHtml = '';
        if (item.prioridad) {
            let colorClass = 'text-info';
            if (item.prioridad === 'Alta') colorClass = 'text-danger';
            else if (item.prioridad === 'Media') colorClass = 'text-warning';
            prioridadHtml = `<span class="${colorClass}">${item.prioridad}</span>`;
        }

        let html = `
            <div class="mediacion-resumen-detalle">
                <!-- Sección de detalle (Visible por defecto) -->
                <div class="detalles-extra-collapsible mb-0" id="detalles-extra-${item.id}">
                    <div class="pt-1">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">${trans["Prioridad"] || "Prioridad"}</span>
                            ${prioridadHtml}
                        </div>
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <span class="text-muted small">${trans["Creado el"] || "Creado el"}</span>
                            <span class="text-muted small">${item.fecha_apertura}</span>
                        </div>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <span class="text-muted small d-block mb-1">${trans["Descripción"] || "Descripción"}</span>
                        <div class="text-13 text-muted p-2 bg-light rounded reda-mediation-desc-clamped" id="desc-container-${item.id}">
                            ${item.descripcion || "<i>" + (trans["Sin descripción"] || "Sin descripción") + "</i>"}
                        </div>
                        <div class="text-right mt-1">
                            <span class="text-success pointer font-weight-700 text-11 btn-toggle-desc-larga d-none" id="btn-toggle-desc-${item.id}" data-id="${item.id}" data-state="less">
                                ${trans["Más..."] || "Más..."}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-12 font-weight-700 mb-2 text-muted text-uppercase letter-spacing-1">${trans["Archivos adjuntos"] || "Archivos adjuntos"}</h6>
                        <div class="adjuntos-container">
                            ${generarListaAdjuntosHtml(item.adjuntos)}
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top bg-light-soft p-3 rounded border">
                        <h6 class="text-12 font-weight-700 mb-3 text-muted text-uppercase letter-spacing-1">${trans["Personas relacionadas"] || "Personas relacionadas"}</h6>
                        ${generarBloquePersonasHtml(item)}

                        <div class="text-right mt-3 border-top pt-2">
                            <p class="text-10 text-muted italic mb-0">
                                <i class="far fa-clock mr-1"></i> ${item.actualizado_hace}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.html(html);

        // Re-chequear truncado de descripción inmediatamente al renderizar
        setTimeout(() => chequearTruncadoDescripcion(item.id), 50);

        // Inyectar botón de conversación en la sección independiente
        const isDesktop = (containerSelector === '#disputas-info-extra-content');
        const actionsContainerId = isDesktop 
            ? '#disputas-conversacion-container' 
            : `#mobile-acciones-${item.id}`;
        
        const actionsContainer = $(actionsContainerId);
        if (actionsContainer.length) {
            if (isDesktop) {
                $('#disputas-acciones-sidebar').removeClass('d-none');
            }

            actionsContainer.html(`
                <div class="mt-2">
                    <a href="${APP_URL}/inbox?id=${item.booking_id}" class="btn btn-success btn-block font-weight-700 text-14 py-2 reda-btn-inbox-direct">
                        <i class="far fa-comments mr-2"></i>
                        ${trans["Ver conversación"] || "Ver conversación"}
                    </a>
                </div>
            `);
        }
    };

    /**
     * Funcion auxiliar para detectar si la descripcion necesita el boton "Mas..."
     */
    const chequearTruncadoDescripcion = (id) => {
        setTimeout(() => {
            const descBox = document.getElementById(`desc-container-${id}`);
            const btn = document.getElementById(`btn-toggle-desc-${id}`);
            if (descBox && btn) {
                // Si la altura del contenido es mayor a la altura visible (clamped), mostramos el botón
                if (descBox.scrollHeight > descBox.clientHeight + 2) { // +2 por seguridad de bordes/paddings
                    btn.classList.remove('d-none');
                } else {
                    btn.classList.add('d-none');
                }
            }
        }, 50);
    };

    /**
     * Inicializa un observador para detectar qué mediación está en el centro visual del móvil.
     */
    const inicializarObservadorEnfoque = () => {
        if (window.innerWidth >= 768) return;
        
        if (observadorEnfoque) {
            observadorEnfoque.disconnect();
        }

        const options = {
            root: null,
            rootMargin: '-30% 0px -30% 0px', // Área central de detección
            threshold: 0.6
        };

        observadorEnfoque = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = $(entry.target).attr('data-id');
                    if (id && id != mediacionSeleccionadaId) {
                        seleccionarMediacion(id, false); // No scroll automático en scroll manual
                    }
                }
            });
        }, options);

        $('.container-mediacion').each(function() {
            observadorEnfoque.observe(this);
        });
    };

    /**
     * Maneja la selección de una mediación.
     */
    const seleccionarMediacion = (id, conScroll = false) => {
        if (mediacionSeleccionadaId == id && !conScroll) return;
        
        mediacionSeleccionadaId = id;
        const item = mediacionesCargadas.find(m => m.id == id);
        if (!item) return;

        // 1. Resaltar la tarjeta visualmente
        $('.card-mediacion').removeClass('active-mediacion');
        const activeCard = $(`.card-mediacion[data-id="${id}"]`);
        activeCard.addClass('active-mediacion');

        // 2. Actualizar Secciones Laterales (Escritorio)
        renderizarCabeceraMediacion(item, '#disputas-header-content');
        renderizarTimeline(item.paso_actual, '#reda-timeline-container');
        renderizarResumenMediacion(item, '#disputas-info-extra-content');

        // 3. Preparar UI Móvil (Tab de Ver Detalle)
        const trans = window.RedaAlojamientoJson || {};
        
        // Limpiar estados previos en móvil
        $('.mobile-detail-toggle').addClass('d-none');
        $('.mobile-detail-content').addClass('d-none');
        
        // Mostrar el toggle de la activa
        const currentToggleWrapper = $(`#mobile-detail-${id}`);
        const currentToggle = currentToggleWrapper.find('.mobile-detail-toggle');
        currentToggle.removeClass('d-none');
        
        // Si estamos en móvil (ancho menor a 768px), expandir automáticamente
        if (window.innerWidth < 768) {
            const content = currentToggleWrapper.find('.mobile-detail-content');
            const icon = currentToggle.find('.toggle-icon');
            const text = currentToggle.find('.toggle-text');

            content.removeClass('d-none');
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            text.text(trans["Ocultar información adicional"] || "Ocultar información adicional");

            // Renderizar datos móviles inmediatamente en el nuevo orden
            renderizarCabeceraMediacion(item, `#mobile-detail-${id} .mobile-header-container`);
            renderizarTimeline(item.paso_actual, `#mobile-detail-${id} .mobile-timeline-container`);
            renderizarResumenMediacion(item, `#mobile-detail-${id} .mobile-resumen-container`);

            // Desplazamiento suave si es por interacción manual
            if (conScroll) {
                const element = document.querySelector(`.container-mediacion[data-id="${id}"]`);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }
    };

    /**
     * Renderiza el listado de mediaciones con el diseño de tres columnas optimizado.
     */
    const renderizarLista = (items) => {
        const container = $('#disputas-list-container');
        const trans = window.RedaAlojamientoJson || {};

        if (!items || !items.length) {
            container.html(`
                <div class="row justify-content-center w-100 p-4 mt-4">
                    <div class="text-center w-100">
                        <img src="${APP_URL}/public/img/unnamed.png" class="img-fluid reda-img-empty-state" alt="No encontrado">
                        <p class="text-center mt-3">${trans["No se encontraron mediaciones."] || "No se encontraron mediaciones."}</p>
                    </div>
                </div>
            `);
            return;
        }

        let html = '<div class="row mt-4">';
        items.forEach(item => {
            // Prioridad con color
            let prioridadHtml = '';
            if (item.prioridad) {
                let colorClass = 'text-info';
                if (item.prioridad === 'Alta') colorClass = 'text-danger';
                else if (item.prioridad === 'Media') colorClass = 'text-warning';
                prioridadHtml = `<span class="${colorClass}">${item.prioridad}</span>`;
            }

            const badgeClass = getStatusBadgeClass(item.estado);
            const statusText = formatStatusText(item.estado);

            html += `
                <div class="col-md-12 p-0 mb-4 container-mediacion" data-id="${item.id}">
                    <div class="card border rounded-3 card-mediacion pointer shadow-sm-hover" data-id="${item.id}">
                        <div class="card-body p-0">
                            <div class="row m-0">
                                <div class="col-md-4 p-0">
                                    <div class="img-container h-100 bg-light d-flex align-items-center justify-content-center border-right">
                                        <img src="${item.propiedad_foto}" 
                                             class="img-fluid w-100 h-100 object-fit-cover rounded-start img-min-150 reda-propiedad-foto-max" 
                                             alt="Propiedad">
                                    </div>
                                </div>

                                <div class="col-md-4 p-4 border-right">
                                    <div class="mb-2">
                                        <span class="badge ${badgeClass}">${statusText}</span>
                                        <span class="text-muted small ml-2 font-weight-700">ID: #${item.id}</span>
                                    </div>

                                    <h5 class="text-18 font-weight-700 text-color mb-1 reda-motivo-clamped" title="${item.motivo}">${item.motivo}</h5>
                                    
                                    ${item.prioridad ? `
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-exclamation-circle mr-1"></i> ${trans["Prioridad"] || "Prioridad"}: ${prioridadHtml}
                                        </div>
                                    ` : ''}

                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-bookmark mr-1"></i> ${trans["ID Reservación"] || "ID Reservación"}: <span class="text-dark">#${item.booking_id}</span>
                                    </div>

                                    <div class="d-flex flex-column mt-3">
                                        <div class="text-muted small">
                                            <i class="far fa-clock mr-1"></i> ${trans["Actualizado"] || "Actualizado"} <span class="text-dark">${item.actualizado_hace}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 p-4 d-flex flex-column justify-content-center bg-light-soft">
                                    ${generarBloquePersonasHtml(item)}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Detail Section -->
                    <div class="mobile-detail-wrapper d-md-none" id="mobile-detail-${item.id}">
                        <div class="mobile-detail-toggle py-2 px-3 border rounded-bottom bg-light d-none align-items-center justify-content-between pointer" data-id="${item.id}">
                            <span class="text-14 font-weight-600 toggle-text">${trans["Mostrar información adicional"] || "Mostrar información adicional"}</span>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="mobile-detail-content p-3 border rounded-bottom bg-white d-none shadow-sm">
                            <div class="mobile-header-wrapper mb-4">
                                <div class="mobile-header-container"></div>
                            </div>
                            <div class="mobile-timeline-wrapper mb-4">
                                <div class="reda-timeline-carousel mobile-timeline-container"></div>
                            </div>
                            <div class="mobile-resumen-wrapper mb-3">
                                <h6 class="font-weight-700 mb-3 text-14 border-bottom pb-2">${trans["Detalle"] || "Detalle"}</h6>
                                <div class="mobile-resumen-container"></div>
                            </div>
                            <div class="mobile-acciones-wrapper" id="mobile-acciones-${item.id}">
                                {{-- Se inyecta vía JS --}}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);

        // Inicializar el observador de enfoque en móvil
        setTimeout(inicializarObservadorEnfoque, 300);
    };

    /**
     * Carga las mediaciones vía Ajax según el estatus y página seleccionada.
     */
    const cargarMediaciones = async (status, page = 1) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }

        const data = await getMediacionesPaginadas(status, page);

        if (data.success) {
            mediacionesCargadas = data.respuesta.data;
            renderizarLista(mediacionesCargadas);
            $('#disputas-pagination-container').html(data.respuesta.pagination);

            // Seleccionar la primera mediación por defecto
            if (mediacionesCargadas.length > 0) {
                seleccionarMediacion(mediacionesCargadas[0].id, false);
            } else {
                const trans = window.RedaAlojamientoJson || {};
                $('#disputas-cabecera-lateral').addClass('d-none');
                $('#reda-timeline-container').html(`<p class="text-center text-muted small w-100">${trans["Selecciona una mediación para ver su progreso."] || "Selecciona una mediación para ver su progreso."}</p>`);
                $('#disputas-info-extra-content').html(`<p class="text-14 text-muted">${trans["Aquí aparecerá información relevante sobre el estado general de tus mediaciones."] || "Aquí aparecerá información relevante sobre el estado general de tus mediaciones."}</p>`);
                $('#disputas-acciones-sidebar').addClass('d-none');
            }
        } else {
            const trans = window.RedaAlojamientoJson || {};
            $('#disputas-list-container').html(`
                <div class="alert alert-danger mt-4">
                    ${data.mensaje_usuario}
                </div>
            `);
        }

        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
            window.RedaNotificaciones.ocultar();
        }
    };

    /**
     * Abre el modal de detalle de la mediación.
     */
    const abrirDetalleMediacion = async (id) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }

        const data = await getHtmlDetalleMediacion(id);

        // Primero ocultamos el de espera para limpiar backdrops
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
            window.RedaNotificaciones.ocultar();
        }

        if (data.success) {
            // Pequeño delay para que Bootstrap limpie el body antes de abrir el nuevo modal
            setTimeout(() => {
                // Remover modal previo si existe
                $('#modal-detalle-mediacion-reda').remove();
                $('body').append(data.respuesta);
                $('#modal-detalle-mediacion-reda').modal('show');
            }, 150);
        } else {
            alert(data.mensaje_usuario);
        }
    };

    $(function() {
        if ($(containerId).length) {
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

            // Manejo de clics en los items de la lista para seleccionar
            $(document).on('click', '.card-mediacion', function(e) {
                const id = $(this).attr('data-id');
                seleccionarMediacion(id, true); // Scroll activado al tocar manualmente
            });

            // Manejo de clics en el toggle de detalle móvil
            $(document).on('click', '.mobile-detail-toggle', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                const content = $(`#mobile-detail-${id} .mobile-detail-content`);
                const icon = $(this).find('.toggle-icon');
                const text = $(this).find('.toggle-text');
                const trans = window.RedaAlojamientoJson || {};

                if (content.hasClass('d-none')) {
                    // Expandir
                    content.removeClass('d-none');
                    icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    text.text(trans["Ocultar información adicional"] || "Ocultar información adicional");

                    // Cargar contenido en los contenedores móviles
                    const item = mediacionesCargadas.find(m => m.id == id);
                    if (item) {
                        renderizarCabeceraMediacion(item, `#mobile-detail-${id} .mobile-header-container`);
                        renderizarTimeline(item.paso_actual, `#mobile-detail-${id} .mobile-timeline-container`);
                        renderizarResumenMediacion(item, `#mobile-detail-${id} .mobile-resumen-container`);
                    }
                } else {
                    // Contraer
                    content.addClass('d-none');
                    icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    text.text(trans["Mostrar información adicional"] || "Mostrar información adicional");
                }
            });

            // Manejo de toggle para el motivo
            $(document).on('click', '.btn-toggle-motivo', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                const state = $(this).attr('data-state');
                const target = $(`#motivo-container-${id}`);
                const trans = window.RedaAlojamientoJson || {};

                if (state === 'less') {
                    target.addClass('expanded');
                    $(this).text(trans["...Menos"] || "...Menos").attr('data-state', 'more');
                } else {
                    target.removeClass('expanded');
                    $(this).text(trans["Más..."] || "Más...").attr('data-state', 'less');
                }
            });

            // Manejo de toggle para descripción larga
            $(document).on('click', '.btn-toggle-desc-larga', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                const state = $(this).attr('data-state');
                const target = $(`#desc-container-${id}`);
                const trans = window.RedaAlojamientoJson || {};

                if (state === 'less') {
                    target.addClass('expanded');
                    $(this).text(trans["...Menos"] || "...Menos").attr('data-state', 'more');
                } else {
                    target.removeClass('expanded');
                    $(this).text(trans["Más..."] || "Más...").attr('data-state', 'less');
                }
            });

            // Click en botón ver detalle dentro del resumen (sidebar o móvil)
            $(document).on('click', '.btn-ver-detalle-sidebar', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                abrirDetalleMediacion(id);
            });

            // Navegación del timeline (Escritorio)
            $(document).on('click', '#timeline-prev', function() {
                const container = $('#reda-timeline-container');
                container.animate({ scrollLeft: container.scrollLeft() - 150 }, 300);
            });

            $(document).on('click', '#timeline-next', function() {
                const container = $('#reda-timeline-container');
                container.animate({ scrollLeft: container.scrollLeft() + 150 }, 300);
            });

            // Manejo de paginación
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
