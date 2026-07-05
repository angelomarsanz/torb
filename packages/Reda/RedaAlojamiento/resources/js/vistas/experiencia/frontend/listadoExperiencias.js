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
                    appendTo: "#modalBusquedaComercios",
                    select: function(event, ui) {
                        setTimeout(() => {
                            ejecutarBusqueda($('#form_busqueda_negocios_modal'));
                        }, 100);
                    }
                });
            }

            // Inicializar Autocomplete para nombres de productos
            if (window.nombresProductos && $('#input_nombre_producto').length) {
                $('#input_nombre_producto').autocomplete({
                    source: window.nombresProductos,
                    minLength: 1,
                    appendTo: "#modalBusquedaComercios",
                    select: function(event, ui) {
                        const seleccion = ui.item.value;
                        $('#modalBusquedaComercios').modal('hide');
                        window.location.href = APP_URL + '/reda/negocios/productos-servicios-encontrados?q=' + encodeURIComponent(seleccion) + '&tipo=producto';
                    }
                });
            }

            // Inicializar Autocomplete para nombres de servicios
            if (window.nombresServicios && $('#input_nombre_servicio').length) {
                $('#input_nombre_servicio').autocomplete({
                    source: window.nombresServicios,
                    minLength: 1,
                    appendTo: "#modalBusquedaComercios",
                    select: function(event, ui) {
                        const seleccion = ui.item.value;
                        $('#modalBusquedaComercios').modal('hide');
                        window.location.href = APP_URL + '/reda/negocios/productos-servicios-encontrados?q=' + encodeURIComponent(seleccion) + '&tipo=servicio';
                    }
                });
            }

            // Inicializar Autocomplete para ubicaciones existentes en BD
            if (window.listaUbicaciones && $('.filtro-ubicacion').length) {
                $('.filtro-ubicacion').autocomplete({
                    source: window.listaUbicaciones,
                    minLength: 1,
                    appendTo: "#modalBusquedaComercios",
                    select: function(event, ui) {
                        const $parentForm = $(this).closest('form');
                        // Al seleccionar de la lista local, no tenemos lat/lng nuevos,
                        // la búsqueda en servidor usará el texto 'ubicacion_texto'
                        $parentForm.find('.filtro-lat').val('');
                        $parentForm.find('.filtro-lng').val('');
                        
                        setTimeout(() => {
                            ejecutarBusqueda($parentForm);
                        }, 100);
                    }
                });
            }

            // --- EXCLUSIVIDAD DE INPUTS DE BÚSQUEDA ---
            const $inputComercio = $('#input_nombre_comercio');
            const $inputProducto = $('#input_nombre_producto');
            const $inputServicio = $('#input_nombre_servicio');
            const $selectCategoria = $('.filtro-categoria');
            const $inputUbicacion = $('.filtro-ubicacion');
            const $latInput = $('.filtro-lat');
            const $lngInput = $('.filtro-lng');

            $inputComercio.on('input', function() {
                if ($(this).val().length > 0) {
                    // Limpiar otros textos
                    $inputProducto.val('');
                    $inputServicio.val('');
                    
                    // Limpiar Categoría y Ubicación (Prioridad absoluta al Nombre del Comercio)
                    $selectCategoria.val('');
                    $inputUbicacion.val('');
                    $latInput.val('');
                    $lngInput.val('');
                }
            });

            $inputProducto.on('input', function() {
                if ($(this).val().length > 0) {
                    $inputComercio.val('');
                    $inputServicio.val('');
                }
            });

            $inputServicio.on('input', function() {
                if ($(this).val().length > 0) {
                    $inputComercio.val('');
                    $inputProducto.val('');
                }
            });

            // Exclusividad para Ubicación: Reiniciar otros filtros al escribir
            $inputUbicacion.on('input', function() {
                if ($(this).val().length > 0) {
                    $inputComercio.val('');
                    $inputProducto.val('');
                    $inputServicio.val('');
                    $selectCategoria.val('');
                    // El slider se resetea vía activarModoUbicacion al disparar búsqueda
                }
            });

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

                        // Limpiar otros filtros (Prioridad absoluta a la Ubicación seleccionada)
                        $inputComercio.val('');
                        $inputProducto.val('');
                        $inputServicio.val('');
                        $selectCategoria.val('');

                        // Cambiar modo a ubicación
                        activarModoUbicacion($parentForm);
                        ejecutarBusqueda($parentForm);
                    }
                });
            });

            // 2. Manejar el rango de distancia (Sync displays)
            $(document).on('input', '.filtro-radio', function() {
                const valor = $(this).val();
                const etiquetaKm = window.RedaAlojamientoJson['km'] || 'km';
                $('.radio-km-display').text(valor + ' ' + etiquetaKm);
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
                
                // Si el usuario escribió algo en productos o servicios pero no seleccionó del autocomplete,
                // redirigimos a la vista de encontrados si esos inputs tienen valor.
                const valProducto = $('#input_nombre_producto').val();
                const valServicio = $('#input_nombre_servicio').val();

                if (valProducto) {
                    $('#modalBusquedaComercios').modal('hide');
                    window.location.href = APP_URL + '/reda/negocios/productos-servicios-encontrados?q=' + encodeURIComponent(valProducto) + '&tipo=producto';
                    return;
                }
                if (valServicio) {
                    $('#modalBusquedaComercios').modal('hide');
                    window.location.href = APP_URL + '/reda/negocios/productos-servicios-encontrados?q=' + encodeURIComponent(valServicio) + '&tipo=servicio';
                    return;
                }

                ejecutarBusqueda($(this));
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
                if (scroll > 10) {
                    $('#search_sticky_bar').addClass('is-sticky');
                } else {
                    $('#search_sticky_bar').removeClass('is-sticky');
                }
            });

            // --- CAPTURA DE UBICACIÓN REAL AL ABRIR MODAL ---
            $('#modalBusquedaComercios').on('show.bs.modal', function () {
                const $form = $('#form_busqueda_negocios_modal');
                const $latInput = $form.find('.filtro-lat');
                const $lngInput = $form.find('.filtro-lng');
                const $radioDisplay = $form.find('.radio-km-display');

                // Si no hay coordenadas fijadas manualmente por búsqueda de texto
                // intentamos obtener la ubicación actual por GPS para el slider de distancia
                if (!$latInput.val() && navigator.geolocation) {
                    const originalText = $radioDisplay.html();
                    $radioDisplay.html('<i class="fas fa-spinner fa-spin mr-1"></i>' + (window.RedaAlojamientoJson['Ubicando...'] || 'Ubicando...'));

                    navigator.geolocation.getCurrentPosition(function(position) {
                        $latInput.val(position.coords.latitude);
                        $lngInput.val(position.coords.longitude);
                        $radioDisplay.html(originalText);
                        console.log('Ubicación capturada por GPS:', position.coords.latitude, position.coords.longitude);
                    }, function(error) {
                        $radioDisplay.html(originalText);
                        console.warn('Error al obtener ubicación GPS:', error.message);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    });
                }
            });

            /**
             * Lógica de Exclusividad: Activar Modo Distancia
             */
            function activarModoDistancia($form) {
                modoBusqueda = 'distancia';
                // No limpiamos lat/lng aquí para que use el GPS capturado al abrir el modal
                // pero sí limpiamos el texto de ubicación para evitar confusiones
                $form.find('.filtro-ubicacion').val('');
            }

            /**
             * Lógica de Exclusividad: Activar Modo Ubicación
             */
            function activarModoUbicacion($form) {
                modoBusqueda = 'ubicacion';
                // Restablecer slider a posición original (25km)
                $form.find('.filtro-radio').val(25);
                const etiquetaKm = window.RedaAlojamientoJson['km'] || 'km';
                $form.find('.radio-km-display').text('25 ' + etiquetaKm);
            }

            /**
             * Orquestador de la búsqueda
             */
            async function ejecutarBusqueda($form) {
                $('#modalBusquedaComercios').modal('hide');

                const formData = $form.serialize();
                const $contenedorDestacados = $('#contenedor_destacados');
                const $contenedorGeneral = $('#contenedor_listado_general');
                const $seccionDestacados = $('#seccion_destacados');

                // Mostrar animación de espera global
                window.RedaNotificaciones.esperar();

                // Estado visual de carga local (CSS-based)
                $contenedorDestacados.addClass('is-loading-ajax');
                $contenedorGeneral.addClass('is-loading-ajax');

                const respuestaBusqueda = await obtenerComercios(formData);

                // Ocultar animación de espera global
                window.RedaNotificaciones.ocultar();

                if (respuestaBusqueda.success) {
                    const data = respuestaBusqueda.respuesta;
                    
                    // Actualizar mensaje de conteo de resultados
                    const $contenedorMensaje = $('#contenedor_mensaje_resultados');
                    const $cantidadResultados = $('#cantidad_resultados_busqueda');
                    const $textoResultados = $('#texto_resultados_busqueda');

                    if (data.total > 0) {
                        $cantidadResultados.text(data.total).removeClass('d-none');
                        $textoResultados.removeClass('text-danger');
                        
                        // Lógica sutil de pluralización basada en el key de es.json
                        const rawString = window.RedaAlojamientoJson['comercio encontrado|comercios encontrados'] || 'comercio encontrado|comercios encontrados';
                        const parts = rawString.split('|');
                        const textoPlural = data.total === 1 ? (parts[0] || 'comercio encontrado') : (parts[1] || 'comercios encontrados');
                        
                        $textoResultados.text(textoPlural);
                    } else {
                        $cantidadResultados.text('0').addClass('d-none');
                        $textoResultados.text(window.RedaAlojamientoJson['No se encontraron comercios'] || 'No se encontraron comercios').addClass('text-danger');
                    }
                    
                    $contenedorMensaje.removeClass('d-none');

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
                    
                    // Notificar error al usuario
                    window.RedaNotificaciones.notificar(
                        window.RedaAlojamientoJson['Error'] || 'Error',
                        respuestaBusqueda.mensaje_usuario,
                        'error'
                    );
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
                        'code' : x.status !== 0 ? x.status : 504,
                    };
                    resolve(respuesta);
                }
            });
        });
    }

})(jQuery);
