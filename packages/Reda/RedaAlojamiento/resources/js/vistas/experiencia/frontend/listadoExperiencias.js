/**
 * packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoExperiencias.js
 * Script para gestionar la interactividad de la vista de listado de negocios.
 */

(function( $ ) {
    "use strict";

    const containerId = '#listado_experiencias';

    if ($(containerId).length) {
        console.log('Script para Listado de Negocios cargado correctamente');

        $(function() {
            let modoBusqueda = 'ninguno'; // 'distancia' o 'ubicacion'

            // 1. Inicializar Google Places Autocomplete
            const inputUbicacion = document.getElementById('filtro_ubicacion');
            if (inputUbicacion) {
                const autocomplete = new google.maps.places.Autocomplete(inputUbicacion);
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    if (place.geometry) {
                        $('#filtro_lat').val(place.geometry.location.lat());
                        $('#filtro_lng').val(place.geometry.location.lng());

                        // Cambiar modo a ubicación
                        activarModoUbicacion();
                        ejecutarBusqueda();
                    }
                });
            }

            // 2. Manejar el rango de distancia
            $('#filtro_radio').on('input', function() {
                $('#radio_km_display').text($(this).val() + ' km');
            });

            $('#filtro_radio').on('change', function() {
                activarModoDistancia();
                ejecutarBusqueda();
            });

            // Detectar si el usuario escribe manualmente en ubicación
            $('#filtro_ubicacion').on('input', function() {
                if ($(this).val().length > 0 && modoBusqueda === 'distancia') {
                    activarModoUbicacion();
                }
            });

            // 3. Manejar cambio de categoría
            $('#filtro_categoria').on('change', function() {
                ejecutarBusqueda();
            });

            // 4. Manejar el envío del formulario (Enter o Botón)
            $('#form_busqueda_negocios').on('submit', function(e) {
                e.preventDefault();
                ejecutarBusqueda();
            });

            // 5. Botones de favoritos
            $(document).on('click', '.btn-favorito', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $icono = $(this).find('i');
                if ($icono.hasClass('far')) {
                    $icono.removeClass('far').addClass('fas text-danger');
                } else {
                    $icono.removeClass('fas text-danger').addClass('far');
                }
            });

            /**
             * Lógica de Exclusividad: Activar Modo Distancia
             */
            function activarModoDistancia() {
                if (modoBusqueda === 'ubicacion') {
                    // Notificar al usuario (opcional, según tu preferencia)
                    // window.mostrarNotificacion('Búsqueda por Distancia', 'Se ha priorizado la búsqueda por rango de KM. Se limpió la ubicación específica.', 'info');
                }

                modoBusqueda = 'distancia';
                $('#item_ubicacion').addClass('item-sombreado-visual');
                $('#item_radio').removeClass('item-sombreado-visual');

                // Limpiar datos de ubicación para evitar conflictos en el servidor
                $('#filtro_ubicacion').val('');
                $('#filtro_lat').val('');
                $('#filtro_lng').val('');
            }

            /**
             * Lógica de Exclusividad: Activar Modo Ubicación
             */
            function activarModoUbicacion() {
                if (modoBusqueda === 'distancia') {
                    window.mostrarNotificacion(
                        window.RedaAlojamientoJson["Búsqueda por Ubicación"] || 'Búsqueda por Ubicación',
                        window.RedaAlojamientoJson["Solo puede buscar por distancia o ubicación. Se ha restablecido el rango de distancia."] || 'Solo puede buscar por distancia o ubicación. Se ha restablecido el rango de distancia.',
                        'info'
                    );
                }

                modoBusqueda = 'ubicacion';
                $('#item_radio').addClass('item-sombreado-visual');
                $('#item_ubicacion').removeClass('item-sombreado-visual');

                // Restablecer slider a posición original (25km)
                $('#filtro_radio').val(25);
                $('#radio_km_display').text('25 km');
            }

            /**
             * Orquestador de la búsqueda
             */
            async function ejecutarBusqueda() {
                const formData = $('#form_busqueda_negocios').serialize();
                const $contenedorDestacados = $('#contenedor_destacados');
                const $contenedorGeneral = $('#contenedor_listado_general');
                const $contenedorPaginacion = $('#contenedor_paginacion');

                // Estado visual de carga
                $contenedorDestacados.css('opacity', '0.5');
                $contenedorGeneral.css('opacity', '0.5');

                const respuestaBusqueda = await obtenerNegocios(formData);

                if (respuestaBusqueda.success) {
                    const data = respuestaBusqueda.respuesta;
                    $contenedorDestacados.html(data.html_destacados).css('opacity', '1');
                    $contenedorGeneral.html(data.html_general).css('opacity', '1');
                    $contenedorPaginacion.html(data.html_paginacion);
                } else {
                    console.error(respuestaBusqueda.message);
                    $contenedorDestacados.css('opacity', '1');
                    $contenedorGeneral.css('opacity', '1');
                }
            }
        });
    }

    /**
     * Función llamada para obtener negocios vía AJAX (Estructura Estándar)
     */
    const obtenerNegocios = (filtros) => {
        return new Promise((resolve) => {
            $.ajax({
                url: window.location.pathname,
                type: 'GET',
                data: filtros,
                dataType: 'json',
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
                        'message' : window.RedaAlojamientoJson["Error buscando negocios"] || 'Error buscando negocios',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                        'respuesta': respuestaServidor.respuesta || '',
                        'code': x.status !== 0 ? x.status : 504,
                    };
                    resolve(respuesta);
                }
            });
        });
    }

})(jQuery);
