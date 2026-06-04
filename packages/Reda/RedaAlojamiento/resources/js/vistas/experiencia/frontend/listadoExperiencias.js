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
            // Inicializar eventos para los botones de favoritos (lógica futura)
            $(document).on('click', '.btn-favorito', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $icono = $(this).find('i');
                if ($icono.hasClass('far')) {
                    $icono.removeClass('far').addClass('fas text-danger');
                } else {
                    $icono.removeClass('fas text-danger').addClass('far');
                }
                
                // Aquí se llamaría a una función para guardar en la base de datos vía AJAX
                console.log('Acción de favorito clickeada');
            });

            // Evitar que el clic en el botón de favorito dispare el onclick de la tarjeta
            $(document).on('click', '.negocio-card', function(e) {
                if ($(e.target).closest('.btn-favorito').length) {
                    return;
                }
                // Aquí se redirigiría al detalle del negocio si no es un clic en favorito
                console.log('Redirigiendo al detalle del negocio...');
            });
        });
    }
})(jQuery);
