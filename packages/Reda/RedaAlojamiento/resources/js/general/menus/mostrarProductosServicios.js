// packages/Reda/RedaAlojamiento/resources/js/general/menus/mostrarProductosServicios.js
// Script para añadir el botón flotante de "Productos y Servicios" exclusivo para la Home.
// Basado en el requerimiento de inyección no invasiva y centralización de estilos.

import { productosServiciosSvg } from '../iconos';

export const mostrarProductosServicios = () => {
    (function( $ ) {
        "use strict";

        // Solo se ejecuta en la página de inicio (detectamos por la clase hero-banner de home.blade.php)
        if (!$('.hero-banner').length) return;

        const botonId = 'btn-flotante-productos-servicios';
        const textoBoton = window.RedaAlojamientoJson["Productos y Servicios"] || "Productos y Servicios";
        const urlProductosServicios = APP_URL + '/reda/listado-negocios';

        // Usamos el icono importado
        const iconoBoton = productosServiciosSvg;

        // HTML del botón flotante
        const botonHtml = `
            <a href="${urlProductosServicios}" id="${botonId}" class="btn-flotante-productos-servicios" data-role="added-by-reda">
                ${iconoBoton}
                <span>${textoBoton}</span>
            </a>
        `;

        const insertarBoton = () => {
            if ($('#' + botonId).length) return true;
            
            // Lo añadimos al body para que tenga libertad de posicionamiento fijo
            $('body').append(botonHtml);
            return true;
        };

        // Iniciar al cargar el DOM
        $(function () {
            insertarBoton();
        });
    })(jQuery);
};

// Auto-ejecución del script
mostrarProductosServicios();
