export const menuLateralAdmin = () =>
{
    (function( $ ) {
        "use strict";
        const containerId = '.sidebar-menu';

        if ($(containerId).length) {
            console.log('Script para "Menú Lateral Admin" cargado correctamente.');

            $(function() {
                // 1. Ubicamos el botón de Properties
                const $propertiesMenuItem = $('.sidebar-menu a[href*="admin/properties"]').closest('li');

                if ($propertiesMenuItem.length) {
                    // 2. Insertamos la estructura HTML (Añadimos un identificador único id="menu-negocios" para controlarlo mejor)
                    const nuevoMenuHtml = `
                        <li class="treeview" id="menu-negocios">
                            <a href="#" class="negocios-toggle">
                                <i class="fa fa-briefcase"></i> <span>${window.RedaAlojamiento.general.negocios}</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu" style="display: none;">
                                <li>
                                    <a href="#"><span>${window.RedaAlojamiento.general.tipos_de_negocios}</span></a>
                                </li>
                            </ul>
                        </li>
                    `;
                    $propertiesMenuItem.after(nuevoMenuHtml);
                    console.log('Opción "Negocios" inyectada.');

                    // 3. Control absoluto del Click bloqueando scripts externos
                    $('#menu-negocios > .negocios-toggle').on('click', function(e) {
                        // DETENER TODO: Evita que el JS original de la plantilla interfiera y cierre el menú
                        e.preventDefault();
                        e.stopImmediatePropagation(); 

                        const $liPadre = $(this).closest('#menu-negocios');
                        const $subMenu = $liPadre.find('.treeview-menu');
                        const $flecha = $(this).find('.pull-right');

                        // Alternar la animación de despliegue de forma aislada
                        if ($subMenu.is(':visible')) {
                            $subMenu.slideUp('fast');
                            $liPadre.removeClass('active menu-open');
                            $flecha.removeClass('fa-angle-down').addClass('fa-angle-left');
                        } else {
                            $subMenu.slideDown('fast');
                            $liPadre.addClass('active menu-open');
                            // Cambiamos la flecha hacia abajo para dar el efecto visual correcto si el tema lo soporta
                            $flecha.removeClass('fa-angle-left').addClass('fa-angle-down');
                        }
                    });

                } else {
                    console.warn('No se encontró la opción "Properties" en el menú lateral.');
                }
            });
        }
    })(jQuery);
}
menuLateralAdmin();