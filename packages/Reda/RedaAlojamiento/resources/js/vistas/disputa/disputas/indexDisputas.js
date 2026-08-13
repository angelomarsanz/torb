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

    // Estado para el visor de medios
    let adjuntosVisualizables = [];
    let indiceAdjuntoActual = 0;
    let currentZoom = 1;
    const zoomStep = 0.5; // Zoom más agresivo para el toggle
    const maxZoom = 3;

    // Para gestos táctiles
    let touchStartX = 0;
    let touchEndX = 0;
    let touchStartY = 0;
    let touchEndY = 0;

    /**
     * Obtiene la URL completa para una imagen.
     */
    const getFullUrl = (path) => {
        if (!path) return `${APP_URL}/public/img/unnamed.png`;
        if (path.startsWith('http')) return path;
        // Eliminar slash inicial si existe para evitar doble slash al unir con APP_URL
        const cleanPath = path.startsWith('/') ? path.substring(1) : path;
        return `${APP_URL}/${cleanPath}`;
    };

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

        let html = `<div class="d-flex flex-nowrap border-bottom pb-2 reda-tabs-nav overflow-x-auto">`;

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

        const agenteFoto = item.agente ? getFullUrl(item.agente.foto) : `${APP_URL}/public/img/unnamed.png`;
        const anfitrionFoto = getFullUrl(item.anfitrion_foto);
        const turistaFoto = getFullUrl(item.turista_foto);

        const agenteNombre = item.agente ? item.agente.nombre : trans["Pendiente de asignación"] || "Pendiente de asignación";
        const agenteIcono = item.agente ? 'fas fa-user-tie' : 'fas fa-user-clock';
        const agenteClaseNombre = item.agente ? 'text-dark' : 'text-muted italic small leading-tight';

        // Identificación del demandante basada en ID y Rol inicial
        const demandanteLabel = ` - ${trans["demandante"] || "demandante"}`;

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
                    <div class="avatar-mini mr-2">
                        <img src="${anfitrionFoto}" class="rounded-circle border reda-avatar-30">
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-muted text-10 leading-tight">${labelAnfitrion}</span>
                        <span class="text-dark text-12 text-truncate" title="${item.anfitrion_nombre}">${item.anfitrion_nombre}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar-mini mr-2">
                        <img src="${turistaFoto}" class="rounded-circle border reda-avatar-30">
                    </div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="text-muted text-10 leading-tight">${labelTurista}</span>
                        <span class="text-dark text-12 text-truncate" title="${item.turista_nombre}">${item.turista_nombre}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
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
                        <span class="${agenteClaseNombre} text-12 text-truncate" title="${agenteNombre}">${agenteNombre}</span>
                    </div>
                </div>

                <div class="mt-2 d-flex justify-content-center">
                    <button class="btn btn-sm btn-outline-success font-weight-600 text-11 px-3 py-1 reda-btn-pill-small btn-ver-mensajes-mediacion" 
                            data-booking-id="${item.booking_id}" 
                            data-id="${item.id}">
                        <i class="far fa-comments mr-1"></i>
                        ${trans["Ver conversación"] || "Ver conversación"}(${item.conteo_mensajes_nuevos ?? 0})
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
                url: APP_URL + '/reda/disputas/mensajes/' + bookingId,
                type: 'GET',
                success: (data) => resolve(data),
                error: (x) => resolve({ success: false, mensaje_usuario: 'Error al cargar mensajes' })
            });
        });
    };

    /**
     * Envía un mensaje como usuario.
     */
    const enviarMensajeHuesped = (bookingId, message, receiverId) => {
        return new Promise((resolve) => {
            $.ajax({
                url: APP_URL + '/reda/disputas/mensajes/store',
                type: 'POST',
                data: {
                    booking_id: bookingId,
                    message: message,
                    receiver_id: receiverId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: (data) => resolve(data),
                error: (x) => {
                    let respuestaServidor = {};
                    try { respuestaServidor = JSON.parse(x.responseText); } catch (e) { respuestaServidor = {}; }
                    resolve({ 
                        success: false, 
                        mensaje_usuario: respuestaServidor.mensaje_usuario || (window.RedaAlojamientoJson["Error al enviar mensaje"] || "Error al enviar mensaje") 
                    });
                }
            });
        });
    };

    /**
     * Renderiza la lista de mensajes en el modal.
     */
    const renderizarMensajes = (mensajes, booking, currentUserId) => {
        const container = $('#reda-mensajes-container');
        const trans = window.RedaAlojamientoJson || {};
        
        if (!mensajes || !mensajes.length) {
            container.html(`<div class="text-center py-5 text-muted italic">${trans["No hay mensajes en esta conversación."] || "No hay mensajes en esta conversación."}</div>`);
            return;
        }

        let html = '';
        mensajes.forEach(m => {
            // El usuario actual es el que envió el mensaje si m.sender_id coincide con el ID de la sesión
            // Y m.sender_type no es admin
            const esMio = (m.sender_id == currentUserId && (m.sender_type === 'user' || !m.sender_type)); 
            const claseMe = esMio ? 'me' : '';
            const nombreSender = m.sender_name || (trans["Sistema"] || "Sistema");
            const fotoSender = getFullUrl(m.sender_foto);
            const roleSender = m.sender_role ? m.sender_role : '';

            html += `
                <div class="message-list-reda ${claseMe} d-flex align-items-start mb-3">
                    <div class="reda-avatar-container flex-shrink-0">
                        <img src="${fotoSender}" class="rounded-circle reda-avatar-30 shadow-sm border" title="${nombreSender} (${roleSender})">
                    </div>

                    <div class="d-flex flex-column msg-bubble-container">
                        <div class="msg-reda shadow-sm p-2 px-3">
                            <span class="d-block text-10 font-weight-700 text-uppercase mb-1 opacity-75">${nombreSender} (${roleSender})</span>
                            <p class="m-0 text-13">${m.message}</p>
                        </div>
                        <div class="time-reda text-10 mt-1 opacity-50">
                            ${m.created_at_humans || m.created_time || ''}
                            ${esMio ? `<i class="fas ${m.read == 1 ? 'fa-check-double text-primary' : 'fa-check'} ml-1" title="${m.read == 1 ? (window.RedaAlojamientoJson['Leído'] || 'Leído') : (window.RedaAlojamientoJson['Enviado'] || 'Enviado')}"></i>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        container.html(html);
        
        const scrollToBottom = () => {
            if (container.length) {
                container.scrollTop(container[0].scrollHeight);
            }
        };

        scrollToBottom();
        setTimeout(scrollToBottom, 100);
    };

    /**
     * Abre el modal de mensajes.
     */
    const abrirMensajesMediacion = (bookingId, disputaId) => {
        const modalElement = $('#modal-mensajes-mediacion-reda');
        const container = $('#reda-mensajes-container');

        // Reset UI
        container.html('<div class="text-center py-5"><div class="spinner-border text-success" role="status"></div></div>');
        
        // Asignar ID al botón de envío para recuperarlo después
        $('#btn-enviar-mensaje-reda').attr('data-booking-id', bookingId);
        $('#input-mensaje-reda').val('');

        modalElement.modal('show');

        obtenerMensajesMediacion(bookingId).then((data) => {
            if (data.success) {
                renderizarMensajes(data.respuesta.messages, data.respuesta.booking, data.respuesta.current_user_id);
            } else {
                container.html(`<div class="alert alert-danger">${data.mensaje_usuario}</div>`);
            }
        });
    };

    /**
     * Aplica el zoom actual a la imagen del visor.
     */
    const applyZoom = () => {
        const img = $('#media-viewer-img');
        if (img.length) {
            img.css('transform', `scale(${currentZoom})`);
            if (currentZoom > 1) {
                img.addClass('zoomed');
            } else {
                img.removeClass('zoomed');
            }
        }
    };

    /**
     * Actualiza el contenido del visor de medios.
     */
    const actualizarMediaViewer = () => {
        const file = adjuntosVisualizables[indiceAdjuntoActual];
        if (!file) return;

        const container = $('#media-content-container');
        const title = $('#media-viewer-title');
        const counter = $('#media-viewer-counter');
        const trans = window.RedaAlojamientoJson || {};

        // Reset zoom y contenido
        currentZoom = 1;
        container.html('<div class="spinner-border text-light" role="status"></div>');
        title.text(file.nombre);
        counter.text(`${indiceAdjuntoActual + 1} / ${adjuntosVisualizables.length}`);

        const esPDF = file.url.toLowerCase().endsWith('.pdf');
        
        if (file.es_imagen) {
            const img = new Image();
            img.onload = () => {
                container.html(`<img src="${file.url}" id="media-viewer-img" class="img-fluid" style="max-height: 80vh; pointer-events: auto;">`);
                $('.zoom-controls').removeClass('d-none');
                applyZoom();

                // Toggle Zoom al hacer clic
                $('#media-viewer-img').on('click', function(e) {
                    e.stopPropagation();
                    if (currentZoom === 1) {
                        currentZoom = 2; // Zoom predefinido al tocar
                    } else {
                        currentZoom = 1; // Regresar a original
                    }
                    applyZoom();
                });
            };
            img.onerror = () => {
                container.html(`<p class="text-white">${trans["Error al cargar la imagen"] || "Error al cargar la imagen"}</p>`);
            };
            img.src = file.url;
        } else if (esPDF) {
            $('.zoom-controls').addClass('d-none');
            // Usar embed o iframe para PDF
            container.html(`<iframe src="${file.url}" width="100%" height="80vh" style="border: none; background: white; min-height: 600px;"></iframe>`);
        } else {
            container.html(`<p class="text-white">${trans["Archivo no soportado para previsualización"] || "Archivo no soportado para previsualización"}</p>`);
        }

        // Mostrar/Ocultar flechas de navegación
        $('.nav-prev-media').css('visibility', indiceAdjuntoActual > 0 ? 'visible' : 'hidden');
        $('.nav-next-media').css('visibility', indiceAdjuntoActual < adjuntosVisualizables.length - 1 ? 'visible' : 'hidden');
    };

    /**
     * Maneja el deslizamiento (swipe) para navegación.
     */
    const handleGesture = () => {
        const deltaX = touchEndX - touchStartX;
        const deltaY = touchEndY - touchStartY;

        if (Math.abs(deltaX) > Math.abs(deltaY)) {
            // Deslizamiento horizontal
            if (deltaX < -50) {
                // Swipe Left -> Next
                $('.nav-next-media:visible').click();
            } else if (deltaX > 50) {
                // Swipe Right -> Prev
                $('.nav-prev-media:visible').click();
            }
        } else {
            // Deslizamiento vertical
            if (Math.abs(deltaY) > 100) {
                // Swipe vertical fuerte -> Cerrar modal
                $('#modal-media-viewer-reda').modal('hide');
            }
        }
    };

    /**
     * Abre el visor de medios en un índice específico.
     */
    const abrirMediaViewer = (index) => {
        indiceAdjuntoActual = index;
        actualizarMediaViewer();
        $('#modal-media-viewer-reda').modal('show');
    };

    /**
     * Genera el HTML de la lista de adjuntos.
     */
    const generarListaAdjuntosHtml = (adjuntos) => {
        const trans = window.RedaAlojamientoJson || {};
        if (!adjuntos || !adjuntos.length) {
            return `<p class="text-11 text-muted italic">${trans["Sin archivos adjuntos"] || "Sin archivos adjuntos"}</p>`;
        }

        // Filtrar para el visor
        const viewables = adjuntos.filter(f => f.es_imagen || f.url.toLowerCase().endsWith('.pdf'));

        let html = '<div class="list-group list-group-flush border-top border-bottom">';
        adjuntos.forEach(file => {
            const icon = file.es_imagen ? 'far fa-image' : 'far fa-file-alt';
            const esPDF = file.url.toLowerCase().endsWith('.pdf');
            const esViewable = file.es_imagen || esPDF;

            if (esViewable) {
                const viewIndex = viewables.findIndex(f => f.url === file.url);
                html += `
                    <div class="list-group-item list-group-item-action py-2 px-0 d-flex align-items-center border-0 bg-transparent pointer reda-viewer-trigger" data-index="${viewIndex}">
                        <div class="mr-2 bg-light-soft rounded d-flex align-items-center justify-content-center reda-adjunto-icon-box">
                            <i class="${icon} text-success text-10"></i>
                        </div>
                        <span class="text-11 text-dark text-truncate" title="${file.nombre}">${file.nombre}</span>
                    </div>
                `;
            } else {
                html += `
                    <a href="${file.url}" target="_blank" class="list-group-item list-group-item-action py-2 px-0 d-flex align-items-center border-0 bg-transparent">
                        <div class="mr-2 bg-light-soft rounded d-flex align-items-center justify-content-center reda-adjunto-icon-box">
                            <i class="${icon} text-success text-10"></i>
                        </div>
                        <span class="text-11 text-dark text-truncate" title="${file.nombre}">${file.nombre}</span>
                    </a>
                `;
            }
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
        const badgeClass = getStatusBadgeClass(item.estado);
        const statusText = formatStatusText(item.estado);

        let html = `
            <div class="mediacion-cabecera-principal mb-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge ${badgeClass} font-weight-600 py-2 px-3 text-12 shadow-sm">${statusText}</span>
                    <span class="text-muted font-weight-600 text-14">ID: #${item.id}</span>
                </div>
                <h5 class="font-weight-500 text-dark text-20 mb-0 leading-tight reda-motivo-clamped cursor-pointer reda-expandible" title="${item.motivo}">${item.motivo}</h5>
                <div class="mt-2">
                    <p class="text-12 text-muted mb-0 reda-property-name-clamped cursor-pointer reda-expandible" title="${item.propiedad_nombre}">
                        <i class="fas fa-home mr-1"></i>${item.propiedad_nombre}
                    </p>
                </div>
            </div>
        `;
        container.html(html);

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
                    <div class="mr-3">
                        <img src="${item.propiedad_foto}" class="rounded border object-fit-cover reda-reservacion-thumb reda-reservacion-thumb-size">
                    </div>
                    <div class="overflow-hidden">
                        <h6 class="text-13 font-weight-700 text-dark mb-0 text-truncate" title="${item.propiedad_nombre}">${item.propiedad_nombre}</h6>
                        <p class="text-11 text-muted mb-0 text-truncate"><i class="fas fa-map-marker-alt mr-1"></i>${item.propiedad_ubicacion}</p>
                    </div>
                </div>

                <div class="row no-gutters border-top pt-3">
                    <div class="col-6 border-right pr-2">
                        <span class="text-muted text-10 d-block text-uppercase letter-spacing-1">${trans["Llegada"] || "Llegada"}</span>
                        <span class="text-12 font-weight-600 text-dark">${item.booking_start_date}</span>
                    </div>
                    <div class="col-6 pl-2">
                        <span class="text-muted text-10 d-block text-uppercase letter-spacing-1">${trans["Salida"] || "Salida"}</span>
                        <span class="text-12 font-weight-600 text-dark">${item.booking_end_date}</span>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted text-11">${trans["Huéspedes"] || "Huéspedes"}</span>
                        <span class="text-11 font-weight-600 text-dark">${item.booking_guest}</span>
                    </div>
                </div>
            </div>
        `;
        container.html(html);

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

        let prioridadHtml = '';
        if (item.prioridad) {
            let colorClass = 'text-info';
            if (item.prioridad === 'Alta') colorClass = 'text-danger';
            else if (item.prioridad === 'Media') colorClass = 'text-warning';
            prioridadHtml = `<span class="${colorClass}">${item.prioridad}</span>`;
        }

        let html = `
            <div class="mediacion-resumen-detalle">
                <div class="detalles-extra-collapsible mb-0" id="detalles-extra-${item.id}">
                    <div class="pt-1">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">${trans["Prioridad"] || "Prioridad"}</span>
                            ${prioridadHtml}
                        </div>
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <span class="text-muted small">${trans["Actualizado"] || "Actualizado"}</span>
                            <span class="text-muted small">${item.actualizado_hace || ''}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <span class="text-muted small">${trans["Creado el"] || "Creado el"}</span>
                            <span class="text-muted small">${item.fecha_apertura}</span>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <span class="text-muted small d-block mb-1">${trans["Descripción"] || "Descripción"}</span>
                        <div class="bg-light rounded p-3">
                            <div class="text-13 text-muted reda-mediation-desc-clamped cursor-pointer reda-expandible">
                                ${item.descripcion || "<i>Sin descripción</i>"}
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 mt-4">
                        <h6 class="text-12 font-weight-700 mb-3 text-muted text-uppercase">${trans["Archivos adjuntos"] || "Archivos adjuntos"}</h6>
                        ${generarListaAdjuntosHtml(item.adjuntos)}
                    </div>

                    <div class="mt-4 pt-3 border-top bg-light-soft p-3 rounded border">
                        <h6 class="text-12 font-weight-700 mb-3 text-muted text-uppercase">${trans["Personas relacionadas"] || "Personas relacionadas"}</h6>
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

    const seleccionarMediacion = (id, conScroll = false) => {
        if (mediacionSeleccionadaId == id && !conScroll) return;
        mediacionSeleccionadaId = id;
        const item = mediacionesCargadas.find(m => m.id == id);
        if (!item) return;

        // Actualizar adjuntos visualizables para esta mediación (Imágenes y PDFs)
        adjuntosVisualizables = (item.adjuntos || []).filter(f => f.es_imagen || f.url.toLowerCase().endsWith('.pdf'));

        $('.card-mediacion').removeClass('active-mediacion');
        $(`.card-mediacion[data-id="${id}"]`).addClass('active-mediacion');

        renderizarCabeceraMediacion(item, '#disputas-header-content');
        renderizarTimeline(item.paso_actual, '#reda-timeline-container');
        renderizarReservacionMediacion(item, '#disputas-reservacion-content');
        renderizarResumenMediacion(item, '#disputas-info-extra-content');

        // Preparar UI Móvil (Tab de Ver Detalle)
        const trans = window.RedaAlojamientoJson || {};

        // Limpiar estados previos en móvil
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

        if (conScroll && window.innerWidth < 768) {
            const element = document.querySelector(`.container-mediacion[data-id="${id}"]`);
            if (element) element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    const renderizarLista = (items) => {
        const container = $('#disputas-list-container');
        const trans = window.RedaAlojamientoJson || {};
        
        if (!items || !items.length) {
            container.html(`<div class="text-center p-5"><img src="${APP_URL}/public/img/unnamed.png" class="img-fluid reda-img-empty-state"><p class="mt-3">No hay mediaciones</p></div>`);
            return;
        }

        let html = '<div class="row mt-4 m-0">';
        items.forEach(item => {
            const badgeClass = getStatusBadgeClass(item.estado);
            const statusText = formatStatusText(item.estado);

            // Prioridad con color
            let prioridadHtml = '';
            if (item.prioridad) {
                let colorClass = 'text-info';
                if (item.prioridad === 'Alta') colorClass = 'text-danger';
                else if (item.prioridad === 'Media') colorClass = 'text-warning';
                prioridadHtml = `<span class="${colorClass}">${item.prioridad}</span>`;
            }

            html += `
                <div class="col-md-12 px-3 mb-4 container-mediacion" data-id="${item.id}">
                    <div class="card border rounded-3 card-mediacion pointer shadow-sm-hover" data-id="${item.id}">
                        <div class="card-body p-0">
                            <div class="row m-0">
                                <div class="col-md-4 p-0">
                                    <div class="img-container h-100 bg-light d-flex align-items-center justify-content-center border-right">
                                        <img src="${item.propiedad_foto}" class="img-fluid w-100 h-100 object-fit-cover rounded-start">
                                    </div>
                                </div>
                                <div class="col-md-4 p-4 border-right">
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <span class="badge ${badgeClass}">${statusText}</span>
                                        <span class="text-muted small font-weight-600">ID: #${item.id}</span>
                                    </div>
                                    <h5 class="text-18 font-weight-500 text-color mb-1 reda-motivo-clamped cursor-pointer reda-expandible" title="${item.motivo}">${item.motivo}</h5>
                                    <div class="mt-2 mb-3">
                                        <p class="text-12 text-muted mb-0 reda-property-name-clamped cursor-pointer reda-expandible" title="${item.propiedad_nombre}">
                                            <i class="fas fa-home mr-1"></i>${item.propiedad_nombre}
                                        </p>
                                    </div>

                                    ${item.prioridad ? `
                                        <div class="text-muted small mb-1">
                                            <i class="fas fa-exclamation-circle mr-1"></i> ${trans["Prioridad"] || "Prioridad"}: ${prioridadHtml}
                                        </div>
                                    ` : ''}

                                    <div class="d-flex flex-column mt-3">
                                        <div class="text-muted small">
                                            <i class="far fa-clock mr-1"></i> ${trans["Actualizado"] || "Actualizado"} <span class="text-dark">${item.actualizado_hace || ''}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 p-4 d-flex flex-column justify-content-center bg-light-soft">
                                    ${generarBloquePersonasHtml(item)}
                                </div>
                            </div>

                            <!-- Mobile Detail Section -->
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
                                        <h6 class="fw-600 mb-3 text-14 border-bottom pb-2">${trans["Información adicional"] || "Información adicional"}</h6>
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

    const cargarMediaciones = (status, page = 1) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') window.RedaNotificaciones.esperar();

        getMediacionesPaginadas(status, page).then((data) => {
            if (data.success) {
                mediacionesCargadas = data.respuesta.data;
                renderizarLista(mediacionesCargadas);
                if (mediacionesCargadas.length > 0) seleccionarMediacion(mediacionesCargadas[0].id, false);
            }
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') window.RedaNotificaciones.ocultar();
        });
    };

    $(function() {
        if ($(containerId).length) {
            inyectarPestanasEstatus();

            $(document).on('click', '.disputa-tab-item', function() {
                $('.disputa-tab-item').removeClass('active');
                $(this).addClass('active');
                cargarMediaciones($(this).attr('data-status'));
            });

            $(document).on('click', '.card-mediacion', function(e) {
                if ($(e.target).closest('.reda-expandible').length || $(e.target).closest('.btn-ver-mensajes-mediacion').length) return;
                seleccionarMediacion($(this).attr('data-id'), true);
            });

            // Manejo de expansión independiente para textos largos (Unificado)
            $(document).on('click', '.reda-expandible', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).toggleClass('expanded');
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

            $(document).off('click', '.btn-ver-mensajes-mediacion').on('click', '.btn-ver-mensajes-mediacion', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const bookingId = $(this).attr('data-booking-id') || $(this).data('booking-id');
                console.log('REDA Frontend: Abriendo mensajes para booking:', bookingId);
                abrirMensajesMediacion(bookingId);
            });

            $(document).off('keydown', '#input-mensaje-reda').on('keydown', '#input-mensaje-reda', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('REDA Frontend: Enter presionado');
                    $('#btn-enviar-mensaje-reda').click();
                }
            });

            $(document).off('click', '#btn-enviar-mensaje-reda').on('click', '#btn-enviar-mensaje-reda', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                
                const $btn = $(this);
                const bookingId = $btn.attr('data-booking-id') || $btn.data('booking-id');
                const message = $('#input-mensaje-reda').val().trim();

                console.log('REDA Frontend: Intentando enviar mensaje para booking:', bookingId);

                if (!message) return;
                
                if (!bookingId) {
                    const msgErrId = window.RedaAlojamientoJson["Error: No se identificó la reservación. Cierre el chat y ábralo de nuevo."] || "Error: No se identificó la reservación. Cierre el chat y ábralo de nuevo.";
                    alert(msgErrId);
                    return;
                }

                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') window.RedaNotificaciones.esperar();

                enviarMensajeHuesped(bookingId, message, 0).then((response) => {
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') window.RedaNotificaciones.ocultar();

                    if (response.success) {
                        $('#input-mensaje-reda').val('');
                        obtenerMensajesMediacion(bookingId).then((data) => {
                            if (data.success) {
                                renderizarMensajes(data.respuesta.messages, data.respuesta.booking, data.respuesta.current_user_id);
                            }
                        });
                    } else {
                        const msgErr = response.mensaje_usuario || 'Error al enviar mensaje';
                        alert(msgErr);
                    }
                }).catch(err => {
                    console.error('REDA Frontend: Excepción al enviar mensaje:', err);
                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') window.RedaNotificaciones.ocultar();
                });
            });

            // Eventos del Visor de Medios
            $(document).on('click', '.reda-viewer-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const index = parseInt($(this).attr('data-index'));
                abrirMediaViewer(index);
            });

            $(document).on('click', '.nav-prev-media', function() {
                if (indiceAdjuntoActual > 0) {
                    indiceAdjuntoActual--;
                    actualizarMediaViewer();
                }
            });

            $(document).on('click', '.nav-next-media', function() {
                if (indiceAdjuntoActual < adjuntosVisualizables.length - 1) {
                    indiceAdjuntoActual++;
                    actualizarMediaViewer();
                }
            });

            $(document).on('click', '.btn-zoom-in', function() {
                currentZoom = Math.min(currentZoom + zoomStep, maxZoom);
                applyZoom();
            });

            $(document).on('click', '.btn-zoom-out', function() {
                currentZoom = Math.max(currentZoom - zoomStep, 1);
                applyZoom();
            });

            $(document).on('click', '.btn-zoom-reset', function() {
                currentZoom = 1;
                applyZoom();
            });

            // Atajos de teclado para el visor
            $(document).on('keydown', function(e) {
                if (!$('#modal-media-viewer-reda').hasClass('show')) return;
                
                if (e.key === 'ArrowLeft') $('.nav-prev-media:visible').click();
                if (e.key === 'ArrowRight') $('.nav-next-media:visible').click();
                if (e.key === 'Escape') $('#modal-media-viewer-reda').modal('hide');
            });

            // Soporte para Swiping
            const viewerModal = document.getElementById('modal-media-viewer-reda');
            if (viewerModal) {
                viewerModal.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                    touchStartY = e.changedTouches[0].screenY;
                }, { passive: true });

                viewerModal.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    touchEndY = e.changedTouches[0].screenY;
                    handleGesture();
                }, { passive: true });
            }

            cargarMediaciones('todos');
        }
    });

})(jQuery);
