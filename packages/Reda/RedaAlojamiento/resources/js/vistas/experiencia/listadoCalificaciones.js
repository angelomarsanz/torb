// packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/listadoCalificaciones.js

/**
 * Obtiene los nombres de los comercios vía AJAX y los agrega al datalist.
 * Cumple con las directrices de copilot-instructions.md (Promesas, Estructura AJAX, Manejo de errores).
 * 
 * @returns {Promise} Resolviedo con un objeto de respuesta estandarizado.
 */
export const obtenerSugerenciasBusqueda = () => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: APP_URL + '/reda/negocios/mis-calificaciones/get-nombres-comercios',
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
                    console.log('respuestaServidor', respuestaServidor);

                    const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    let respuesta = {
                        'success': false,
                        'message' : window.RedaAlojamientoJson["Error al cargar sugerencias de búsqueda"] || 'Error al cargar sugerencias de búsqueda',
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

    const containerId = '#listado_calificaciones_duenio';
    if ($(containerId).length) {
        $(async function() {
            // Cargar nombres de comercios para el datalist (búsqueda inteligente)
            // Se realiza en segundo plano sin bloquear la pantalla (sin 'esperar')
            const respuestaSugerencias = await obtenerSugerenciasBusqueda();
            
            if (respuestaSugerencias.success && Array.isArray(respuestaSugerencias.respuesta)) {
                const $datalist = $('#lista-nombres-comercios');
                $datalist.empty();
                respuestaSugerencias.respuesta.forEach(function(nombre) {
                    $datalist.append($('<option>').attr('value', nombre));
                });
            }

            // --- Animación de espera para la búsqueda ---
            $('#form-busqueda-comercios').on('submit', function() {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }
            });

            // --- Búsqueda de Comercios ---
            
            // Abrir el modal al hacer clic en el disparador (barra o icono de lupa superior)
            $('#trigger-busqueda-inteligente').on('click', function(e) {
                e.preventDefault();
                $('#modalBusquedaInteligente').modal('show');
            });

            // Si se desea que la búsqueda sea inmediata al seleccionar de la lista (dentro del modal)
            $(document).on('input', '#input-busqueda-comercios', function() {
                const val = $(this).val();
                const options = $('#lista-nombres-comercios option');
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === val) {
                        $('#form-busqueda-comercios').submit();
                        break;
                    }
                }
            });

            // Filtros por Categoría
            $('.btn-filtro-categoria').on('click', function() {
                const category = $(this).data('category');
                $('.btn-filtro-categoria').removeClass('active');
                $(this).addClass('active');
                $('#hidden_category').val(category);
            });
        });
    }

})(jQuery);
