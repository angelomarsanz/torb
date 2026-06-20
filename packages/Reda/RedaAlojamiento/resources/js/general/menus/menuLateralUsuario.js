export const menuLateralUsuario = () =>
{
    (function( $ ) {
        "use strict";

        // 0. VERIFICACIÓN DE LOGIN Y DUPLICADOS
        // Si no existe el link de logout, el usuario no ha iniciado sesión
        const isLoggedIn = $('a[href*="/logout"]').length > 0;
        if (!isLoggedIn) return;

        const textoNegocios = window.RedaAlojamientoJson["Negocios"] || "Negocios";
        const textoCalificaciones = window.RedaAlojamientoJson["Calificaciones"] || "Calificaciones";
        const textoQrCalificaciones = window.RedaAlojamientoJson["QR calificaciones"] || "QR calificaciones";
        const textoListadoCalificaciones = window.RedaAlojamientoJson["Listado de calificaciones"] || "Listado de calificaciones";

        // Función para mostrar la animación de espera
        const mostrarEsperar = () => {
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }
        };

        // --- GESTIÓN DE CLICKS (ANIMACIÓN DE CARGA PARA DASHBOARD) ---
        // Los links del dashboard pueden no tener el prefijo /reda/negocios en algunos casos o ser específicos
        $(document).on('click', '#card-negocios-dashboard a', function(e) {
            if (this.href && !this.target && !e.ctrlKey && !e.metaKey) {
                mostrarEsperar();
            }
        });

        // 1. GESTIÓN DEL MENÚ LATERAL (ESCRITORIO)
        const containerSidebar = '.list-group-flush';
        const sidebarPresent = $(containerSidebar).length;

        if (sidebarPresent && !$(containerSidebar).find('.nav-item-plugin').length) {
            console.log('Script para "Menú Lateral Usuario" cargado (Escritorio).');

            // Definir los bloques de opciones para el sidebar
            const bloqueNegociosSidebar = `
                <a class="text-color font-weight-500 mt-1 nav-item-plugin" href="${APP_URL}/reda/negocios/index-experiencias">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-4">
                        <i class="fas fa-store mr-3 text-18 align-middle"></i>
                        ${textoNegocios}
                    </li>
                </a>
            `;

            const bloqueCalificacionesSidebar = `
                <div class="nav-item-plugin mt-4 mb-2 pl-25 text-muted font-weight-700 text-12 text-uppercase letter-spacing-1">
                    ${textoCalificaciones}
                </div>
                <a class="text-color font-weight-500 nav-item-plugin" href="${APP_URL}/reda/negocios/mis-calificaciones/qr">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 pt-3 pb-3">
                        <i class="fas fa-qrcode mr-3 text-18 align-middle"></i>
                        ${textoQrCalificaciones}
                    </li>
                </a>
                <a class="text-color font-weight-500 nav-item-plugin" href="${APP_URL}/reda/negocios/mis-calificaciones/listado">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 pt-3 pb-3">
                        <i class="fas fa-star mr-3 text-18 align-middle"></i>
                        ${textoListadoCalificaciones}
                    </li>
                </a>
            `;

            const menuInyectarSidebar = bloqueNegociosSidebar + bloqueCalificacionesSidebar;

            // Localizar la opción "Listings" (Alojamientos) SOLO en el sidebar
            const opcionReferenciaSidebar = $(containerSidebar).find('a[href*="/properties"]');

            if (opcionReferenciaSidebar.length) {
                opcionReferenciaSidebar.after(menuInyectarSidebar);
            } else {
                $(containerSidebar).prepend(menuInyectarSidebar);
            }
        }

        // 2. GESTIÓN DEL MENÚ MÓVIL (MODAL IZQUIERDO)
        const containerMobile = '.mobile-side';
        if ($(containerMobile).length && !$(containerMobile).find('.nav-item-plugin-mobile').length) {
            console.log('Script para "Menú Lateral Usuario" cargado (Móvil).');

            const menuInyectarMobile = `
                <li class="nav-item-plugin-mobile">
                    <a href="${APP_URL}/reda/negocios/index-experiencias">
                        <i class="fas fa-store mr-3"></i>${textoNegocios}
                    </a>
                </li>
                <li class="nav-item-plugin-mobile">
                    <a href="${APP_URL}/reda/negocios/mis-calificaciones/qr">
                        <i class="fas fa-qrcode mr-3"></i>${textoQrCalificaciones}
                    </a>
                </li>
                <li class="nav-item-plugin-mobile">
                    <a href="${APP_URL}/reda/negocios/mis-calificaciones/listado">
                        <i class="fas fa-star mr-3"></i>${textoListadoCalificaciones}
                    </a>
                </li>
            `;

            // Buscamos la opción "Listings" en el menú móvil
            const opcionReferenciaMobile = $(containerMobile).find('a[href*="/properties"]').parent('li');

            if (opcionReferenciaMobile.length) {
                opcionReferenciaMobile.after(menuInyectarMobile);
            } else {
                // Si no encontramos la referencia, lo ponemos después del Dashboard (primer li)
                $(containerMobile).find('li:first').after(menuInyectarMobile);
            }
        }

        // 3. GESTIÓN DEL DASHBOARD (TARJETAS RESUMEN)
        const isDashboard = window.location.pathname.includes('/dashboard');
        if (isDashboard) {
            const dashboardRow = $('.container-fluid .row.mt-4').first();
            if (dashboardRow.length && !$('#card-negocios-dashboard').length) {
                const cardNegociosHtml = `
                    <div class="col-md-4" id="card-negocios-dashboard">
                        <div class="card card-default p-3 mt-3">
                            <div class="card-body">
                                <p class="text-center font-weight-bold m-0">
                                    <i class="fas fa-briefcase mr-2 text-16 align-middle badge-dark rounded-circle p-3 vbadge-success"></i> 
                                    ${textoNegocios}
                                </p>
                                <div class="d-flex justify-content-around mt-3">
                                    <a href="${APP_URL}/reda/negocios/mis-calificaciones/qr" class="text-center text-color" title="${textoQrCalificaciones}">
                                        <i class="fas fa-qrcode d-block mb-1 text-18"></i>
                                        <span class="small font-weight-700">QR</span>
                                    </a>
                                    <a href="${APP_URL}/reda/negocios/mis-calificaciones/listado" class="text-center text-color" title="${textoListadoCalificaciones}">
                                        <i class="fas fa-star d-block mb-1 text-18"></i>
                                        <span class="small font-weight-700">Reseñas</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                dashboardRow.append(cardNegociosHtml);
            }
        }

    })(jQuery);
}
menuLateralUsuario();

