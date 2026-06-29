import { ListadoInfinito } from '../../../general/utilidades/listadoInfinito.js';

(function( $ ) {
    "use strict";

    const containerId = '#productos_servicios_encontrados';

    if ($(containerId).length) {
        console.log(window.RedaAlojamientoJson["Script para Productos y Servicios Encontrados cargado correctamente"] || 'Script para Productos y Servicios Encontrados cargado correctamente');

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
                        resolve({ success: false, mensaje_usuario: window.RedaAlojamientoJson['Error en el servidor'] || 'Error en el servidor' });
                    }
                });
            });
        };

        $(function() {
            // Inicializar carruseles
            $('.container-carrusel-productos').each(function() {
                initCarrusel($(this));
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

            // Detalle de Actividad al hacer clic en una card
            $(document).on('click', '.producto-card:not(.card-ver-todos)', async function() {
                const id = $(this).data('id');
                if (!id) return;

                $('#bodyDetalleActividad').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x text-success"></i></div>');
                $('#modalDetalleActividad').modal('show');

                const res = await obtenerDetalleActividad(id);
                if (res.success) {
                    $('#bodyDetalleActividad').html(res.respuesta.html);
                } else {
                    const errorHtml = `<div class="alert alert-danger m-4">${res.mensaje_usuario}</div>`;
                    $('#bodyDetalleActividad').html(errorHtml);
                }
            });

            // Interacción con "Ver todos" (Scroll Infinito)
            $(document).on('click', '.card-ver-todos', function() {
                const $card = $(this);
                
                const options = {
                    tipo: $card.data('tipo'),
                    tituloModal: $card.data('titulo-modal'),
                    urlBase: APP_URL + '/reda/negocios/productos-servicios-encontrados', // Necesitaría endpoint paginado
                    extraData: {
                        q: $card.data('busqueda'),
                        tipo: $card.data('tipo-filtro'),
                        es_modal: true
                    }
                };
                
                // Nota: El controlador productosServiciosEncontrados debe soportar AJAX para esto
                // o crear una ruta específica de paginación similar a obtenerNegociosPaginados.
                // ListadoInfinito.iniciar(options);
            });
        });
    }
})(jQuery);
