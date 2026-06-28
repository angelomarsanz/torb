/**
 * packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/frontend/listadoExperiencias.js
 * Script para gestionar la interactividad de la vista de listado de comercios.
 */

import { ListadoInfinito } from '../../../general/utilidades/listadoInfinito.js';

(function( $ ) {
    "use strict";

    const containerId = '#listado_experiencias';

    if ($(containerId).length) {
        console.log(window.RedaAlojamientoJson['Script para Listado de Comercios cargado correctamente'] || 'Script para Listado de Comercios cargado correctamente');

        // --- LÓGICA DE CARRUSELES ---

        const actualizarBotonesCarrusel = ($carrusel) => {
            const scrollLeft = Math.ceil($carrusel.scrollLeft());
            const scrollWidth = $carrusel[0].scrollWidth;
            const clientWidth = $carrusel[0].clientWidth;

            const $parent = $carrusel.closest('.seccion-productos');
            const $btnPrev = $parent.find('.btn-prev');
            const $btnNext = $parent.find('.btn-next');

            const alFinal = scrollLeft + clientWidth >= scrollWidth - 15;
            const alInicio = scrollLeft <= 10;

            $btnPrev.prop('disabled', alInicio);
            $btnNext.prop('disabled', alFinal);
        };

        const initCarrusel = ($carrusel) => {
            $carrusel.on('scroll', function() {
                actualizarBotonesCarrusel($(this));
            });
            actualizarBotonesCarrusel($carrusel);
        };

        $(function() {
            let modoBusqueda = 'ninguno'; // 'distancia' o 'ubicacion'

            // Inicializar carruseles
            $('.container-carrusel-productos').each(function() {
                initCarrusel($(this));
            });

            // Inicializar Autocomplete para nombres de comercios
            if (window.nombresComercios && $('#input_nombre_comercio').length) {
                $('#input_nombre_comercio').autocomplete({
                    source: window.nombresComercios,
                    minLength: 1,
                    select: function(event, ui) {
                        // Al seleccionar, podemos disparar la búsqueda automáticamente
                        setTimeout(() => {
                            ejecutarBusqueda($('#form_busqueda_negocios_modal'));
                        }, 100);
                    }
                });
            }

            // Manejo de Clics en Desktop
            $(document).on('click', '.btn-carrusel-control', function() {
                const $btn = $(this);
                const $carrusel = $($btn.data('target'));
                const scrollLeft = $carrusel.scrollLeft();
                const clientWidth = $carrusel[0].clientWidth;
                const step = clientWidth * 0.8;

                if ($btn.hasClass('btn-next')) {
                    $carrusel.animate({ scrollLeft: scrollLeft + step }, 400);
                } else {
                    $carrusel.animate({ scrollLeft: scrollLeft - step }, 400);
                }
            });

            // Interacción con "Ver todos" (Scroll Infinito)
            $(document).on('click', '.card-ver-todos', function() {
                const $card = $(this);
                
                // Capturar filtros actuales para el listado infinito
                const $form = $('#form_busqueda_negocios_modal');
                const extraData = {};
                if ($form.length) {
                    $form.serializeArray().forEach(item => {
                        extraData[item.name] = item.value;
                    });
                }

                const options = {
                    tipo: $card.data('tipo'),
                    tituloModal: $card.data('titulo-modal'),
                    urlBase: APP_URL + '/reda/negocios/listado-negocios/paginados',
                    extraData: extraData
                };
                
                ListadoInfinito.iniciar(options);
            });

            // 1. Inicializar Google Places Autocomplete para el input de ubicación
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
                    }
                });
            });

            // 2. Manejar el rango de distancia (Sync displays)
            $(document).on('input', '.filtro-radio', function() {
                const valor = $(this).val();
                $('.radio-km-display').text(valor + ' km');
            });

            $(document).on('change', '.filtro-radio', function() {
                const $parentForm = $(this).closest('form');
                activarModoDistancia($parentForm);
                ejecutarBusqueda($parentForm);
            });

            // 3. Manejar cambio de categoría
            $(document).on('change', '.filtro-categoria', function() {
                const $parentForm = $(this).closest('form');
                ejecutarBusqueda($parentForm);
            });

            // 4. Manejar el envío del formulario (Enter o Botón)
            $(document).on('submit', '.form-busqueda-comercios', function(e) {
                e.preventDefault();
                ejecutarBusqueda($(this));
                
                // Opcional: cerrar el modal al buscar (si el usuario así lo prefiere)
                // $('#modalBusquedaComercios').modal('hide');
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
                    $('.seccion-filtros-desktop, .seccion-filtros-movil').addClass('is-sticky');
                } else {
                    $('.seccion-filtros-desktop, .seccion-filtros-movil').removeClass('is-sticky');
                }
            });

            /**
             * Lógica de Exclusividad: Activar Modo Distancia
             */
            function activarModoDistancia($form) {
                modoBusqueda = 'distancia';
                // Limpiar datos de ubicación en el form para consistencia
                $form.find('.filtro-ubicacion').val('');
                $form.find('.filtro-lat').val('');
                $form.find('.filtro-lng').val('');
            }

            /**
             * Lógica de Exclusividad: Activar Modo Ubicación
             */
            function activarModoUbicacion($form) {
                modoBusqueda = 'ubicacion';
                // Restablecer slider a posición original (25km)
                $form.find('.filtro-radio').val(25);
                $form.find('.radio-km-display').text('25 km');
            }

            /**
             * Orquestador de la búsqueda
             */
            async function ejecutarBusqueda($form) {
                const formData = $form.serialize();
                const $contenedorDestacados = $('#contenedor_destacados');
                const $contenedorGeneral = $('#contenedor_listado_general');
                const $seccionDestacados = $('#seccion_destacados');

                // Estado visual de carga (CSS-based)
                $contenedorDestacados.addClass('is-loading-ajax');
                $contenedorGeneral.addClass('is-loading-ajax');

                const respuestaBusqueda = await obtenerComercios(formData);

                if (respuestaBusqueda.success) {
                    const data = respuestaBusqueda.respuesta;
                    
                    // Actualizar visibilidad y contenido de destacados
                    if (data.total_destacados > 0) {
                        $seccionDestacados.show();
                        $contenedorDestacados.html(data.html_destacados);
                    } else {
                        $seccionDestacados.hide();
                    }

                    // Actualizar contenido general y quitar estado de carga
                    $contenedorGeneral.html(data.html_general).removeClass('is-loading-ajax');
                    $contenedorDestacados.removeClass('is-loading-ajax');
                    
                    // Re-inicializar comportamientos de carrusel tras actualización AJAX
                    $('.container-carrusel-productos').each(function() {
                        actualizarBotonesCarrusel($(this));
                    });

                } else {
                    console.error(respuestaBusqueda.message);
                    $contenedorDestacados.removeClass('is-loading-ajax');
                    $contenedorGeneral.removeClass('is-loading-ajax');
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
