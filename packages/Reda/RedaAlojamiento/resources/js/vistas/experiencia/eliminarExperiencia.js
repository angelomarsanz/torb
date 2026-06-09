// import ...

export const eliminarExperiencia = (idExperiencia) => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: APP_URL + '/reda/negocios/experiencias/eliminar-experiencia/' + idExperiencia, // Ajusta la ruta según tu web.php
                type: 'DELETE',
                data: {
                    "_token": $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(data) {
                    resolve(data);
                },
                error: function (x, xs, xt) {
                    // 1. Intentamos obtener el JSON que el servidor envió junto con el error 400
                    let respuestaServidor = {};
                    try {
                        // x.responseText contiene el cuerpo del JSON enviado por Laravel
                        respuestaServidor = JSON.parse(x.responseText);;
                    } catch (e) {
                        respuestaServidor = {};
                    }
                    console.log('respuestaServidor', respuestaServidor);

                    const mensajeErrorBase = window.RedaAlojamiento?.general?.error_en_el_servidor_de_Torbian || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    // 2. Construimos la respuesta usando los datos reales del servidor si existen

                    let respuesta = {
                        'success': false,
                        'message' : 'Error eliminando experiencia',
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
