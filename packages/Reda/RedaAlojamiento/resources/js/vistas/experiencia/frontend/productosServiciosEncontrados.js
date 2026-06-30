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

            // Redirección al hacer clic en una tarjeta de producto/servicio (Tratamiento especial solicitado)
            $(document).on('click', '.producto-card:not(.card-ver-todos):not(.reseña-card)', function(e) {
                const $card = $(this);
                const idExperiencia = $card.data('id-experiencia');
                const idActividad = $card.data('id');
                
                // Si la tarjeta ya es un enlace (vía Blade), permitimos el comportamiento natural del enlace 
                // pero si el clic ocurre en el contenedor, forzamos la redirección con los parámetros correctos.
                if ($(e.target).closest('a').length === 0 && idExperiencia && idActividad) {
                    const url = APP_URL + '/reda/negocios/listado-productos-servicios/' + idExperiencia + '/' + idActividad;
                    window.location.href = url;
                }
            });

            // Interacción con "Ver todos" (Scroll Infinito)
            $(document).on('click', '.card-ver-todos', function() {
                const $card = $(this);
                
                const options = {
                    tipo: $card.data('tipo'),
                    tituloModal: $card.data('titulo-modal'),
                    urlBase: APP_URL + '/reda/negocios/productos-servicios-encontrados',
                    extraData: {
                        q: $card.data('busqueda'),
                        tipo: $card.data('tipo-filtro'),
                        es_modal: true
                    }
                };
                
                ListadoInfinito.iniciar(options);
            });
        });
    }
})(jQuery);
