// import ...

/**
 * Función llamada: Ejecuta la petición AJAX para guardar el ticket de soporte.
 */
export const guardarTicketSoporte = (formData) => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: APP_URL + '/reda/negocios/soporte-tecnico/store',
                type: 'POST',
                data: formData,
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
                    console.log('respuestaServidor', respuestaServidor);

                    let detalleValidacion = '';
                    if (respuestaServidor.errors && respuestaServidor.errors.mensaje) {
                        detalleValidacion = `<br /><span class="text-danger">${respuestaServidor.errors.mensaje[0]}</span>`;
                    }

                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    let respuesta = {
                        'success': false,
                        'message' : window.RedaAlojamientoJson["Error al crear el ticket de soporte"] || 'Error al crear el ticket de soporte',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}${detalleValidacion}`,
                        'respuesta': respuestaServidor.respuesta || '',
                        'code': x.status !== 0 ? x.status : 504,
                    };
                    resolve(respuesta);
                }
            })
        })(jQuery);
    });
}

(function($) {
    "use strict";

    const containerId = '#detalle_calificaciones_duenio';
    if ($(containerId).length) {
        $(function() {
            // --- ELEMENTOS ---
            const formBusqueda = $('#form-busqueda-inteligente');
            const inputReviewId = $('#input_review_id');
            const inputCustomerName = $('#input_customer_name');
            const inputDateFrom = $('#date_from');
            const inputDateTo = $('#date_to');
            const hiddenRatingFilter = $('#hidden_rating_filter');
            const hiddenIsReported = $('#hidden_is_reported');

            // --- LÓGICA DE BÚSQUEDA ---

            // Animación de espera al enviar
            formBusqueda.on('submit', function() {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }
                return true;
            });

            // Abrir el modal
            $('#trigger-busqueda-inteligente').on('click', function(e) {
                e.preventDefault();
                $('#modalBusquedaInteligente').modal('show');
            });

            /**
             * Resetea grupos de filtros excluyentes.
             * @param {string} excepcion El grupo que NO debe resetearse.
             */
            const resetearFiltrosExcluyentes = (excepcion) => {
                // 1. Grupo Puntual (ID / Nombre)
                if (excepcion !== 'puntual') {
                    inputReviewId.val('');
                    inputCustomerName.val('');
                }

                // 2. Grupo Rápidos (Botones)
                if (excepcion !== 'rapidos') {
                    $('.btn-filtro-rapido').removeClass('active');
                    hiddenRatingFilter.val('');
                    hiddenIsReported.val('');
                }

                // 3. Grupo Fechas
                if (excepcion !== 'fechas') {
                    inputDateFrom.val('');
                    inputDateTo.val('');
                }
            };

            // Listener para ID (Numérico estricto + Exclusión)
            inputReviewId.on('input', function() {
                const cleanValue = $(this).val().replace(/\D/g, '');
                $(this).val(cleanValue); // Forzar valor limpio en el input visible
                
                if (cleanValue !== '') {
                    inputCustomerName.val(''); // Limpiar compañero de grupo
                    resetearFiltrosExcluyentes('puntual');
                }
            });

            // Listener para Nombre (Exclusión)
            inputCustomerName.on('input', function() {
                if ($(this).val().trim() !== '') {
                    inputReviewId.val(''); // Limpiar compañero de grupo
                    resetearFiltrosExcluyentes('puntual');
                }
            });

            // Listener para Fechas (Exclusión)
            inputDateFrom.add(inputDateTo).on('change', function() {
                if ($(this).val() !== '') {
                    resetearFiltrosExcluyentes('fechas');
                }
            });

            // Filtros Rápidos (Exclusión + Auto-submit)
            $('.btn-filtro-rapido').on('click', function() {
                const filter = $(this).data('filter');
                resetearFiltrosExcluyentes('rapidos');
                
                if (filter === 'reported') {
                    hiddenIsReported.val('1');
                } else if (filter !== 'recent') {
                    hiddenRatingFilter.val(filter);
                }
                
                formBusqueda.submit();
            });

            // --- GESTIÓN DE REPORTES ---
            
            $(document).on('click', '.btn-reportar-reseña', function() {
                const calificacionId = $(this).data('id');
                const idExperiencia = $(this).data('id-experiencia');
                const usuario = $(this).data('usuario');
                const calificacion = $(this).data('calificacion');
                const comentario = $(this).data('comentario');
                
                $('#reporte_calificacion_id').val(calificacionId);
                $('#reporte_tema').val("Negocios");
                
                const tituloBase = window.RedaAlojamientoJson["Reportar Reseña"] || "Reportar Reseña";
                $('#modalReportarReseñaLabel').html(`<i class="fas fa-flag mr-2"></i> ${tituloBase} #${calificacionId}`);

                const linkErrorObj = {
                    id_de_la_reseña: calificacionId,
                    id_experiencia: idExperiencia,
                    nombre_usuario_que_hizo_la_reseña: usuario,
                    calificacion_reseña: calificacion,
                    comentario_reseña: comentario,
                    vista_origen: 'Reportar calificación'
                };
                
                $('#reporte_link_error').val(JSON.stringify(linkErrorObj));
                $('#mensaje').val('').removeClass('border-danger');
                $('#mensaje_error').removeClass('text-danger').addClass('text-muted');
                $('#prioridad').val('Media');
                $('#modalReportarReseña').modal('show');
            });

            $('#mensaje').on('input', function() {
                if ($(this).val().trim().length >= 10) {
                    $(this).removeClass('border-danger');
                    $('#mensaje_error').removeClass('text-danger').addClass('text-muted');
                }
            });

            $('#formReportarReseña').on('submit', async function(e) {
                e.preventDefault();
                const $mensaje = $('#mensaje');
                if ($mensaje.val().trim().length < 10) {
                    $mensaje.addClass('border-danger').focus();
                    $('#mensaje_error').removeClass('text-muted').addClass('text-danger');
                    return false;
                }
                
                window.RedaNotificaciones.esperar();
                const formData = $(this).serialize();
                const respuesta = await guardarTicketSoporte(formData);

                if (respuesta.success) {
                    $('#modalReportarReseña').modal('hide');
                    window.RedaNotificaciones.notificar(window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!", respuesta.mensaje_usuario, 'exito');
                } else {
                    window.RedaNotificaciones.notificar(window.RedaAlojamientoJson["Error"] || "Error", respuesta.mensaje_usuario, 'error');
                }
            });
        });
    }

})(jQuery);
