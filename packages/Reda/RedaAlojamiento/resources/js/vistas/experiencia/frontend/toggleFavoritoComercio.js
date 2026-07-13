/**
 * Alterna el estado de favorito de un comercio vía Ajax.
 */
export const toggleFavoritoComercio = (id) => {
    return new Promise((resolve) => {
        (function( $ ) {
            // Validación preventiva
            if (!id || id === 'undefined' || id === 'null') {
                resolve({
                    'success': false,
                    'message' : 'Invalid ID',
                    'mensaje_usuario': window.RedaAlojamientoJson?.["Error al identificar el comercio"] || "Error al identificar el comercio",
                    'respuesta': '',
                    'code': 400
                });
                return;
            }

            $.ajax({
                url: APP_URL + '/reda/negocios/experiencias/toggle-favorito/' + id,
                type: 'POST',
                data: {
                    "_token": $('meta[name="csrf-token"]').attr('content'),
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

                    const mensajeErrorBase = window.RedaAlojamientoJson?.["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    let mensajeUsuario = respuestaServidor.mensaje_usuario;

                    // Si es error 401 (No autenticado), el middleware ya envía un mensaje_usuario
                    if (x.status === 401 && !mensajeUsuario) {
                        mensajeUsuario = window.RedaAlojamientoJson?.["Debes iniciar sesión para agregar a favoritos"] || "Debes iniciar sesión para agregar a favoritos";
                    }

                    let respuesta = {
                        'success': false,
                        'message' : 'Error toggling favorite',
                        'mensaje_usuario': mensajeUsuario ?? `${mensajeErrorBase}.${detalleError}`,
                        'respuesta': respuestaServidor.respuesta || '',
                        'code': x.status !== 0 ? x.status : 504,
                    };
                    resolve(respuesta);
                }
            })
        })(jQuery);
    });
}
