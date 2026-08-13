export const obtenerConteoMediaciones = () => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: APP_URL + '/reda/disputas/count-activas',
                type: 'GET',
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
                    
                    let respuesta = {
                        'success': false,
                        'message' : 'Error obteniendo conteo de mediaciones',
                        'respuesta': 0,
                        'code': x.status !== 0 ? x.status : 504,
                    };
                    resolve(respuesta);
                }
            })
        })(jQuery);
    });
}
