// import ...

/**
 * Función llamada: Ejecuta la petición AJAX para guardar el ticket de soporte.
 * Cumple con las directrices de GEMINI.md (Promesas, Estructura AJAX, Manejo de errores).
 * 
 * @param {Object} formData Datos del formulario serializados o como objeto.
 * @returns {Promise} Resolviedo con un objeto de respuesta estandarizado.
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
                    // 1. Intentamos obtener el JSON que el servidor envió junto con el error
                    let respuestaServidor = {};
                    try {
                        // x.responseText contiene el cuerpo del JSON enviado por Laravel
                        respuestaServidor = JSON.parse(x.responseText);
                    } catch (e) {
                        respuestaServidor = {};
                    }
                    console.log('respuestaServidor', respuestaServidor);

                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    // 2. Construimos la respuesta usando los datos reales del servidor si existen
                    let respuesta = {
                        'success': false,
                        'message' : window.RedaAlojamientoJson["Error al crear el ticket de soporte"] || 'Error al crear el ticket de soporte',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
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

    $(function() {
        // --- Función llamadora ---
        
        // Al hacer clic en el botón Reportar
        $(document).on('click', '.btn-reportar-reseña', function() {
            const calificacionId = $(this).data('id');
            const negocio = $(this).data('negocio');
            const usuario = $(this).data('usuario');
            const calificacion = $(this).data('calificacion');
            const comentario = $(this).data('comentario');
            
            // Llenar campos ocultos del modal
            $('#reporte_calificacion_id').val(calificacionId);
            $('#reporte_tema').val("Negocios");
            
            const linkErrorObj = {
                id_de_la_reseña: calificacionId,
                nombre_usuario_que_hizo_la_reseña: usuario,
                calificacion_reseña: calificacion,
                comentario_reseña: comentario
            };
            
            $('#reporte_link_error').val(JSON.stringify(linkErrorObj));
            
            // Limpiar textarea y resetear prioridad
            $('#mensaje').val('');
            $('#prioridad').val('Media');
            
            // Mostrar modal
            $('#modalReportarReseña').modal('show');
        });

        // Manejar el envío del formulario
        $('#formReportarReseña').on('submit', async function(e) {
            e.preventDefault();
            
            // Animación de espera (Directriz GEMINI.md)
            window.RedaNotificaciones.esperar();

            const formData = $(this).serialize();
            
            // Ejecutamos la función llamada (Promesa)
            const respuesta = await guardarTicketSoporte(formData);

            if (respuesta.success) {
                $('#modalReportarReseña').modal('hide');
                // Mostramos notificación de éxito
                window.RedaNotificaciones.notificar(
                    window.RedaAlojamientoJson["¡Éxito!"] || "¡Éxito!",
                    respuesta.mensaje_usuario,
                    'exito'
                );
            } else {
                // Mostramos notificación de error
                window.RedaNotificaciones.notificar(
                    window.RedaAlojamientoJson["Error"] || "Error",
                    respuesta.mensaje_usuario,
                    'error'
                );
            }
        });
    });

})(jQuery);
