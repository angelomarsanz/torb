import { mediacionSvg } from './iconos';

let archivosSeleccionados = [];

/**
 * Verifica si existe una disputa para una reservación.
 */
export const verificarDisputaReda = (bookingId) => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: APP_URL + '/reda/disputas/check/' + bookingId,
                type: 'GET',
                success: function(data) {
                    resolve(data);
                },
                error: function (x) {
                    let respuestaServidor = {};
                    try { respuestaServidor = JSON.parse(x.responseText); } catch (e) { respuestaServidor = {}; }
                    
                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';
                    
                    resolve({
                        'success': false,
                        'message' : window.RedaAlojamientoJson["Error al cargar mediación"] || 'Error al cargar mediación',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                        'respuesta': { exists: false },
                        'code': x.status !== 0 ? x.status : 504,
                    });
                }
            })
        })(jQuery);
    });
}

/**
 * Obtiene el HTML del modal de detalle de mediación.
 */
export const obtenerModalDetalleMediacionReda = (id) => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: APP_URL + '/reda/disputas/get-detail-modal/' + id,
                type: 'GET',
                success: function(data) {
                    resolve(data);
                },
                error: function (x) {
                    let respuestaServidor = {};
                    try { respuestaServidor = JSON.parse(x.responseText); } catch (e) { respuestaServidor = {}; }
                    resolve({ 
                        success: false, 
                        mensaje_usuario: respuestaServidor.mensaje_usuario || 'Error al cargar modal',
                        code: x.status || 500
                    });
                }
            })
        })(jQuery);
    });
}

/**
 * Almacena una nueva mediación.
 */
export const guardarMediacionReda = (formData) => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: APP_URL + '/reda/disputas/store',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    resolve(data);
                },
                error: function (x) {
                    let respuestaServidor = {};
                    try { respuestaServidor = JSON.parse(x.responseText); } catch (e) { respuestaServidor = {}; }
                    
                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';
                    
                    resolve({
                        'success': false,
                        'message' : window.RedaAlojamientoJson["Error al procesar su solicitud."] || 'Error al procesar su solicitud.',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                        'respuesta': respuestaServidor.respuesta || '',
                        'code': x.status !== 0 ? x.status : 504,
                    });
                }
            })
        })(jQuery);
    });
}

/**
 * Peticion AJAX para obtener el HTML del modal de creacion.
 */
const getModalMediacionHtml = () => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: APP_URL + '/reda/disputas/get-modal',
                type: 'GET',
                success: function(data) {
                    resolve(data);
                },
                error: function (x) {
                    let respuestaServidor = {};
                    try { respuestaServidor = JSON.parse(x.responseText); } catch (e) { respuestaServidor = {}; }
                    resolve({ 
                        success: false, 
                        mensaje_usuario: respuestaServidor.mensaje_usuario || 'Error al cargar modal',
                        code: x.status || 500
                    });
                }
            })
        })(jQuery);
    });
}

