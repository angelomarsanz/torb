export const menuLateralAdmin = () =>
{
    (function( $ ) {
        "use strict";
        const containerId = '.sidebar-menu';

        if ($(containerId).length) {
            console.log('Script para "Menú Lateral Admin" cargado correctamente.');

            $(function() {
                const baseUrl = window.location.origin;

                // 1. Inyección de Menú "Negocios" (Después de Properties)
                const $propertiesMenuItem = $('.sidebar-menu a[href*="admin/properties"]').closest('li');

                if ($propertiesMenuItem.length) {
                    const linkOpcionesNegocios = `${baseUrl}/admin/reda/negocios/opciones-tipos-de-negocios`;
                    const nuevoMenuHtml = `
                        <li class="treeview" id="menu-negocios">
                            <a href="#" class="negocios-toggle">
                                <i class="fa fa-briefcase"></i> <span>${window.RedaAlojamiento.general.negocios}</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu" style="display: none;">
                                <li>
                                    <a href="${linkOpcionesNegocios}"><span>${window.RedaAlojamiento.general.tipos_de_negocios}</span></a>
                                </li>
                            </ul>
                        </li>
                    `;
                    $propertiesMenuItem.after(nuevoMenuHtml);
                    console.log('Opción "Negocios" inyectada.');

                    $('#menu-negocios > .negocios-toggle').on('click', function(e) {
                        e.preventDefault();
                        e.stopImmediatePropagation(); 

                        const $liPadre = $(this).closest('#menu-negocios');
                        const $subMenu = $liPadre.find('.treeview-menu');
                        const $flecha = $(this).find('.pull-right');

                        if ($subMenu.is(':visible')) {
                            $subMenu.slideUp('fast');
                            $liPadre.removeClass('active menu-open');
                            $flecha.removeClass('fa-angle-down').addClass('fa-angle-left');
                        } else {
                            $subMenu.slideDown('fast');
                            $liPadre.addClass('active menu-open');
                            $flecha.removeClass('fa-angle-left').addClass('fa-angle-down');
                        }
                    });
                }

                // 2. Inyección de Opción "Soporte Técnico" (Después de Messages)
                const $messagesMenuItem = $('.sidebar-menu a[href*="admin/messages"]').closest('li');

                if ($messagesMenuItem.length) {
                    const linkSoporte = `${baseUrl}/admin/reda/general/soporte-tecnico`;
                    const soporteMenuHtml = `
                        <li id="menu-soporte">
                            <a href="${linkSoporte}" class="btn-menu-soporte">
                                <i class="fa fa-life-ring"></i> <span>${window.RedaAlojamiento.general.soporte_tecnico || 'Soporte técnico'}</span>
                            </a>
                        </li>
                    `;
                    $messagesMenuItem.after(soporteMenuHtml);
                    console.log('Opción "Soporte Técnico" inyectada.');

                    // Animación de espera al hacer clic en Soporte Técnico
                    $(document).on('click', '.btn-menu-soporte', function(e) {
                        if (this.href && !this.target && !e.ctrlKey && !e.metaKey) {
                            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                                window.RedaNotificaciones.esperar();
                            }
                        }
                    });
                }
            });
        }
    })(jQuery);
}
menuLateralAdmin();