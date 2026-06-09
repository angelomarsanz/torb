// packages/Reda/RedaAlojamiento/resources/js/general/menus/menuPrincipal.js
import { alojamientosSvg, comerciosSvg } from '../iconos';

export const menuPrincipal = () => {
    (function( $ ) {
        "use strict";

        const navbarContainer = $('.navbar .container-fluid');
        if (!navbarContainer.length) return;

        // Evitar duplicados
        if ($('#reda-menu-principal').length) return;

        const urlAlojamientos = APP_URL + '/';
        const urlComercios = APP_URL + '/reda/negocios/listado-negocios';

        const textoAlojamientos = window.RedaAlojamientoJson["Alojamientos"] || "Alojamientos";
        const textoComercios = window.RedaAlojamientoJson["Comercios"] || "Comercios";

        // Determinar cuál está activo basándonos en la URL actual
        const pathActual = window.location.pathname;
        
        // Lógica simplificada para detectar secciones
        const esAlojamientos = pathActual === '/' 
            || pathActual === APP_URL + '/' 
            || pathActual.includes('/search') 
            || pathActual.includes('/properties') 
            || pathActual.includes('/property/');
            
        const esComercios = pathActual.includes('/reda/negocios/listado-negocios') 
            || pathActual.includes('/reda/negocios/listado-productos-servicios') 
            || pathActual.includes('/reda/negocios/experiencias');

        const menuHtml = `
            <div id="reda-menu-principal" class="d-flex align-items-center reda-menu-principal" data-role="added-by-reda">
                <a href="${urlAlojamientos}" class="reda-menu-item ${esAlojamientos ? 'active' : ''}">
                    <div class="reda-menu-icon">${alojamientosSvg}</div>
                    <span class="reda-menu-text">${textoAlojamientos}</span>
                </a>
                <a href="${urlComercios}" class="reda-menu-item ${esComercios ? 'active' : ''}">
                    <div class="reda-menu-icon">${comerciosSvg}</div>
                    <span class="reda-menu-text">${textoComercios}</span>
                </a>
            </div>
        `;

        // Insertar después del logo (navbar-brand) para que esté entre el logo y el toggler/collapse
        const logo = navbarContainer.find('.navbar-brand');
        if (logo.length) {
            logo.after(menuHtml);
        }

    })(jQuery);
};

// Iniciar al cargar el DOM
$(function () {
    menuPrincipal();
});
