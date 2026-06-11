export const menuLateralUsuario = () =>
{
    (function( $ ) {
        "use strict";

        // 1. GESTIÓN DEL MENÚ LATERAL
        const containerSidebar = '.list-group-flush';
        const sidebarPresent = $(containerSidebar).length;

        if (sidebarPresent) {
            console.log('Script para "Menú Lateral Usuario" cargado con jerarquía mejorada.');

            // Definir los bloques de opciones para el sidebar
            const bloqueNegociosSidebar = `
                <a class="text-color font-weight-500 mt-1 nav-item-plugin" href="${APP_URL}/reda/negocios/index-experiencias">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-4">
                        <i class="fas fa-store mr-3 text-18 align-middle"></i>
                        Negocios
                    </li>
                </a>
            `;

            const bloqueCalificacionesSidebar = `
                <div class="mt-4 mb-2 pl-25 text-muted font-weight-700 text-12 text-uppercase letter-spacing-1">
                    Calificaciones
                </div>
                <a class="text-color font-weight-500 nav-item-plugin" href="${APP_URL}/reda/negocios/mis-calificaciones/qr">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 pt-3 pb-3">
                        <i class="fas fa-qrcode mr-3 text-18 align-middle"></i>
                        QR calificaciones
                    </li>
                </a>
                <a class="text-color font-weight-500 nav-item-plugin" href="${APP_URL}/reda/negocios/mis-calificaciones/listado">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 pt-3 pb-3">
                        <i class="fas fa-star mr-3 text-18 align-middle"></i>
                        Listado de calificaciones
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

        // 2. GESTIÓN DEL DASHBOARD (TARJETAS RESUMEN)
        const isDashboard = window.location.pathname.includes('/dashboard');
        if (isDashboard) {
            const dashboardRow = $('.container-fluid .row.mt-4').first();
            if (dashboardRow.length && !$('#card-negocios-dashboard').length) {

                // Ajustamos las columnas existentes de col-md-4 a col-md-3 para que quepan 4 tarjetas
                // o lo dejamos en col-md-3 para la nueva si queremos que se vea bien.
                // Según dashboard.blade.php original usa col-md-4 (3 tarjetas por fila).
                // Añadiremos una cuarta tarjeta que se posicionará debajo o redimensionaremos.

                const cardNegociosHtml = `
                    <div class="col-md-4" id="card-negocios-dashboard">
                        <div class="card card-default p-3 mt-3">
                            <div class="card-body">
                                <p class="text-center font-weight-bold m-0">
                                    <i class="fas fa-briefcase mr-2 text-16 align-middle badge-dark rounded-circle p-3 vbadge-success"></i> 
                                    Negocios
                                </p>
                                <div class="d-flex justify-content-around mt-3">
                                    <a href="${APP_URL}/reda/negocios/mis-calificaciones/qr" class="text-center text-color" title="QR Calificaciones">
                                        <i class="fas fa-qrcode d-block mb-1 text-18"></i>
                                        <span class="small font-weight-700">QR</span>
                                    </a>
                                    <a href="${APP_URL}/reda/negocios/mis-calificaciones/listado" class="text-center text-color" title="Listado de Calificaciones">
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

