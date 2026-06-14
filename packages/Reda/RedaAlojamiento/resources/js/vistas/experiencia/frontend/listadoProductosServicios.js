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
         * Inicializa el mapa de Google.
         */
        function initMapDetalle() {
            if (typeof google === 'undefined' || !window.datosUbicacionNegocio) return;
            const lat = parseFloat(window.datosUbicacionNegocio.lat);
            const lng = parseFloat(window.datosUbicacionNegocio.lng);
            const titulo = window.datosUbicacionNegocio.titulo || 'Negocio';
            if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) return;
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
            new google.maps.Marker({ position: myLatLng, map, title: titulo });
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
                    error: function () {
                        resolve({ success: false, mensaje_usuario: 'Error en el servidor' });
                    }
                });
            });
        };

        // --- LÓGICA DE CARRUSELES Y PAGINACIÓN AJAX ---

        /**
         * Carga actividades y REEMPLAZA el contenido actual.
         */
        const cargarActividades = ($carrusel, direccion = 'next') => {
            const id = $carrusel.attr('id');
            const state = carruselState[id];
            if (state.loading) return;
            
            let nuevoOffset = state.offset;
            if (direccion === 'next') {
                if (state.noMore) return;
            } else {
                const cantidadActual = $carrusel.find('.producto-card').length;
                nuevoOffset = Math.max(0, state.offset - cantidadActual - 10);
                if (state.offset <= 10) return;
            }

            state.loading = true;
            const $loader = $(`#loader_${state.tipo === 'promociones' ? 'promociones' : 'todos'}`);
            $loader.addClass('active');
            $carrusel.addClass('loading');

            $.ajax({
                url: APP_URL + `/reda/negocios/experiencias/actividades/paginadas/${state.idNegocio}`,
                type: 'GET',
                data: { offset: (direccion === 'next' ? state.offset : nuevoOffset), tipo: state.tipo },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.cantidad > 0) {
                        // LIMPIEZA Y REEMPLAZO: Asegura que solo se vean 10 a la vez
                        $carrusel.empty().append(response.html);
                        $carrusel.scrollLeft(0);
                        
                        state.offset = response.proximo_offset;
                        state.noMore = (direccion === 'next' && response.cantidad < 10);
                        
                        // Resetear estados para forzar 2 clics de nuevo al llegar al borde
                        actualizarBotonesCarrusel($carrusel);
                    } else if (direccion === 'next') {
                        state.noMore = true;
                    }
                },
                complete: function() {
                    state.loading = false;
                    $loader.removeClass('active');
                    $carrusel.removeClass('loading');
                }
            });
        };

        /**
         * Actualiza el estado de los botones.
         */
        const actualizarBotonesCarrusel = ($carrusel) => {
            const id = $carrusel.attr('id');
            const state = carruselState[id];
            if (!state) return;

            const scrollLeft = Math.ceil($carrusel.scrollLeft());
            const scrollWidth = $carrusel[0].scrollWidth;
            const clientWidth = $carrusel[0].clientWidth;

            const $parent = $carrusel.closest('.seccion-productos');
            const $btnPrev = $parent.find('.btn-prev');
            const $btnNext = $parent.find('.btn-next');

            const tieneScroll = scrollWidth > clientWidth + 10;
            const alFinal = scrollLeft + clientWidth >= scrollWidth - 30;
            const alInicio = scrollLeft <= 15;
            
            const esPaginableAdelante = state.offset >= 10 && !state.noMore;
            const esPaginableAtras = state.offset > ($carrusel.find('.producto-card').length);

            // Botones habilitados si no están en el borde O si el borde es "paginable"
            $btnPrev.prop('disabled', alInicio && !esPaginableAtras);
            $btnNext.prop('disabled', alFinal && !esPaginableAdelante);
            
            if (!tieneScroll && !esPaginableAdelante && !esPaginableAtras) {
                $parent.find('.carrusel-controles-desktop').css('opacity', '0').css('pointer-events', 'none');
            } else {
                $parent.find('.carrusel-controles-desktop').css('opacity', '1').css('pointer-events', 'auto');
            }
        };

        const initCarrusel = ($carrusel) => {
            const id = $carrusel.attr('id');
            carruselState[id] = {
                idNegocio: $carrusel.data('id-negocio'),
                tipo: $carrusel.data('tipo'),
                offset: parseInt($carrusel.data('offset')) || 0,
                loading: false,
                noMore: false
            };

            $carrusel.on('scroll', function() {
                actualizarBotonesCarrusel($(this));
            });

            // Swipe Móvil Robusto
            let touchStartX = 0;
            $carrusel.on('touchstart', function(e) {
                touchStartX = e.originalEvent.touches[0].pageX;
            });

            $carrusel.on('touchmove', function(e) {
                const touchCurrentX = e.originalEvent.touches[0].pageX;
                const diffX = touchStartX - touchCurrentX;
                const scrollLeft = $(this).scrollLeft();
                const scrollWidth = $(this)[0].scrollWidth;
                const clientWidth = $(this)[0].clientWidth;

                // Adelante (swipe izquierda)
                if (diffX > 40 && (scrollLeft + clientWidth >= scrollWidth - 15) && !carruselState[id].loading) {
                    if (carruselState[id].offset >= 10 && !carruselState[id].noMore) {
                        cargarActividades($(this), 'next');
                    }
                }
                
                // Atrás (swipe derecha)
                if (diffX < -40 && scrollLeft <= 15 && !carruselState[id].loading) {
                    if (carruselState[id].offset > $(this).find('.producto-card').length) {
                        cargarActividades($(this), 'prev');
                    }
                }
            });

            actualizarBotonesCarrusel($carrusel);
        };

        $(function() {
            initMapDetalle();
            manejarExpansionDescripcion();
            $('.container-carrusel-productos').each(function() { initCarrusel($(this)); });

            // Manejo de Clics en Desktop
            $(document).on('click', '.btn-carrusel-control', function() {
                const $btn = $(this);
                const $carrusel = $($btn.data('target'));
                const id = $carrusel.attr('id');
                const state = carruselState[id];
                if (!state || state.loading) return;

                const scrollLeft = $carrusel.scrollLeft();
                const scrollWidth = $carrusel[0].scrollWidth;
                const clientWidth = $carrusel[0].clientWidth;
                const step = clientWidth * 0.8;

                if ($btn.hasClass('btn-next')) {
                    const alFinal = scrollLeft + clientWidth >= scrollWidth - 30;
                    const esPaginable = state.offset >= 10 && !state.noMore;

                    if (alFinal && esPaginable) {
                        cargarActividades($carrusel, 'next');
                    } else {
                        $carrusel.animate({ scrollLeft: scrollLeft + step }, 400);
                    }
                } else {
                    const alInicio = scrollLeft <= 20;
                    const esPaginableAtras = state.offset > ($carrusel.find('.producto-card').length);

                    if (alInicio && esPaginableAtras) {
                        cargarActividades($carrusel, 'prev');
                    } else {
                        $carrusel.animate({ scrollLeft: scrollLeft - step }, 400);
                    }
                }
            });

            // Filtros y Detalle
            $('#filtro_tipo_actividad').on('change', function() { filtrarActividades($(this).val()); });
            $(document).on('click', '.producto-card', async function() {
                const id = $(this).data('id');
                if (!id) return;
                $('#bodyDetalleActividad').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x text-success"></i></div>');
                $('#modalDetalleActividad').modal('show');
                const res = await obtenerDetalleActividad(id);
                if (res.success) $('#bodyDetalleActividad').html(res.respuesta.html);
                else $('#bodyDetalleActividad').html(`<div class="alert alert-danger m-4">Error al cargar</div>`);
            });
        });

        function filtrarActividades(tipo) {
            $('.producto-card').each(function() {
                const tc = $(this).data('tipo-actividad');
                if (tipo === '' || tipo === tc) $(this).fadeIn(300); else $(this).hide();
            });
            $('.seccion-productos').each(function() {
                $(this).toggle($(this).find('.producto-card:visible').length > 0);
            });
        }
    }
})(jQuery);
