(function( $ ) {
    "use strict";

    /**
     * Script para el módulo de Soporte Técnico (Admin) - Vista Show (Detalle)
     * Maneja la inicialización de componentes de la interfaz de usuario para el detalle del ticket.
     */
    const containerId = '#show_soporte_tecnico';
    const indexUrl = APP_URL + '/admin/reda/general/soporte-tecnico';

    /**
     * Obtiene un valor de un objeto buscando por claves similares (útil para problemas de encoding con 'ñ')
     * @param {Object} obj - El objeto donde buscar
     * @param {Array} posiblesClaves - Lista de nombres de claves posibles
     * @returns {*} El valor encontrado o null
     */
    const obtenerValorSeguro = (obj, posiblesClaves) => {
        if (!obj || typeof obj !== 'object') return null;

        // 1. Intento de coincidencia exacta
        for (const clave of posiblesClaves) {
            if (obj[clave] !== undefined) return obj[clave];
        }

        // 2. Intento de coincidencia por unicode (si la clave tiene ñ)
        for (const clave of posiblesClaves) {
            if (clave.includes('ñ')) {
                const unicodeClave = clave.replace(/ñ/g, '\\u00f1');
                if (obj[unicodeClave] !== undefined) return obj[unicodeClave];
            }
        }

        // 3. Búsqueda por coincidencia parcial o normalizada (ignorando ñ/n)
        const keys = Object.keys(obj);
        for (const clave of posiblesClaves) {
            const normalizedClave = clave.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/ñ/g, 'n');
            for (const key of keys) {
                const normalizedKey = key.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/ñ/g, 'n');
                if (normalizedKey === normalizedClave || normalizedKey.includes(normalizedClave)) {
                    return obj[key];
                }
            }
        }

        return null;
    };

    /**
     * Elimina una reseña vía AJAX
     * @param {number|string} idReseña - ID de la reseña a eliminar
     * @param {number|string} ticketId - ID del ticket relacionado
     * @returns {Promise}
     */
    const eliminarReseña = (idReseña, ticketId) => {
        const urlEliminar = APP_URL + '/admin/reda/general/eliminar-calificacion/' + idReseña;
        console.log('Intentando eliminar reseña en URL:', urlEliminar);

        return new Promise((resolve) => {
            (function( $ ) {
                $.ajax({
                    url: urlEliminar,
                    type: 'DELETE',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        "ticket_id": ticketId // Enviamos el ID del ticket para cerrarlo automáticamente
                    },
                    success: function(data) {
                        console.log('Respuesta de eliminación:', data);
                        resolve(data);
                    },
                    error: function (x, xs, xt) {
                        console.error('Error en AJAX de eliminación:', x);
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
                            'message' : window.RedaAlojamientoJson["Error eliminando reseña"] || 'Error eliminando reseña',
                            'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                            'respuesta': respuestaServidor.respuesta || '',
                            'code': x.status !== 0 ? x.status : 504,
                        };
                        resolve(respuesta);
                    }
                });
            })(jQuery);
        });
    };

    /**
     * Cierra un ticket manualmente vía AJAX
     * @param {number|string} ticketId - ID del ticket
     * @param {string} resultado - Resultado de la gestión (ej: 'Mantenida')
     * @returns {Promise}
     */
    const cerrarTicketAjax = (ticketId, resultado) => {
        const urlCerrar = APP_URL + '/admin/reda/general/soporte-tecnico/cerrar/' + ticketId;
        return new Promise((resolve) => {
            (function( $ ) {
                $.ajax({
                    url: urlCerrar,
                    type: 'POST',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        "resultado": resultado
                    },
                    success: function(data) {
                        resolve(data);
                    },
                    error: function (x, xs, xt) {
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
                            'message' : window.RedaAlojamientoJson["Error al cerrar el ticket"] || 'Error al cerrar el ticket',
                            'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                            'respuesta': respuestaServidor.respuesta || '',
                            'code': x.status !== 0 ? x.status : 504,
                        };
                        resolve(respuesta);
                    }
                });
            })(jQuery);
        });
    };

    /**
     * Genera el contenido dinámico del modal basado en el origen del ticket
     * @param {Object|string} linkError - Datos del JSON guardado en link_error
     * @param {Object} metadatosTicket - Datos adicionales (ticket_id, recurso_existe, estatus)
     */
    const cargarContenidoGestionar = (linkError, metadatosTicket) => {
        const containerModal = $('#contenido_modal_gestionar');
        const containerAcciones = $('#acciones_dinamicas_modal');

        // Limpiar acciones previas
        containerAcciones.empty();

        // Si el ticket ya está cerrado o el recurso no existe, mostramos mensaje informativo
        if (metadatosTicket.estatus === 'Cerrado' || metadatosTicket.recursoExiste === '0') {
            const mensajeBase = window.RedaAlojamientoJson["Este ticket ya ha sido procesado o el recurso vinculado (ej: la reseña) ya no existe en la base de datos."] || "Este ticket ya ha sido procesado o el recurso vinculado (ej: la reseña) ya no existe en la base de datos.";
            
            containerModal.html(`
                <div class="text-center p-5">
                    <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                    <h4 class="fw-bold">${window.RedaAlojamientoJson["Ticket ya gestionado"] || "Ticket ya gestionado"}</h4>
                    <p class="text-muted">${mensajeBase}</p>
                </div>
            `);
            return;
        }

        // --- DECODIFICACIÓN ROBUSTA (Doble/Triple JSON) ---
        let datosSoporte = linkError;
        console.log('datosSoporte original:', datosSoporte);

        // Decodificación recursiva: mientras sea un string, intentamos parsearlo.
        let niveles = 0;
        while (typeof datosSoporte === 'string' && niveles < 5) {
            try {
                let parseado = JSON.parse(datosSoporte);
                if (parseado === datosSoporte) break;
                datosSoporte = parseado;
                niveles++;
            } catch (e) {
                break;
            }
        }

        // Normalizamos la vista de origen
        let vistaOrigen = datosSoporte?.vista_origen || '';

        console.log('Datos procesados:', datosSoporte);
        console.log('Identificador de origen final:', vistaOrigen);

        let htmlContenido = '';

        switch (vistaOrigen) {
            case 'Reportar calificación':
                console.log('Renderizando panel de Gestión de Calificaciones');

                // Extracción robusta de datos
                const idReseña = obtenerValorSeguro(datosSoporte, ['id_reseña', 'id_de_la_reseña', 'id_de_la_rese\u00f1a']) || 'N/A';
                const usuarioReseña = obtenerValorSeguro(datosSoporte, ['nombre_usuario_que_hizo_la_reseña', 'nombre_usuario', 'usuario_reseña']) || 'N/A';
                const calificacion = obtenerValorSeguro(datosSoporte, ['calificacion_reseña', 'calificacion', 'puntos']) || 0;
                const comentario = obtenerValorSeguro(datosSoporte, ['comentario_reseña', 'comentario', 'mensaje']) || '';

                htmlContenido = `
                    <div class="alert alert-custom bg-light-primary border-primary border-dashed p-4 mb-0">
                        <h4 class="text-primary"><i class="fa fa-star me-2"></i>${window.RedaAlojamientoJson["Gestión de Calificaciones"] || "Gestión de Calificaciones"}</h4>
                        <p class="mb-3">${window.RedaAlojamientoJson["Este ticket fue reportado desde el detalle de una calificación. Aquí puede realizar acciones directas sobre la reseña reportada."] || "Este ticket fue reportado desde el detalle de una calificación. Aquí puede realizar acciones directas sobre la reseña reportada."}</p>

                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <tr>
                                    <td class="fw-bold w-250px">${window.RedaAlojamientoJson["ID de Reseña"] || "ID de Reseña"}:</td>
                                    <td>${idReseña}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">${window.RedaAlojamientoJson["Usuario que reseñó"] || "Usuario que reseñó"}:</td>
                                    <td>${usuarioReseña}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">${window.RedaAlojamientoJson["Calificación"] || "Calificación"}:</td>
                                    <td>
                                        <div class="text-warning">
                                            ${'★'.repeat(Math.min(5, Math.max(0, parseInt(calificacion))))}${'☆'.repeat(Math.max(0, 5 - Math.min(5, Math.max(0, parseInt(calificacion)))))}
                                            <span class="text-muted ms-1">(${calificacion}/5)</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">${window.RedaAlojamientoJson["Comentario"] || "Comentario"}:</td>
                                    <td><em class="text-muted">"${comentario}"</em></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;

                // Inyectar acciones en el footer
                containerAcciones.html(`
                    <button class="btn btn-danger btn-flat btn-sm btn-accion-directa me-2" data-accion="eliminar" data-id="${idReseña}" data-ticket-id="${metadatosTicket.ticketId}">
                        <i class="fa fa-trash me-1"></i> ${window.RedaAlojamientoJson["Eliminar Reseña"] || "Eliminar Reseña"}
                    </button>
                    <button class="btn btn-success btn-flat btn-sm btn-accion-directa" data-accion="mantener" data-id="${idReseña}" data-ticket-id="${metadatosTicket.ticketId}">
                        <i class="fa fa-check me-1"></i> ${window.RedaAlojamientoJson["Mantener la reseña"] || "Mantener la reseña"}
                    </button>
                `);
                break;

            default:
                htmlContenido = `
                    <div class="text-center p-5">
                        <i class="fa fa-info-circle fa-3x text-muted mb-3"></i>
                        <p class="fs-5">${window.RedaAlojamientoJson["No hay una vista de gestión específica para este origen."] || "No hay una vista de gestión específica para este origen."}</p>
                        <p class="text-muted small">${window.RedaAlojamientoJson["Origen"] || "Origen"}: ${vistaOrigen || 'N/A'}</p>
                    </div>
                `;
                break;
        }

        containerModal.html(htmlContenido);
    };

    /**
     * Inicializa los componentes necesarios en la vista de detalle
     */
    const inicializarDetalleSoporte = () => {
        // Inicialización de tooltips
        if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover'
            });
        }

        // Evento para el botón de gestionar ticket
        $(document).on('click', '#btn_gestionar_ticket', function(e) {
            e.preventDefault();
            console.log('Click en gestionar ticket');

            const linkError = $(this).data('link-error');
            const metadatosTicket = {
                ticketId: $(this).data('ticket-id'),
                recursoExiste: $(this).data('recurso-existe').toString(),
                estatus: $(this).data('estatus')
            };

            // Reiniciar contenido del modal y botones de acción
            $('#contenido_modal_gestionar').html(`
                <div class="text-center p-5">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">${window.RedaAlojamientoJson["Cargando opciones de gestión..."] || "Cargando opciones de gestión..."}</p>
                </div>
            `);
            $('#acciones_dinamicas_modal').empty();

            // Mostrar modal
            $('#modal_gestionar_ticket').modal('show');

            // Cargar contenido basado en los datos
            setTimeout(() => {
                cargarContenidoGestionar(linkError, metadatosTicket);
            }, 300);
        });

        // Evento para acciones directas (Eliminar/Mantener)
        $(document).on('click', '.btn-accion-directa', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const accion = $btn.data('accion');
            const id = $btn.data('id');
            const ticketId = $btn.data('ticket-id');

            if (accion === 'eliminar') {
                const mensajeConfirmacion = window.RedaAlojamientoJson["¿Está seguro de que desea eliminar esta reseña? Esta acción no se puede deshacer."] || "¿Está seguro de que desea eliminar esta reseña? Esta acción no se puede deshacer.";
                
                window.mostrarConfirmacion(mensajeConfirmacion, async () => {
                    window.RedaNotificaciones.esperar();
                    
                    const resultado = await eliminarReseña(id, ticketId);
                    
                    if (resultado.success) {
                        window.RedaNotificaciones.notificar(
                            window.RedaAlojamientoJson["Reseña eliminada"] || "Reseña eliminada",
                            resultado.mensaje_usuario,
                            'exito',
                            indexUrl // Redireccionar al índice tras éxito
                        );
                    } else {
                        window.RedaNotificaciones.notificar(
                            window.RedaAlojamientoJson["Error"] || "Error",
                            resultado.mensaje_usuario,
                            'error'
                        );
                    }
                }, window.RedaAlojamientoJson["Confirmar eliminación"] || "Confirmar eliminación", window.RedaAlojamientoJson["Eliminar"] || "Eliminar");
            } else if (accion === 'mantener') {
                const mensajeConfirmacion = window.RedaAlojamientoJson["¿Desea cerrar este ticket manteniendo la reseña intacta?"] || "¿Desea cerrar este ticket manteniendo la reseña intacta?";
                
                window.mostrarConfirmacion(mensajeConfirmacion, async () => {
                    window.RedaNotificaciones.esperar();
                    
                    const resultado = await cerrarTicketAjax(ticketId, 'Reseña mantenida');
                    
                    if (resultado.success) {
                        window.RedaNotificaciones.notificar(
                            window.RedaAlojamientoJson["Ticket Cerrado"] || "Ticket Cerrado",
                            resultado.mensaje_usuario,
                            'exito',
                            indexUrl // Redireccionar al índice tras éxito
                        );
                    } else {
                        window.RedaNotificaciones.notificar(
                            window.RedaAlojamientoJson["Error"] || "Error",
                            resultado.mensaje_usuario,
                            'error'
                        );
                    }
                }, window.RedaAlojamientoJson["Mantener reseña"] || "Mantener reseña", window.RedaAlojamientoJson["Mantener"] || "Mantener");
            }
        });

        // Evento para mostrar animación de espera al volver al listado
        $(document).on('click', '.btn-volver-listado', function(e) {
            // Solo si es un link con href válido y no se abre en pestaña nueva
            if (this.href && !this.target && !e.ctrlKey && !e.metaKey) {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }
            }
        });

        // Log de carga exitosa
        console.log(window.RedaAlojamientoJson["Vista de detalle de ticket cargada"] || "Vista de detalle de ticket cargada.");
    };

    // Ejecutar cuando el DOM esté listo y el contenedor exista
    if ($(containerId).length) {
        $(function() {
            console.log('showSoporteTecnico.js cargado correctamente.')
            inicializarDetalleSoporte();
        });
    }

})(jQuery);
