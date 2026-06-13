(function( $ ) {
    "use strict";

    const containerId = '#listado_productos_servicios';

    if ($(containerId).length) {
        console.log('Script para Listado de Productos y Servicios cargado correctamente');

        // --- ESTADO DE LOS CARRUSELES ---
        const carruselState = {};

        /**
         * Maneja el truncamiento y expansión de la descripción del negocio.
         */
        const manejarExpansionDescripcion = () => {
            $('.negocio-detalle-desc-wrapper').each(function() {
                const $wrapper = $(this);
                const $desc = $wrapper.find('.negocio-detalle-desc');
                const $btn = $wrapper.find('.btn-leer-mas-desc');

                // Verificamos si el texto realmente se trunca
                setTimeout(() => {
                    const elemento = $desc[0];
                    if (elemento && elemento.scrollHeight > elemento.offsetHeight) {
                        $btn.show();
                    } else {
                        $btn.hide();
                    }
                }, 250);
            });
        };

        $(document).on('click', '.btn-leer-mas-desc', function() {
            const $btn = $(this);
            const $desc = $btn.siblings('.negocio-detalle-desc');
            $desc.addClass('expanded');
            $btn.hide();
        });

        /**
         * Inicializa el mapa de Google en modo solo lectura para mostrar la ubicación del negocio.
         */
        function initMapDetalle() {
            if (typeof google === 'undefined' || !window.datosUbicacionNegocio) return;

            const lat = parseFloat(window.datosUbicacionNegocio.lat);
            const lng = parseFloat(window.datosUbicacionNegocio.lng);
            const titulo = window.datosUbicacionNegocio.titulo || 'Negocio';

            if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) {
                console.warn('Coordenadas de ubicación no válidas');
                $('#mapa_detalle_negocio').hide();
                return;
            }

            const myLatLng = { lat: lat, lng: lng };
            const map = new google.maps.Map(document.getElementById("mapa_detalle_negocio"), {
                zoom: 15,
                center: myLatLng,
                disableDefaultUI: false,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                zoomControl: true,
            });

            new google.maps.Marker({
                position: myLatLng,
                map,
                title: titulo,
            });
        }

        /**
         * Obtiene el detalle de la actividad vía Ajax.
         */
        const obtenerDetalleActividad = (id) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: APP_URL + '/reda/negocios/experiencias/actividades/detalle/' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: (data) => resolve(data),
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
                            'message' : window.RedaAlojamientoJson["Error al recuperar detalle"] || 'Error al recuperar detalle',
                            'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                            'respuesta': respuestaServidor.respuesta || '',
                            'code': x.status !== 0 ? x.status : 504,
                        };
                        resolve(respuesta);
                    }
                });
            });
        };

        // --- LÓGICA DE CARRUSELES Y PAGINACIÓN AJAX ---

        /**
         * Carga más elementos para un carrusel específico.
         */
        const cargarMasActividades = ($carrusel) => {
            const id = $carrusel.attr('id');
            const state = carruselState[id];

            if (state.loading || state.noMore) return;

            state.loading = true;
            const $loader = $(`#loader_${state.tipo === 'promociones' ? 'promociones' : 'todos'}`);
            $loader.addClass('active');
            $carrusel.addClass('loading');

            $.ajax({
                url: APP_URL + `/reda/negocios/experiencias/actividades/paginadas/${state.idNegocio}`,
                type: 'GET',
                data: {
                    offset: state.offset,
                    tipo: state.tipo
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.cantidad > 0) {
                        $carrusel.append(response.html);
                        state.offset = response.proximo_offset;
                        
                        // --- TEMPORAL PARA PRUEBAS: CAMBIO DE 10 A 4 ---
                        if (response.cantidad < 4) {
                            state.noMore = true;
                        }
                        
                        // Actualizar botones después de cargar
                        actualizarBotonesCarrusel($carrusel);
                    } else {
                        state.noMore = true;
                    }
                },
                error: function() {
                    console.error('Error al cargar más actividades');
                },
                complete: function() {
                    state.loading = false;
                    $loader.removeClass('active');
                    $carrusel.removeClass('loading');
                }
            });
        };

        /**
         * Actualiza el estado de los botones prev/next.
         */
        const actualizarBotonesCarrusel = ($carrusel) => {
            const id = $carrusel.attr('id');
            const scrollLeft = Math.ceil($carrusel.scrollLeft());
            const scrollWidth = $carrusel[0].scrollWidth;
            const clientWidth = $carrusel[0].clientWidth;

            const $parent = $carrusel.closest('.seccion-productos');
            const $btnPrev = $parent.find('.btn-prev');
            const $btnNext = $parent.find('.btn-next');

            // Habilitar/Deshabilitar PREV
            $btnPrev.prop('disabled', scrollLeft <= 5);

            // Determinar si hay scroll posible
            const tieneScroll = scrollWidth > clientWidth + 10;
            
            // Umbral de final de scroll (tolerancia de 20px)
            const alFinal = scrollLeft + clientWidth >= scrollWidth - 20;
            
            // LOGICA AJAX: Solo si el carrusel es "potencialmente paginable"
            // --- TEMPORAL PARA PRUEBAS: CAMBIO DE 10 A 4 ---
            const esPaginable = carruselState[id].offset >= 4;

            if (alFinal && !carruselState[id].noMore && !carruselState[id].loading) {
                if (esPaginable) {
                    cargarMasActividades($carrusel);
                } else {
                    // Marcamos que no hay más para no volver a intentar
                    carruselState[id].noMore = true;
                }
            }

            // Habilitar/Deshabilitar NEXT
            const puedeHacerScrollNext = tieneScroll && !alFinal;
            const puedeCargarMas = esPaginable && !carruselState[id].noMore;
            
            $btnNext.prop('disabled', !puedeHacerScrollNext && !puedeCargarMas);
            
            // Ocultar controles si no hay nada que desplazar NI cargar (Evita botones "muertos")
            if (!tieneScroll && !puedeCargarMas) {
                $parent.find('.carrusel-controles-desktop').css('opacity', '0');
                $parent.find('.carrusel-controles-desktop').css('pointer-events', 'none');
            } else {
                $parent.find('.carrusel-controles-desktop').css('opacity', '1');
                $parent.find('.carrusel-controles-desktop').css('pointer-events', 'auto');
            }
        };

        /**
         * Inicializa el estado de un carrusel.
         */
        const initCarrusel = ($carrusel) => {
            const id = $carrusel.attr('id');
            carruselState[id] = {
                idNegocio: $carrusel.data('id-negocio'),
                tipo: $carrusel.data('tipo'),
                offset: parseInt($carrusel.data('offset')) || 0,
                loading: false,
                noMore: false
            };

            // Listener de Scroll para Móvil y Desktop
            $carrusel.on('scroll', function() {
                actualizarBotonesCarrusel($(this));
            });

            // Ajuste inicial de botones
            actualizarBotonesCarrusel($carrusel);
        };

        $(function() {
            // Inicializar el mapa
            initMapDetalle();
            
            // Inicializar expansión de descripción
            manejarExpansionDescripcion();

            // Inicializar todos los carruseles
            $('.container-carrusel-productos').each(function() {
                initCarrusel($(this));
            });

            // --- CLICK EN BOTONES DE CONTROL (DESKTOP) ---
            $(document).on('click', '.btn-carrusel-control', function() {
                const $btn = $(this);
                const $carrusel = $($btn.data('target'));
                const step = $carrusel[0].clientWidth * 0.8; // Desplazamiento del 80% de la vista

                if ($btn.hasClass('btn-next')) {
                    $carrusel.scrollLeft($carrusel.scrollLeft() + step);
                } else {
                    $carrusel.scrollLeft($carrusel.scrollLeft() - step);
                }
            });

            // Manejo de Filtros Desktop
            $('#filtro_tipo_actividad').on('change', function() {
                const tipo = $(this).val();
                $('#filtro_tipo_actividad_movil').val(tipo); // Sincronizar con móvil
                filtrarActividades(tipo);
            });

            // Manejo de Filtros Móvil (Modal)
            $('.btn-aplicar-filtro').on('click', function() {
                const tipo = $('#filtro_tipo_actividad_movil').val();
                $('#filtro_tipo_actividad').val(tipo); // Sincronizar con desktop
                filtrarActividades(tipo);
                $('#modalBusquedaActividades').modal('hide');
            });

            // --- CLICK EN PRODUCTO/SERVICIO PARA VER DETALLE ---
            $(document).on('click', '.producto-card', async function(e) {
                const id = $(this).data('id');
                if (!id) return;

                // Reset modal content with loader
                $('#bodyDetalleActividad').html(`
                    <div class="text-center p-5">
                        <i class="fa fa-spinner fa-spin fa-3x text-success"></i>
                    </div>
                `);
                
                $('#modalDetalleActividad').modal('show');

                const response = await obtenerDetalleActividad(id);

                if (response.success) {
                    $('#bodyDetalleActividad').html(response.respuesta.html);
                } else {
                    $('#bodyDetalleActividad').html(`
                        <div class="alert alert-danger m-4">
                            ${response.mensaje_usuario}
                        </div>
                    `);
                }
            });
        });

        /**
         * Filtra los productos/servicios en el frontend basándose en el tipo.
         * @param {string} tipo - 'producto', 'servicio' o '' (todos)
         */
        function filtrarActividades(tipo) {
            console.log('Filtrando por tipo:', tipo);

            $('.producto-card').each(function() {
                const tipoCard = $(this).data('tipo-actividad');

                if (tipo === '' || tipo === tipoCard) {
                    $(this).fadeIn(300);
                } else {
                    $(this).hide();
                }
            });

            // Si una sección de carrusel queda vacía, podríamos ocultar el título de la sección
            $('.seccion-productos').each(function() {
                const cardsVisibles = $(this).find('.producto-card:visible').length;
                if (cardsVisibles === 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        }
    }

})(jQuery);