(function($) {
    "use strict";

    /**
     * Renderiza la lista de archivos seleccionados con boton de eliminar.
     */
    const renderizarPrevisualizacionArchivos = () => {
        const container = $('#file-list-preview');
        if (!container.length) return;

        const textBtn = $('#text-btn-adjuntos');
        const trans = window.RedaAlojamientoJson || {};

        if (archivosSeleccionados.length === 0) {
            container.html('');
            textBtn.text(trans["Adjuntar archivos"] || "Adjuntar archivos");
            return;
        }

        // Cambiar texto del boton si hay archivos
        textBtn.text(trans["Adjuntar más archivos"] || "Adjuntar más archivos");

        let html = '';
        archivosSeleccionados.forEach((file, index) => {
            const icon = file.type.includes('image') ? 'far fa-image' : 'far fa-file-pdf';
            html += `
                <div class="reda-file-preview-item" data-index="${index}">
                    <div class="file-info">
                        <i class="${icon}"></i>
                        <span class="file-name">${file.name}</span>
                    </div>
                    <button type="button" class="btn-remove-file" data-index="${index}">
                        <i class="fas fa-times text-danger"></i>
                    </button>
                </div>
            `;
        });
        container.html(html);
    };

    /**
     * Inyecta el cuadro de mediación en la barra lateral de la reserva.
     */
    const inyectarCajaMediacionReda = async (force = false) => {
        const containerId = '#booking';
        const targetContainer = $(containerId);

        if (targetContainer.length) {
            if ($('#caja-mediacion-reda').length) {
                const currentBookingId = $('.send-btn').attr('data-booking') || '';
                if ($('#caja-mediacion-reda').attr('data-booking-id') !== currentBookingId || force) {
                     $('#caja-mediacion-reda').remove();
                } else {
                    return;
                }
            }

            const paymentText = window.RedaAlojamientoJson["Pago"] || "Pago";
            const paymentHeader = targetContainer.find('h5:contains("' + paymentText + '")');

            if (paymentHeader.length) {
                const sendBtn = $('.send-btn');
                const bookingId = sendBtn.attr('data-booking') || '';
                const otherUserId = sendBtn.attr('data-receiver') || '';
                const myUserId = window.USER_ID || '';

                if (!bookingId) return;

                let anfitrionId = '';
                let turistaId = '';

                const isHostView = $('.active-sidebar:contains("' + (window.RedaAlojamientoJson["Mis Reservas"] || "Bookings") + '")').length > 0;
                const isTouristView = $('.active-sidebar:contains("' + (window.RedaAlojamientoJson["Mis Viajes"] || "Trips") + '")').length > 0;

                if (isHostView) {
                    anfitrionId = myUserId;
                    turistaId = otherUserId;
                } else if (isTouristView) {
                    anfitrionId = otherUserId;
                    turistaId = myUserId;
                } else {
                    anfitrionId = otherUserId; 
                    turistaId = myUserId;
                }

                if (bookingId) {
                    targetContainer.attr('data-reservacion-id', bookingId);
                    targetContainer.attr('data-anfitrion-id', anfitrionId);
                    targetContainer.attr('data-turista-id', turistaId);
                }

                const mediacionText = window.RedaAlojamientoJson["Mediación"] || "Mediación";
                
                const cajaHtml = `
                    <div id="caja-mediacion-reda" class="row mt-3 mb-1 reda-mediation-box" data-booking-id="${bookingId}">
                        <div class="col-md-12">
                            <div class="content-caja-mediacion p-0">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="text-success mr-2 d-flex align-items-center">
                                        ${mediacionSvg}
                                    </div>
                                    <h5 class="reda-mediation-title">${mediacionText}</h5>
                                </div>
                                <div class="caja-contenido-dinamico">
                                    <div class="text-center"><div class="spinner-border spinner-border-sm text-success" role="status"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                paymentHeader.closest('.row').before(cajaHtml);

                const response = await verificarDisputaReda(bookingId);
                const container = $('#caja-mediacion-reda').find('.caja-contenido-dinamico');
                
                if (response.success && response.respuesta.exists) {
                    const d = response.respuesta.data;
                    const verText = window.RedaAlojamientoJson["Ver"] || "Ver";
                    const fechaLabel = window.RedaAlojamientoJson["Fecha:"] || "Fecha:";
                    const estatusLabel = window.RedaAlojamientoJson["Estatus:"] || "Estatus:";
                    const pasoLabel = window.RedaAlojamientoJson["Paso:"] || "Paso:";
                    const listadoText = window.RedaAlojamientoJson["Listado de mediaciones"] || "Listado de mediaciones";
                    const procesoIdText = window.RedaAlojamientoJson["Proceso de mediación con ID #"] || "Proceso de mediación con ID #";

                    const htmlActiva = `
                        <div class="info-mediacion-activa mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="text-14 mb-0"><strong>${procesoIdText}${d.id}</strong></p>
                                <button class="btn btn-sm btn-outline-success py-0 px-2 text-12 font-weight-700 btn-ver-detalle-mediacion-reda" data-id="${d.id}">
                                    ${verText}
                                </button>
                            </div>
                            <div class="mb-1">
                                <span class="reda-mediation-label mb-0">${fechaLabel}</span> <span class="text-12 text-dark font-weight-600">${d.fecha}</span>
                            </div>
                            <div class="mb-1">
                                <span class="reda-mediation-label mb-0">${estatusLabel}</span> <span class="text-12 text-dark font-weight-600">${d.estado}</span>
                            </div>
                            <div class="mb-3">
                                <span class="reda-mediation-label mb-0">${pasoLabel}</span> <span class="text-12 text-dark font-weight-600">${d.paso_actual}</span>
                            </div>

                            <a href="${APP_URL}/reda/disputas" class="btn btn-outline-success btn-block text-14 font-weight-700" data-reda-plugin>
                                ${listadoText}
                            </a>
                        </div>
                    `;
                    container.html(htmlActiva);
                } else if (!response.success) {
                    container.html(`<p class="text-12 text-danger">${response.mensaje_usuario}</p>`);
                } else {
                    const sinMediacionText = window.RedaAlojamientoJson["Sin mediación activa"] || "Sin mediación activa";
                    const ayudaText = window.RedaAlojamientoJson["Si tienes problema con esta reserva, puedes solicitar ayuda a nuestro equipo"] || "Si tienes problema con esta reserva, puedes solicitar ayuda a nuestro equipo";
                    const solicitarText = window.RedaAlojamientoJson["Solicitar mediación"] || "Solicitar mediación";

                    const htmlSolicitar = `
                        <h6 class="text-14 font-weight-700 mb-1 mt-2">${sinMediacionText}</h6>
                        <p class="text-12 text-muted mb-3">${ayudaText}</p>
                        <button id="btn-solicitar-mediacion-reda" 
                            class="btn btn-success btn-block text-14 font-weight-700"
                            data-reservacion-id="${bookingId}"
                            data-anfitrion-id="${anfitrionId}"
                            data-turista-id="${turistaId}">
                            ${solicitarText}
                        </button>
                    `;
                    container.html(htmlSolicitar);
                }
            }
        }
    };

    /**
     * Carga e inyecta el modal de mediación si no existe.
     */
    const cargarModalMediacion = async () => {
        if ($('#modal-mediacion-reda').length) return;

        const data = await getModalMediacionHtml();
        if (data.success) {
            $('body').append(data.respuesta);
            configurarEventosModal();
        }
    };

    /**
     * Carga e inyecta el modal de detalle de mediación.
     */
    const cargarModalDetalleMediacion = async (id) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }
        
        const response = await obtenerModalDetalleMediacionReda(id);
        
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
            window.RedaNotificaciones.ocultar();
        }

        if (response.success) {
            $('#modal-detalle-mediacion-reda').remove();
            $('body').append(response.respuesta);
            $('#modal-detalle-mediacion-reda').modal('show');
        } else {
            const errorTitle = window.RedaAlojamientoJson["Error"] || "Error";
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                window.RedaNotificaciones.notificar(errorTitle, response.mensaje_usuario, 'error');
            } else {
                alert(response.mensaje_usuario);
            }
        }
    };

    /**
     * Configura los eventos del formulario dentro del modal.
     */
    const configurarEventosModal = () => {
        // Trigger para el input oculto
        $(document).off('click', '#btn-trigger-documentos').on('click', '#btn-trigger-documentos', function() {
            $('#documentos').click();
        });

        $(document).off('change', '#documentos').on('change', '#documentos', function() {
            let files = Array.from($(this)[0].files);
            if (files.length === 0) return;
            
            // Acumular archivos
            archivosSeleccionados = archivosSeleccionados.concat(files);
            
            // Limpiar input para permitir seleccionar el mismo archivo de nuevo si se borra
            $(this).val('');
            
            renderizarPrevisualizacionArchivos();
        });

        $(document).off('click', '.btn-remove-file').on('click', '.btn-remove-file', function(e) {
            e.preventDefault();
            const index = $(this).attr('data-index');
            archivosSeleccionados.splice(index, 1);
            renderizarPrevisualizacionArchivos();
        });

        $(document).off('submit', '#form-mediacion-reda').on('submit', '#form-mediacion-reda', async function(e) {
            e.preventDefault();
            const form = $(this);
            
            // Construir FormData manualmente para incluir los archivos acumulados
            const formData = new FormData();
            
            // Agregar campos normales
            const serializado = form.serializeArray();
            $.each(serializado, function(i, field) {
                formData.append(field.name, field.value);
            });

            // Agregar archivos acumulados
            archivosSeleccionados.forEach(file => {
                formData.append('documentos[]', file);
            });

            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }
            
            const response = await guardarMediacionReda(formData);
            
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                window.RedaNotificaciones.ocultar();
            }

            if (response.success) {
                $('#modal-mediacion-reda').modal('hide');
                const exitoTitle = window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!";
                
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                    window.RedaNotificaciones.notificar(exitoTitle, response.mensaje_usuario, 'exito');
                } else {
                    alert(response.mensaje_usuario);
                }
                
                form[0].reset();
                archivosSeleccionados = [];
                renderizarPrevisualizacionArchivos();
                
                inyectarCajaMediacionReda(true);
            } else {
                const errorTitle = window.RedaAlojamientoJson["Error"] || "Error";
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.notificar === 'function') {
                    window.RedaNotificaciones.notificar(errorTitle, response.mensaje_usuario, 'error');
                } else {
                    alert(response.mensaje_usuario);
                }
            }
        });
    };

    $(function() {
        // Lógica para posicionar la conversación en el Inbox si viene con un ID en la URL
        if (window.location.pathname.endsWith('/inbox')) {
            const urlParams = new URLSearchParams(window.location.search);
            const bookingId = urlParams.get('id');
            
            if (bookingId) {
                // Pequeño delay para asegurar que el DOM y los eventos de inbox.js estén listos
                setTimeout(() => {
                    const targetConversation = $(`.conversassion[data-id="${bookingId}"]`);
                    if (targetConversation.length) {
                        // 1. Disparar el click para que la lógica original cargue los mensajes vía AJAX
                        targetConversation.click();
                        
                        // 2. Resaltar visualmente (clase active)
                        $('.conversassion').removeClass('active');
                        targetConversation.addClass('active');
                        
                        // 3. Desplazar el scroll del sidebar hacia el elemento
                        targetConversation[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 500);
            }
        }

        if ($('#messages').length && $('#booking').length) {
            inyectarCajaMediacionReda();
            cargarModalMediacion();

            $(document).on('click', '#btn-solicitar-mediacion-reda', function() {
                const btn = $(this);
                const bookingId = btn.attr('data-reservacion-id');
                const anfitrionId = btn.attr('data-anfitrion-id');
                const turistaId = btn.attr('data-turista-id');

                $('#reda-booking-id').val(bookingId);
                $('#reda-anfitrion-id').val(anfitrionId);
                $('#reda-turista-id').val(turistaId);

                // Limpiar adjuntos previos al abrir
                archivosSeleccionados = [];
                renderizarPrevisualizacionArchivos();

                $('#modal-mediacion-reda').modal('show');
            });

            $(document).on('click', '.btn-ver-detalle-mediacion-reda', function() {
                const id = $(this).attr('data-id');
                cargarModalDetalleMediacion(id);
            });

            const targetNode = document.getElementById('booking');
            if (targetNode) {
                const observer = new MutationObserver((mutationsList) => {
                    for (let mutation of mutationsList) {
                        if (mutation.type === 'childList') {
                            setTimeout(inyectarCajaMediacionReda, 100);
                        }
                    }
                });
                observer.observe(targetNode, { childList: true, subtree: true });
            }
        }
    });

})(jQuery);
