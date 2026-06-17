/**
 * packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoExperiencias.js
 * Script para gestionar la interactividad de la vista de listado de comercios.
 */

(function( $ ) {
    "use strict";

    const containerId = '#listado_experiencias';

    if ($(containerId).length) {
        console.log('Script para Listado de Comercios cargado correctamente');

        $(function() {
            let modoBusqueda = 'ninguno'; // 'distancia' o 'ubicacion'

            // 1. Inicializar Google Places Autocomplete para ambos inputs (Desktop y Modal)
            const inputsUbicacion = document.querySelectorAll('.filtro-ubicacion');
            inputsUbicacion.forEach(input => {
                const autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    if (place.geometry) {
                        const $parentForm = $(input).closest('form');
                        $parentForm.find('.filtro-lat').val(place.geometry.location.lat());
                        $parentForm.find('.filtro-lng').val(place.geometry.location.lng());

                        // Cambiar modo a ubicación
                        activarModoUbicacion($parentForm);
                        ejecutarBusqueda($parentForm);
                        
                        // Si estamos en móvil, cerrar el modal tras seleccionar
                        if ($('#modalBusquedaComercios').hasClass('show')) {
                            $('#modalBusquedaComercios').modal('hide');
                        }
                    }
                });
            });

            // 2. Manejar el rango de distancia (Sync displays)
            $(document).on('input', '.filtro-radio', function() {
                const valor = $(this).val();
                $('.radio-km-display').text(valor + ' km');
                // Sincronizar el otro slider si existe
                $('.filtro-radio').not(this).val(valor);
            });

            $(document).on('change', '.filtro-radio', function() {
                const $parentForm = $(this).closest('form');
                activarModoDistancia($parentForm);
                ejecutarBusqueda($parentForm);
            });

            // Detectar si el usuario escribe manualmente en ubicación
            $(document).on('input', '.filtro-ubicacion', function() {
                const $parentForm = $(this).closest('form');
                if ($(this).val().length > 0 && modoBusqueda === 'distancia') {
                    activarModoUbicacion($parentForm);
                }
            });

            // 3. Manejar cambio de categoría
            $(document).on('change', '.filtro-categoria', function() {
                const $parentForm = $(this).closest('form');
                // Sincronizar el otro select
                $('.filtro-categoria').not(this).val($(this).val());
                ejecutarBusqueda($parentForm);
            });

            // 4. Manejar el envío del formulario (Enter o Botón)
            $(document).on('submit', '.form-busqueda-comercios', function(e) {
                e.preventDefault();
                ejecutarBusqueda($(this));
                
                // Si es el modal, cerrarlo
                if ($(this).attr('id') === 'form_busqueda_negocios_movil') {
                    $('#modalBusquedaComercios').modal('hide');
                }
            });

            // 5. Botones de favoritos
            $(document).on('click', '.btn-favorito', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $icono = $(this).find('i');
                if ($icono.hasClass('far')) {
                    $icono.removeClass('far').addClass('fas text-success');
                } else {
                    $icono.removeClass('fas text-success').addClass('far');
                }
            });

            // 6. Efecto de barra de búsqueda flotante (Sticky Shadow)
            $(window).on('scroll', function() {
                const scroll = $(window).scrollTop();
                if (scroll > 40) {
                    $('.seccion-filtros, .seccion-filtros-movil').addClass('is-sticky');
                } else {
                    $('.seccion-filtros, .seccion-filtros-movil').removeClass('is-sticky');
                }
            });

            /**
             * Lógica de Exclusividad: Activar Modo Distancia
             */
            function activarModoDistancia($form) {
                modoBusqueda = 'distancia';
                
                // Sombreado visual solo en desktop
                $('#item_ubicacion').addClass('item-sombreado-visual');
                $('#item_radio').removeClass('item-sombreado-visual');

                // Limpiar datos de ubicación en todos los forms para consistencia
                $('.filtro-ubicacion').val('');
                $('.filtro-lat').val('');
                $('.filtro-lng').val('');
            }

            /**
             * Lógica de Exclusividad: Activar Modo Ubicación
             */
            function activarModoUbicacion($form) {
                modoBusqueda = 'ubicacion';
                
                $('#item_radio').addClass('item-sombreado-visual');
                $('#item_ubicacion').removeClass('item-sombreado-visual');

                // Restablecer slider a posición original (25km)
                $('.filtro-radio').val(25);
                $('.radio-km-display').text('25 km');
            }

            /**
             * Orquestador de la búsqueda
             */
            async function ejecutarBusqueda($form) {
                const formData = $form.serialize();
                const $contenedorDestacados = $('#contenedor_destacados');
                const $contenedorGeneral = $('#contenedor_listado_general');
                const $contenedorPaginacion = $('#contenedor_paginacion');

                // Estado visual de carga
                $contenedorDestacados.css('opacity', '0.5');
                $contenedorGeneral.css('opacity', '0.5');

                const respuestaBusqueda = await obtenerComercios(formData);

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
     * Función llamada para obtener comercios vía AJAX (Estructura Estándar)
     */
    const obtenerComercios = (filtros) => {
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
