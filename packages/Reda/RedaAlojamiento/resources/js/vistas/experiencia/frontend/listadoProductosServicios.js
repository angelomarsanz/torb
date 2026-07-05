import { ListadoInfinito } from '../../../general/utilidades/listadoInfinito.js';

(function( $ ) {
    "use strict";

    const containerId = '#listado_productos_servicios';

    if ($(containerId).length) {
        console.log(window.RedaAlojamientoJson["Script para Listado de Productos y Servicios cargado correctamente"] || 'Script para Listado de Productos y Servicios cargado correctamente');

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
                }, 300);
            });
        };

        $(document).on('click', '.btn-leer-mas-desc', function() {
            const $btn = $(this);
            const $desc = $btn.siblings('.negocio-detalle-desc');
            $desc.addClass('expanded');
            $btn.hide();
        });

        /**
         * Maneja el truncamiento inicial de las reseñas para mostrar el botón "Más".
         */
        const manejarTruncamientoReseñas = () => {
            $('.reseña-comentario-wrapper').each(function() {
                const $wrapper = $(this);
                const $comentario = $wrapper.find('.reseña-comentario');
                const $btn = $wrapper.find('.btn-leer-mas-reseña');

                setTimeout(() => {
                    const elemento = $comentario[0];
                    if (elemento && elemento.scrollHeight > elemento.offsetHeight) {
                        $btn.show();
                    } else {
                        $btn.hide();
                    }
                }, 300);
            });
        };

        /**
         * Abre el modal con el comentario completo de la reseña.
         */
        $(document).on('click', '.btn-leer-mas-reseña', function(e) {
            e.stopPropagation();
            const $btn = $(this);
            const $card = $btn.closest('.reseña-card');
            const $header = $card.find('.d-flex.align-items-center').first().clone();
            const $stars = $card.find('.star-rating').clone();
            const comentarioCompleto = $card.find('.reseña-comentario').text().trim();

            // Limpiar y poblar modal
            const $modalHeader = $('#headerDetalleReseña');
            $modalHeader.empty().append($header);
            $modalHeader.find('.img-profile-list').removeClass('mr-3').addClass('mr-3'); // Asegurar margen
            $modalHeader.append('<div class="ml-auto"></div>').find('.ml-auto').append($stars);
            
            $('#textoDetalleReseña').html(comentarioCompleto.replace(/\n/g, '<br>'));
            
            $('#modalDetalleReseña').modal('show');
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
                            'message' : window.RedaAlojamientoJson["Error obteniendo detalle"] || 'Error obteniendo detalle',
                            'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                            'respuesta': respuestaServidor.respuesta || '',
                            'code': x.status !== 0 ? x.status : 504,
                        };
                        resolve(respuesta);
                    }
                });
            });
        };

        // --- LÓGICA DE CARRUSELES (SIMPLIFICADA) ---

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
            initMapDetalle();
            manejarExpansionDescripcion();
            manejarTruncamientoReseñas();
            window.addEventListener('load', () => {
                manejarExpansionDescripcion();
                manejarTruncamientoReseñas();
                // Nota: Ya no abrimos el modal automáticamente porque se muestra inline
            });

            $('.container-carrusel-productos').each(function() { initCarrusel($(this)); });

            // Manejo de Clics en Desktop (Navegación pura, sin AJAX)
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

            // --- INTERACCIÓN CON "VER TODOS" (SCROLL INFINITO) ---
            $(document).on('click', '.card-ver-todos', function() {
                const $card = $(this);
                const tipo = $card.data('tipo');
                
                // Capturar filtros activos para el listado infinito
                const extraData = {};
                if (tipo !== 'reseñas') {
                    const prod = $('#buscar_producto_modal').val();
                    const serv = $('#buscar_servicio_modal').val();
                    const tipoAct = $('#filtro_tipo_modal').val();
                    
                    if (prod) {
                        extraData['tipo_actividad'] = 'producto';
                        extraData['q'] = prod;
                    } else if (serv) {
                        extraData['tipo_actividad'] = 'servicio';
                        extraData['q'] = serv;
                    } else if (tipoAct) {
                        extraData['tipo_actividad'] = tipoAct;
                    }
                }

                let options = {
                    idNegocio: $card.data('id-negocio'),
                    tipo: tipo,
                    tituloModal: $card.data('titulo-modal'),
                    urlBase: APP_URL + `/reda/negocios/experiencias/actividades/paginadas/${$card.data('id-negocio')}`,
                    extraData: extraData
                };

                // Si son reseñas, ajustar tamaño y estilos
                if (tipo === 'reseñas') {
                    options.dialogClass = 'modal-lg';
                    options.contentClass = 'modal-content-infinito-reseñas';
                }
                
                ListadoInfinito.iniciar(options);
            });

            // Lógica de Exclusión Mutua en el Modal
            $('#buscar_producto_modal').on('input', function() {
                if ($(this).val().length > 0) {
                    $('#buscar_servicio_modal').val('');
                    $('#filtro_tipo_modal').val('');
                }
            });

            $('#buscar_servicio_modal').on('input', function() {
                if ($(this).val().length > 0) {
                    $('#buscar_producto_modal').val('');
                    $('#filtro_tipo_modal').val('');
                }
            });
            
            $('#filtro_tipo_modal').on('change', function() {
                if ($(this).val() !== '') {
                    $('#buscar_producto_modal, #buscar_servicio_modal').val('');
                }
            });

            $('.btn-aplicar-filtro').on('click', function() {
                const prod = $('#buscar_producto_modal').val().trim();
                const serv = $('#buscar_servicio_modal').val().trim();
                const tipoSelect = $('#filtro_tipo_modal').val();
                
                window.RedaNotificaciones.esperar();
                const url = new URL(window.location.origin + window.location.pathname);
                
                if (prod) {
                    url.searchParams.set('q', prod);
                    url.searchParams.set('tipo_actividad', 'producto');
                } else if (serv) {
                    url.searchParams.set('q', serv);
                    url.searchParams.set('tipo_actividad', 'servicio');
                } else if (tipoSelect) {
                    url.searchParams.set('tipo_actividad', tipoSelect);
                }
                
                window.location.href = url.toString();
            });
            
            $(document).on('click', '.producto-card:not(.card-ver-todos):not(.reseña-card)', function() {
                const id = $(this).data('id');
                if (id) abrirModalActividad(id);
            });
        });

        async function abrirModalActividad(id) {
            // Mostrar animación de espera global
            window.RedaNotificaciones.esperar();

            const res = await obtenerDetalleActividad(id);

            // Ocultar animación de espera global
            window.RedaNotificaciones.ocultar();

            if (res.success) {
                $('#bodyDetalleActividad').html(res.respuesta.html);
                $('#modalDetalleActividad').modal('show');
            } else {
                // Notificar error al usuario
                window.RedaNotificaciones.notificar(
                    window.RedaAlojamientoJson['Error'] || 'Error',
                    res.mensaje_usuario,
                    'error'
                );
            }
        }

        function filtrarActividades(texto, tipo) {
            const search = (texto || '').toLowerCase().trim();
            const filterTipo = (tipo || '').toLowerCase().trim();
            const esBusqueda = search !== '' || filterTipo !== '';

            $('.producto-card:not(.reseña-card)').each(function() {
                const $card = $(this);
                
                if ($card.hasClass('card-ver-todos')) {
                    // Si hay búsqueda activa, ocultamos el "Ver todos" del carrusel
                    if (esBusqueda) {
                        $card.hide();
                    } else {
                        $card.show();
                    }
                    return;
                }

                // Usar .attr() para mayor seguridad con atributos hyphenated y normalizar a minúsculas
                const tc = ($card.attr('data-tipo-actividad') || '').toLowerCase().trim();
                const nombre = $card.find('.producto-nombre').text().toLowerCase();
                
                const matchesTipo = (filterTipo === '' || filterTipo === tc);
                const matchesTexto = (search === '' || nombre.includes(search));

                if (matchesTipo && matchesTexto) {
                    $card.fadeIn(300);
                } else {
                    $card.hide();
                }
            });

            // Ocultar secciones vacías
            $('.seccion-productos').each(function() {
                if ($(this).attr('id') === 'seccion_reseñas') return;
                const visibles = $(this).find('.producto-card:visible:not(.card-ver-todos)').length;
                $(this).toggle(visibles > 0);
            });
        }
    }
})(jQuery);
