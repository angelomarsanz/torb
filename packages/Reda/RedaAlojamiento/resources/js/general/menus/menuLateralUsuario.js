export const menuLateralUsuario = () =>
{
    (function( $ ) {
        "use strict";

        // 0. VERIFICACIÓN DE LOGIN Y DUPLICADOS
        // Si no existe el link de logout, el usuario no ha iniciado sesión
        const isLoggedIn = $('a[href*="/logout"]').length > 0;
        if (!isLoggedIn) return;

        // Traducciones
        const textoNegocios = window.RedaAlojamientoJson["Negocios"] || "Negocios";
        const textoAlojamientos = window.RedaAlojamientoJson["Alojamientos"] || "Alojamientos";
        const textoCalificacionesObtenidas = window.RedaAlojamientoJson["Calificaciones obtenidas"] || "Calificaciones obtenidas";
        const textoCalificacionesRealizadas = window.RedaAlojamientoJson["Calificaciones realizadas"] || "Calificaciones realizadas";
        const textoQrCalificaciones = window.RedaAlojamientoJson["QR calificaciones"] || "QR calificaciones";
        const textoListadoCalificaciones = window.RedaAlojamientoJson["Listado de calificaciones"] || "Listado de calificaciones";

        // Función para mostrar la animación de espera
        const mostrarEsperar = () => {
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }
        };

        // --- GESTIÓN DE CLICKS (ANIMACIÓN DE CARGA PARA DASHBOARD) ---
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

            const bloqueNegociosSidebar = `
                <a class="text-color font-weight-500 mt-1 nav-item-plugin" href="${APP_URL}/reda/negocios/index-experiencias">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-4">
                        <i class="fas fa-store mr-3 text-18 align-middle"></i>
                        ${textoNegocios}
                    </li>
                </a>
            `;

            const opcionReferenciaSidebar = $(containerSidebar).find('a[href*="/properties"]');
            if (opcionReferenciaSidebar.length) {
                opcionReferenciaSidebar.after(bloqueNegociosSidebar);
            } else {
                $(containerSidebar).prepend(bloqueNegociosSidebar);
            }

            // --- REESTRUCTURACIÓN DE RESEÑAS (ESCRITORIO) ---
            const reviewsCollapseSidebar = $('#collapseReviews');
            if (reviewsCollapseSidebar.length) {
                const ul = reviewsCollapseSidebar.find('ul').first();
                ul.empty();
                
                const path = window.location.pathname;
                const isAlojamientosActive = path.includes('/users/reviews') || path.includes('/users/reviews_by_you');
                const isNegociosActive = path.includes('/reda/negocios/mis-calificaciones');

                const nestedHtml = `
                    <li class="nav-item-plugin">
                        <a data-toggle="collapse" href="#collapseAlojamientos" class="reda-sub-menu-toggle text-color">
                            <span class="pl-25">${textoAlojamientos}</span>
                            <i class="fas ${isAlojamientosActive ? 'fa-angle-down' : 'fa-angle-right'} reda-sub-menu-arrow pr-4"></i>
                        </a>
                        <div class="collapse ${isAlojamientosActive ? 'show' : ''}" id="collapseAlojamientos">
                            <ul class="reda-sub-menu-list">
                                <a href="${APP_URL}/users/reviews">
                                    <li class="list-group-item vbg-default-hover border-0 ${path.includes('/users/reviews') ? 'reda-active-option' : ''}">
                                        ${textoCalificacionesObtenidas}
                                    </li>
                                </a>
                                <a href="${APP_URL}/users/reviews_by_you">
                                    <li class="list-group-item vbg-default-hover border-0 ${path.includes('/users/reviews_by_you') ? 'reda-active-option' : ''}">
                                        ${textoCalificacionesRealizadas}
                                    </li>
                                </a>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item-plugin">
                        <a data-toggle="collapse" href="#collapseNegocios" class="reda-sub-menu-toggle text-color">
                            <span class="pl-25">${textoNegocios}</span>
                            <i class="fas ${isNegociosActive ? 'fa-angle-down' : 'fa-angle-right'} reda-sub-menu-arrow pr-4"></i>
                        </a>
                        <div class="collapse ${isNegociosActive ? 'show' : ''}" id="collapseNegocios">
                            <ul class="reda-sub-menu-list">
                                <a href="${APP_URL}/reda/negocios/mis-calificaciones/qr">
                                    <li class="list-group-item vbg-default-hover border-0 ${path.includes('/mis-calificaciones/qr') ? 'reda-active-option' : ''}">
                                        <i class="fas fa-qrcode mr-2"></i> ${textoQrCalificaciones}
                                    </li>
                                </a>
                                <a href="${APP_URL}/reda/negocios/mis-calificaciones/listado">
                                    <li class="list-group-item vbg-default-hover border-0 ${path.includes('/mis-calificaciones/listado') ? 'reda-active-option' : ''}">
                                        <i class="fas fa-star mr-2"></i> ${textoListadoCalificaciones}
                                    </li>
                                </a>
                            </ul>
                        </div>
                    </li>
                `;
                ul.append(nestedHtml);
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
            `;

            const opcionReferenciaMobile = $(containerMobile).find('a[href*="/properties"]').parent('li');
            if (opcionReferenciaMobile.length) {
                opcionReferenciaMobile.after(menuInyectarMobile);
            } else {
                $(containerMobile).find('li:first').after(menuInyectarMobile);
            }

            // --- REESTRUCTURACIÓN DE RESEÑAS (MÓVIL) ---
            const reviewsCollapseMobile = $('#collapseExample');
            if (reviewsCollapseMobile.length) {
                const ul = reviewsCollapseMobile.find('ul').first();
                ul.empty();
                
                const path = window.location.pathname;
                const isAlojamientosActive = path.includes('/users/reviews') || path.includes('/users/reviews_by_you');
                const isNegociosActive = path.includes('/reda/negocios/mis-calificaciones');

                const nestedHtmlMobile = `
                    <li class="nav-item-plugin-mobile">
                        <a data-toggle="collapse" href="#collapseAlojamientosMobile" class="reda-sub-menu-toggle">
                            <span><i class="fas fa-home mr-3"></i>${textoAlojamientos}</span>
                            <i class="fas ${isAlojamientosActive ? 'fa-angle-down' : 'fa-angle-right'} reda-sub-menu-arrow pr-3"></i>
                        </a>
                        <div class="collapse ${isAlojamientosActive ? 'show' : ''}" id="collapseAlojamientosMobile">
                            <ul class="reda-sub-menu-list">
                                <li><a href="${APP_URL}/users/reviews" class="${path.includes('/users/reviews') ? 'reda-active-option' : ''}">${textoCalificacionesObtenidas}</a></li>
                                <li><a href="${APP_URL}/users/reviews_by_you" class="${path.includes('/users/reviews_by_you') ? 'reda-active-option' : ''}">${textoCalificacionesRealizadas}</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item-plugin-mobile">
                        <a data-toggle="collapse" href="#collapseNegociosMobile" class="reda-sub-menu-toggle">
                            <span><i class="fas fa-store mr-3"></i>${textoNegocios}</span>
                            <i class="fas ${isNegociosActive ? 'fa-angle-down' : 'fa-angle-right'} reda-sub-menu-arrow pr-3"></i>
                        </a>
                        <div class="collapse ${isNegociosActive ? 'show' : ''}" id="collapseNegociosMobile">
                            <ul class="reda-sub-menu-list">
                                <li><a href="${APP_URL}/reda/negocios/mis-calificaciones/qr" class="${path.includes('/mis-calificaciones/qr') ? 'reda-active-option' : ''}"><i class="fas fa-qrcode mr-3"></i>${textoQrCalificaciones}</a></li>
                                <li><a href="${APP_URL}/reda/negocios/mis-calificaciones/listado" class="${path.includes('/mis-calificaciones/listado') ? 'reda-active-option' : ''}"><i class="fas fa-star mr-3"></i>${textoListadoCalificaciones}</a></li>
                            </ul>
                        </div>
                    </li>
                `;
                ul.append(nestedHtmlMobile);
            }
        }

        // --- GESTIÓN DE FLECHAS PARA SUB-MENÚS ---
        $(document).on('show.bs.collapse', '.collapse', function (e) {
            e.stopPropagation();
            $(this).prev('.reda-sub-menu-toggle').find('.reda-sub-menu-arrow').removeClass('fa-angle-right').addClass('fa-angle-down');
        });
        $(document).on('hide.bs.collapse', '.collapse', function (e) {
            e.stopPropagation();
            $(this).prev('.reda-sub-menu-toggle').find('.reda-sub-menu-arrow').removeClass('fa-angle-down').addClass('fa-angle-right');
        });

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
