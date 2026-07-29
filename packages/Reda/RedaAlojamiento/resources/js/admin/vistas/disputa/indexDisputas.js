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

    const containerId = '#index_disputas_admin';
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
     * Peticion AJAX para obtener mediaciones paginadas (Admin).
     */
    const obtenerMediacionesPaginadas = (estatus, pagina) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/admin/reda/disputas/get-listado',
                type: 'GET',
                data: { status: estatus, page: pagina },
                success: (data) => resolve(data),
                error: (x) => {
                    let respuestaServidor = {};
                    try {
                        respuestaServidor = JSON.parse(x.responseText);
                    } catch (e) {
                        respuestaServidor = {};
                    }

                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    let respuesta = {
                        'success': false,
                        'message' : window.RedaAlojamientoJson["Error al cargar las mediaciones. Intente de nuevo."] || 'Error al cargar las mediaciones. Intente de nuevo.',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                        'respuesta': respuestaServidor.respuesta || '',
                        'code': x.status !== 0 ? x.status : 504,
                    };
                    resolve(respuesta);
                }
            });
        });
    };

    /**
     * Peticion AJAX para obtener el HTML del modal de detalle (Admin).
     */
    const obtenerHtmlDetalleMediacion = (id) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/admin/reda/disputas/get-detail-modal/' + id,
                type: 'GET',
                success: (data) => resolve(data),
                error: (x) => {
                    let respuestaServidor = {};
                    try {
                        respuestaServidor = JSON.parse(x.responseText);
                    } catch (e) {
                        respuestaServidor = {};
                    }

                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    let respuesta = {
                        'success': false,
                        'message' : window.RedaAlojamientoJson["Error al cargar el detalle."] || 'Error al cargar el detalle.',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                        'respuesta': respuestaServidor.respuesta || '',
                        'code': x.status !== 0 ? x.status : 504,
                    };
                    resolve(respuesta);
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

        let html = `<div class="d-flex flex-nowrap border-bottom pb-2 reda-tabs-nav overflow-x-auto">`;

        estatus.forEach((e, index) => {
            const isActive = index === 0 ? 'active' : '';
            html += `
                <div class="disputa-tab-item px-3 py-2 me-2 pointer text-center ${isActive}" data-status="${e.id}">
                    <div class="d-flex align-items-center justify-content-center mb-1 status-icon">
                        ${e.icono}
                    </div>
                    <div class="d-flex align-items-center justify-content-center">
                        <span class="text-14 fw-600">${e.nombre}</span>
                        <span class="badge rounded-pill bg-success ms-2 text-10 d-none status-counter" id="counter-${e.id}">${e.contador}</span>
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
     * Renderiza el bloque unificado de personas relacionadas (Admin).
     * Se identifica al demandante (quien inició el proceso) agregando "(demandante)".
     */
    const generarBloquePersonasHtml = (item) => {
        const trans = window.RedaAlojamientoJson || {};
        const agenteFoto = item.agente ? item.agente.foto : `${APP_URL}/public/img/unnamed.png`;
        const agenteNombre = item.agente ? item.agente.nombre : trans["Pendiente de asignación"] || "Pendiente de asignación";
        const agenteIcono = item.agente ? 'fas fa-user-tie' : 'fas fa-user-clock';
        const agenteClaseNombre = item.agente ? 'text-dark' : 'text-muted italic small leading-tight';

        // Identificación del demandante basada en ID y Rol inicial
        const demandanteLabel = ` (${trans["demandante"] || "demandante"})`;
        
        const esDemandanteAnfitrion = item.id_usuario_anfitrion == item.id_usuario_inicial && 
                                     item.rol_usuario_inicial && 
                                     item.rol_usuario_inicial.toLowerCase().includes('anfitr');
                                     
        const esDemandanteTurista = item.id_usuario_turista == item.id_usuario_inicial && 
                                   item.rol_usuario_inicial && 
                                   item.rol_usuario_inicial.toLowerCase().includes('turist');

        const labelAnfitrion = (trans["Anfitrión"] || "Anfitrión") + (esDemandanteAnfitrion ? demandanteLabel : '') + ':';
        const labelTurista = (trans["Turista"] || "Turista") + (esDemandanteTurista ? demandanteLabel : '') + ':';

        return `
            <div class="personas-relacionadas-block">
                <div class="d-flex align-items-center mb-2">
                    <div class="symbol symbol-30px symbol-circle me-2">
                        <img src="${item.anfitrion_foto}" class="rounded-circle">
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-muted text-10 leading-tight">${labelAnfitrion}</span>
                        <span class="text-dark text-12 text-truncate" title="${item.anfitrion_nombre}">${item.anfitrion_nombre}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="symbol symbol-30px symbol-circle me-2">
                        <img src="${item.turista_foto}" class="rounded-circle">
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-muted text-10 leading-tight">${labelTurista}</span>
                        <span class="text-dark text-12 text-truncate" title="${item.turista_nombre}">${item.turista_nombre}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="symbol symbol-30px symbol-circle me-2 position-relative">
                        <img src="${agenteFoto}" alt="Agente" class="rounded-circle">
                        <div class="position-absolute reda-status-badge-pos">
                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm reda-status-badge-icon">
                                <i class="${agenteIcono} f-10 text-success"></i>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-muted text-10 leading-tight">${trans["Agente:"] || "Agente:"}</span>
                        <span class="${agenteClaseNombre} text-12 text-truncate" title="${agenteNombre}">${agenteNombre}</span>
                    </div>
                </div>
                <div class="mt-2 border-top pt-2">
                    <button class="btn btn-outline-success btn-sm w-100 border-0 text-12 btn-ver-mensajes-mediacion" 
                            data-booking-id="${item.booking_id}" 
                            data-id="${item.id}">
                        <i class="far fa-comments me-1"></i> ${trans["Ver mensajes"] || "Ver mensajes"}
                    </button>
                </div>
            </div>
        `;
    };

    /**
     * Obtiene los mensajes de la mediación vía Ajax.
     */
    const obtenerMensajesMediacion = (bookingId) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/admin/reda/disputas/mensajes/' + bookingId,
                type: 'GET',
                success: (data) => resolve(data),
                error: (x) => resolve({ success: false, mensaje_usuario: 'Error al cargar mensajes' })
            });
        });
    };

    /**
     * Envía un mensaje como admin.
     */
    const enviarMensajeAdmin = (bookingId, message, receiverId) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/admin/reda/disputas/mensajes/store',
                type: 'POST',
                data: {
                    booking_id: bookingId,
                    message: message,
                    receiver_id: receiverId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (data) => resolve(data),
                error: (x) => resolve({ success: false, mensaje_usuario: 'Error al enviar mensaje' })
            });
        });
    };

    /**
     * Renderiza la lista de mensajes en el modal.
     */
    const renderizarMensajes = (mensajes, booking) => {
        const container = $('#reda-mensajes-container');
        const trans = window.RedaAlojamientoJson || {};
        
        if (!mensajes || !mensajes.length) {
            container.html(`<div class="text-center py-5 text-muted italic">${trans["No hay mensajes en esta conversación."] || "No hay mensajes en esta conversación."}</div>`);
            return;
        }

        let html = '';
        mensajes.forEach(m => {
            // Previsión: Determinar si es "mio" (del admin) basándonos en el sender_type.
            // Si no hay metadata, m.sender_type será undefined, lo cual es correcto tratar como 'user'.
            const esMio = (m.sender_type === 'admin');
            const claseMe = esMio ? 'me' : '';
            const nombreSender = m.sender_name || (trans["Administrador"] || "Administrador");

            html += `
                <div class="message-list-admin ${claseMe}">
                    <div class="msg-admin shadow-sm p-3">
                        ${!esMio ? `<span class="d-block text-10 fw-700 text-uppercase mb-1 opacity-75">${nombreSender}</span>` : ''}
                        <p class="m-0 text-13">${m.message}</p>
                    </div>
                    <div class="time-admin text-10 mt-1 opacity-50 text-end">${m.created_time}</div>
                </div>
            `;
        });
        container.html(html);
        
        // Scroll al final
        container.scrollTop(container[0].scrollHeight);
    };

    /**
     * Abre el modal de mensajes.
     */
    const abrirMensajesMediacion = async (bookingId, disputaId) => {
        const modalElement = document.getElementById('modal-mensajes-mediacion-reda');
        const container = $('#reda-mensajes-container');
        const trans = window.RedaAlojamientoJson || {};

        // Reset UI
        container.html('<div class="text-center py-5"><div class="spinner-border text-success" role="status"></div></div>');
        $('#btn-enviar-mensaje-admin').attr('data-booking-id', bookingId);
        $('#input-mensaje-admin').val('');

        const bsModal = new bootstrap.Modal(modalElement);
        bsModal.show();

        const data = await obtenerMensajesMediacion(bookingId);
        if (data.success) {
            const b = data.respuesta.booking;
            
            // Configurar receptores en el dropdown
            $('#send-to-turista').attr('data-id', b.user_id);
            $('#label-send-turista').text(`${trans["Turista"] || "Turista"}: ${b.users.first_name}`);
            
            $('#send-to-anfitrion').attr('data-id', b.host_id);
            $('#label-send-anfitrion').text(`${trans["Anfitrión"] || "Anfitrión"}: ${b.host.first_name}`);

            renderizarMensajes(data.respuesta.messages, b);
        } else {
            container.html(`<div class="alert alert-danger">${data.mensaje_usuario}</div>`);
        }
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
                    <div class="me-2 bg-light-soft rounded d-flex align-items-center justify-content-center reda-adjunto-icon-box">
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
                    <span class="badge ${badgeClass} fw-600 py-2 px-3 text-12 shadow-sm">${statusText}</span>
                    <span class="text-muted fw-600 text-14">ID: #${item.id}</span>
                </div>
                <h5 class="fw-600 text-dark text-20 mb-0 reda-motivo-clamped motivo-lista-expandible cursor-pointer" id="motivo-container-${item.id}" title="${item.motivo}">${item.motivo}</h5>
            </div>
        `;
        container.html(html);

        // Mostrar el card contenedor si estaba oculto
        if (containerSelector === '#disputas-header-content') {
            $('#disputas-cabecera-lateral').removeClass('d-none');
        }
    };

    /**
     * Renderiza la información de la reservación asociada.
     */
    const renderizarReservacionMediacion = (item, containerSelector) => {
        const container = $(containerSelector);
        if (!container.length) return;
        const trans = window.RedaAlojamientoJson || {};

        let html = `
            <div class="reservacion-detalle-sidebar">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <img src="${item.propiedad_foto}" class="rounded border object-fit-cover reda-reservacion-thumb reda-reservacion-thumb-size">
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-muted text-10 d-block mb-1">${trans["ID Reservación"] || "ID Reservación"}: #${item.booking_id}</span>
                        <h6 class="text-13 fw-700 text-dark mb-0 text-truncate" title="${item.propiedad_nombre}">${item.propiedad_nombre}</h6>
                        <p class="text-11 text-muted mb-0 text-truncate"><i class="fas fa-map-marker-alt me-1"></i>${item.propiedad_ubicacion}</p>
                    </div>
                </div>

                <div class="row g-0 border-top pt-3">
                    <div class="col-6 border-end pe-2">
                        <span class="text-muted text-10 d-block text-uppercase letter-spacing-1">${trans["Llegada"] || "Llegada"}</span>
                        <span class="text-12 fw-600 text-dark">${item.booking_start_date}</span>
                    </div>
                    <div class="col-6 ps-2">
                        <span class="text-muted text-10 d-block text-uppercase letter-spacing-1">${trans["Salida"] || "Salida"}</span>
                        <span class="text-12 fw-600 text-dark">${item.booking_end_date}</span>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted text-11">${trans["Huéspedes"] || "Huéspedes"}</span>
                        <span class="text-11 fw-600 text-dark">${item.booking_guest}</span>
                    </div>
                </div>
            </div>
        `;
        container.html(html);

        // Mostrar el card si estaba oculto
        if (containerSelector === '#disputas-reservacion-content') {
            $('#disputas-reservacion-sidebar').removeClass('d-none');
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
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <span class="text-muted small">${trans["Actualizado"] || "Actualizado"}</span>
                            <span class="text-muted small">${item.actualizado_hace}</span>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <span class="text-muted small d-block mb-1">${trans["Descripción"] || "Descripción"}</span>
                        <div class="bg-light rounded motivo-lista-expandible cursor-pointer p-3" id="desc-container-${item.id}">
                            <div class="text-13 text-muted reda-mediation-desc-clamped">
                                ${item.descripcion || "<i>" + (trans["Sin descripción"] || "Sin descripción") + "</i>"}
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-12 fw-700 mb-2 text-muted text-uppercase letter-spacing-1">${trans["Archivos adjuntos"] || "Archivos adjuntos"}</h6>
                        <div class="adjuntos-container">
                            ${generarListaAdjuntosHtml(item.adjuntos)}
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top bg-light-soft p-3 rounded border">
                        <h6 class="text-12 fw-700 mb-3 text-muted text-uppercase letter-spacing-1">${trans["Personas relacionadas"] || "Personas relacionadas"}</h6>
                        ${generarBloquePersonasHtml(item)}
                    </div>
                </div>
            </div>
        `;
        container.html(html);
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

        // 2. Actualizar Secciones Laterales (Solo en Escritorio >= 768px)
        if (window.innerWidth >= 768) {
            renderizarCabeceraMediacion(item, '#disputas-header-content');
            renderizarTimeline(item.paso_actual, '#reda-timeline-container');
            renderizarReservacionMediacion(item, '#disputas-reservacion-content');
            renderizarResumenMediacion(item, '#disputas-info-extra-content');
        }

        // 3. Preparar UI Móvil (Tab de Ver Detalle)
        const trans = window.RedaAlojamientoJson || {};

        // Limpiar estados previos en móvil: ocultamos todos los toggles y contenidos
        $('.mobile-detail-toggle').addClass('d-none');
        $('.mobile-detail-content').addClass('d-none');

        // Mostrar solo el toggle de la mediación activa
        const currentToggleWrapper = $(`#mobile-detail-${id}`);
        const currentToggle = currentToggleWrapper.find('.mobile-detail-toggle');
        currentToggle.removeClass('d-none');

        // Resetear el estado del icono y texto a "Mostrar" (cerrado)
        const icon = currentToggle.find('.toggle-icon');
        const text = currentToggle.find('.toggle-text');
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        text.text(trans["Mostrar información adicional"] || "Mostrar información adicional");

        // NO EXPANDIR AUTOMÁTICAMENTE (Requerimiento del usuario)

        // Desplazamiento suave si es por interacción manual
        if (conScroll && window.innerWidth < 768) {
            const element = document.querySelector(`.container-mediacion[data-id="${id}"]`);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    };

    /**
     * Trunca un texto al límite de caracteres indicado sin cortar palabras.
     */
    const truncarMotivo = (texto, limite = 65) => {
        if (!texto || texto.length <= limite) return texto;
        let truncado = texto.substring(0, limite);
        const ultimoEspacio = truncado.lastIndexOf(' ');
        if (ultimoEspacio > 0) {
            truncado = truncado.substring(0, ultimoEspacio);
        }
        return truncado + '...';
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
                <div class="col-md-12 px-3 mb-4 container-mediacion" data-id="${item.id}">
                    <div class="card border rounded-3 card-mediacion pointer shadow-sm-hover" data-id="${item.id}">
                        <div class="card-body p-0">
                            <div class="row m-0 g-0">
                                <div class="col-md-4 p-0">
                                    <div class="img-container h-100 bg-light d-flex align-items-center justify-content-center border-end">
                                        <img src="${item.propiedad_foto}"
                                             class="img-fluid w-100 h-100 object-fit-cover rounded-start img-min-150 reda-propiedad-foto-max"
                                             alt="Propiedad">
                                    </div>
                                </div>

                                <div class="col-md-4 p-4 border-end">
                                    <div class="mb-2">
                                        <span class="badge ${badgeClass}">${statusText}</span>
                                        <span class="text-muted small ms-2 fw-600">ID: #${item.id}</span>
                                    </div>

                                    <h5 class="text-18 fw-600 text-color mb-1 reda-motivo-clamped motivo-lista-expandible" 
                                        title="${item.motivo}">
                                        ${item.motivo}
                                    </h5>

                                    ${item.prioridad ? `
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-exclamation-circle me-1"></i> ${trans["Prioridad"] || "Prioridad"}: ${prioridadHtml}
                                        </div>
                                    ` : ''}

                                    <div class="text-muted small mb-2">
                                        <i class="fas fa-bookmark me-1"></i> ${trans["ID Reservación"] || "ID Reservación"}: <span class="text-dark">#${item.booking_id}</span>
                                    </div>

                                    <div class="d-flex flex-column mt-3">
                                        <div class="text-muted small">
                                            <i class="far fa-clock me-1"></i> ${trans["Actualizado"] || "Actualizado"} <span class="text-dark">${item.actualizado_hace}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 p-4 d-flex flex-column justify-content-center bg-light-soft">
                                    ${generarBloquePersonasHtml(item)}
                                </div>
                            </div>

                            <!-- Mobile Detail Section (Inside Card Body) -->
                            <div class="mobile-detail-wrapper d-md-none" id="mobile-detail-${item.id}">
                                <div class="mobile-detail-toggle py-3 px-4 border-top bg-light d-none align-items-center justify-content-between pointer" data-id="${item.id}">
                                    <span class="text-14 fw-600 toggle-text">${trans["Mostrar información adicional"] || "Mostrar información adicional"}</span>
                                    <i class="fas fa-chevron-down toggle-icon"></i>
                                </div>
                                <div class="mobile-detail-content p-4 border-top bg-white d-none">
                                    <div class="mobile-header-wrapper mb-4">
                                        <div class="mobile-header-container"></div>
                                    </div>
                                    <div class="mobile-timeline-wrapper mb-4">
                                        <div class="reda-timeline-carousel mobile-timeline-container"></div>
                                    </div>
                                    <div class="mobile-reservacion-wrapper mb-4">
                                        <h6 class="fw-600 mb-3 text-14 border-bottom pb-2">${trans["Reservación"] || "Reservación"}</h6>
                                        <div class="mobile-reservacion-container"></div>
                                    </div>
                                    <div class="mobile-resumen-wrapper mb-3">
                                        <h6 class="fw-600 mb-3 text-14 border-bottom pb-2">${trans["Detalle"] || "Detalle"}</h6>
                                        <div class="mobile-resumen-container"></div>
                                    </div>
                                </div>
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
     * Carga las mediaciones vía Ajax según el estatus y página seleccionada (Admin).
     */
    const cargarMediaciones = async (estatus, pagina = 1) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }

        const data = await obtenerMediacionesPaginadas(estatus, pagina);

        if (data.success) {
            mediacionSeleccionadaId = null; // Reset selection state for new results
            mediacionesCargadas = data.respuesta.data;
            renderizarLista(mediacionesCargadas);
            if ($('#disputas-pagination-container').length) {
                $('#disputas-pagination-container').html(data.respuesta.pagination);
            }

            // Seleccionar la primera mediación por defecto
            if (mediacionesCargadas.length > 0) {
                seleccionarMediacion(mediacionesCargadas[0].id, false);
            } else {
                const trans = window.RedaAlojamientoJson || {};
                $('#disputas-cabecera-lateral').addClass('d-none');
                $('#reda-timeline-container').html(`<p class="text-center text-muted small w-100">${trans["Selecciona una mediación para ver su progreso."] || "Selecciona una mediación para ver su progreso."}</p>`);
                $('#disputas-reservacion-sidebar').addClass('d-none');
                $('#disputas-info-extra-content').html(`<p class="text-14 text-muted">${trans["Aquí aparecerá información relevante sobre el estado general de tus mediaciones."] || "Aquí aparecerá información relevante sobre el estado general de tus mediaciones."}</p>`);
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
     * Abre el modal de detalle de la mediación (Admin).
     */
    const abrirDetalleMediacion = async (id) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }

        const data = await obtenerHtmlDetalleMediacion(id);

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

                // Inicializar modal con la API de Bootstrap 5
                const modalElement = document.getElementById('modal-detalle-mediacion-reda');
                if (modalElement && window.bootstrap) {
                    const bsModal = new bootstrap.Modal(modalElement);
                    bsModal.show();
                }
            }, 150);
        } else {
            alert(data.mensaje_usuario);
        }
    };

    $(function() {
        if ($(containerId).length) {
            inyectarPestanasEstatus();

            // Ajustar título si no es super admin
            if (window.RedaAdminAccess && !window.RedaAdminAccess.isFullAdmin) {
                const trans = window.RedaAlojamientoJson || {};
                $('.content-header h1').text(trans["Mis Mediaciones"] || "Mis Mediaciones");
            }

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
                // Si el clic fue en el motivo expandible, no procesamos la selección de la tarjeta
                if ($(e.target).closest('.motivo-lista-expandible').length || $(e.target).closest('.btn-ver-mensajes-mediacion').length) {
                    return;
                }
                const id = $(this).attr('data-id');
                seleccionarMediacion(id, true); // Scroll activado al tocar manualmente
            });

            // Manejo de toggle para el motivo en el listado (especialmente para móvil)
            $(document).on('click', '.motivo-lista-expandible', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $el = $(this);
                $el.toggleClass('expanded');
                
                // Si el clic fue en el contenedor gris de la descripción, expandimos también el hijo
                $el.find('.reda-mediation-desc-clamped, .reda-motivo-clamped').toggleClass('expanded');

                // Si el elemento está dentro de una tarjeta del listado, actualizamos la selección
                const card = $el.closest('.card-mediacion');
                if (card.length) {
                    const id = card.attr('data-id');
                    if (id) {
                        seleccionarMediacion(id, false); // false para no forzar scroll y permitir lectura fluida
                    }
                }
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
                        renderizarReservacionMediacion(item, `#mobile-detail-${id} .mobile-reservacion-container`);
                        renderizarResumenMediacion(item, `#mobile-detail-${id} .mobile-resumen-container`);
                    }
                } else {
                    // Contraer
                    content.addClass('d-none');
                    icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    text.text(trans["Mostrar información adicional"] || "Mostrar información adicional");
                }
            });

            // Click en botón ver detalle dentro del resumen (sidebar o móvil)
            $(document).on('click', '.btn-ver-detalle-sidebar', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                abrirDetalleMediacion(id);
            });

            // Click en "Ver mensajes"
            $(document).on('click', '.btn-ver-mensajes-mediacion', function(e) {
                e.stopPropagation();
                const bookingId = $(this).attr('data-booking-id');
                const disputaId = $(this).attr('data-id');
                abrirMensajesMediacion(bookingId, disputaId);
            });

            // Enviar mensaje al presionar Enter
            $(document).on('keypress', '#input-mensaje-admin', function(e) {
                if (e.which === 13) {
                    const dropdown = new bootstrap.Dropdown($('#btn-enviar-mensaje-admin')[0]);
                    dropdown.show();
                }
            });

            // Seleccionar receptor y enviar
            $(document).on('click', '.btn-send-to-user', async function(e) {
                e.preventDefault();
                const receiverId = $(this).attr('data-id');
                const bookingId = $('#btn-enviar-mensaje-admin').attr('data-booking-id');
                const message = $('#input-mensaje-admin').val().trim();
                const trans = window.RedaAlojamientoJson || {};

                if (!message) return;

                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }

                const response = await enviarMensajeAdmin(bookingId, message, receiverId);

                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                    window.RedaNotificaciones.ocultar();
                }

                if (response.success) {
                    $('#input-mensaje-admin').val('');
                    // Recargar mensajes
                    const data = await obtenerMensajesMediacion(bookingId);
                    if (data.success) {
                        renderizarMensajes(data.respuesta.messages, data.respuesta.booking);
                    }
                } else {
                    alert(response.mensaje_usuario || 'Error al enviar mensaje');
                }
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
