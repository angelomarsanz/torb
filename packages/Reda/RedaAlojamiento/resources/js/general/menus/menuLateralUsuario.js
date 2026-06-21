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
        const textoMisListados = window.RedaAlojamientoJson["Mis listados"] || "Mis listados";
        const textoReservaciones = window.RedaAlojamientoJson["Reservaciones"] || "Reservaciones";
        const textoMisViajes = window.RedaAlojamientoJson["Mis viajes"] || "Mis viajes";
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

        if (sidebarPresent && !$(containerSidebar).find('[data-reda-plugin]').length) {
            console.log('Script para "Menú Lateral Usuario" cargado (Escritorio).');

            const path = window.location.pathname;

            // --- REESTRUCTURACIÓN DE ALOJAMIENTO (ESCRITORIO) ---
            const linkPropiedades = $(containerSidebar).find('a[href*="/properties"]');
            const linkReservaciones = $(containerSidebar).find('a[href*="/my-bookings"]');
            const linkViajes = $(containerSidebar).find('a[href*="/trips/active"]');

            if (linkPropiedades.length && linkReservaciones.length && linkViajes.length && !$('#collapseAlojamientoMain').length) {
                const isAlojamientoActive = path.includes('/properties') || path.includes('/my-bookings') || path.includes('/trips/active');

                const alojamientoHtml = `
                    <a data-toggle="collapse" href="#collapseAlojamientoMain" class="text-color font-weight-500 mt-1" role="button" data-reda-plugin>
                        <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-4 mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-home mr-3 text-18 align-middle"></i>
                                    ${textoAlojamientos}
                                </div>
                                <i class="fas ${isAlojamientoActive ? 'fa-angle-down' : 'fa-angle-right'} reda-sub-menu-arrow pr-4"></i>
                            </div>
                        </li>
                    </a>
                    <div class="collapse ${isAlojamientoActive ? 'show' : ''}" id="collapseAlojamientoMain">
                        <ul class="reda-sub-menu-list">
                            <a href="${APP_URL}/properties">
                                <li class="list-group-item vbg-default-hover border-0 ${path.includes('/properties') ? 'reda-active-option' : ''}">
                                    <i class="far fa-list-alt mr-2 text-14"></i> ${textoMisListados}
                                </li>
                            </a>
                            <a href="${APP_URL}/my-bookings">
                                <li class="list-group-item vbg-default-hover border-0 ${path.includes('/my-bookings') ? 'reda-active-option' : ''}">
                                    <i class="fa fa-bookmark mr-2 text-14"></i> ${textoReservaciones}
                                </li>
                            </a>
                            <a href="${APP_URL}/trips/active">
                                <li class="list-group-item vbg-default-hover border-0 ${path.includes('/trips/active') ? 'reda-active-option' : ''}">
                                    <i class="fa fa-suitcase mr-2 text-14"></i> ${textoMisViajes}
                                </li>
                            </a>
                        </ul>
                    </div>
                `;

                linkPropiedades.before(alojamientoHtml);
                linkPropiedades.remove();
                linkReservaciones.remove();
                linkViajes.remove();
            }

            const bloqueNegociosSidebar = `
                <a class="text-color font-weight-500 mt-1" href="${APP_URL}/reda/negocios/index-experiencias" data-reda-plugin>
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-4">
                        <i class="fas fa-store mr-3 text-18 align-middle"></i>
                        ${textoNegocios}
                    </li>
                </a>
            `;

            // Insertamos Negocios después de nuestro nuevo menú de Alojamiento
            const nuevoMenuAlojamiento = $('#collapseAlojamientoMain');
            if (nuevoMenuAlojamiento.length) {
                nuevoMenuAlojamiento.after(bloqueNegociosSidebar);
            } else {
                $(containerSidebar).prepend(bloqueNegociosSidebar);
            }

            // --- REESTRUCTURACIÓN DE RESEÑAS (ESCRITORIO) ---
            const reviewsCollapseSidebar = $('#collapseReviews');
            const reviewsToggleSidebar = $('#reviewIcon');

            if (reviewsCollapseSidebar.length && reviewsToggleSidebar.length) {
                const ul = reviewsCollapseSidebar.find('ul').first();
                ul.empty();
                
                const isReviewsAlojamientosActive = path.includes('/users/reviews') || path.includes('/users/reviews_by_you');
                const isReviewsNegociosActive = path.includes('/reda/negocios/mis-calificaciones');

                const nestedHtml = `
                    <a data-toggle="collapse" href="#collapseAlojamientosReviews" class="reda-sub-menu-toggle text-color" role="button">
                        <li class="list-group-item vbg-default-hover border-0 pl-25">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div><i class="fas fa-home mr-3 text-18"></i>${textoAlojamientos}</div>
                                <i class="fas ${isReviewsAlojamientosActive ? 'fa-angle-down' : 'fa-angle-right'} reda-sub-menu-arrow pr-4"></i>
                            </div>
                        </li>
                    </a>
                    <div class="collapse ${isReviewsAlojamientosActive ? 'show' : ''}" id="collapseAlojamientosReviews">
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
                    <a data-toggle="collapse" href="#collapseNegociosReviews" class="reda-sub-menu-toggle text-color" role="button">
                        <li class="list-group-item vbg-default-hover border-0 pl-25">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div><i class="fas fa-store mr-3 text-18"></i>${textoNegocios}</div>
                                <i class="fas ${isReviewsNegociosActive ? 'fa-angle-down' : 'fa-angle-right'} reda-sub-menu-arrow pr-4"></i>
                            </div>
                        </li>
                    </a>
                    <div class="collapse ${isReviewsNegociosActive ? 'show' : ''}" id="collapseNegociosReviews">
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
                `;
                ul.append(nestedHtml);

                // MOVER ENTRE FAVORITOS Y PAGOS
                const linkFavoritos = $(containerSidebar).find('a[href*="/user/favourite"]');
                if (linkFavoritos.length) {
                    linkFavoritos.after(reviewsCollapseSidebar);
                    linkFavoritos.after(reviewsToggleSidebar);
                }
            }
        }

        // 2. GESTIÓN DEL MENÚ MÓVIL (MODAL IZQUIERDO)
        const containerMobile = '.mobile-side';
        if ($(containerMobile).length && !$(containerMobile).find('[data-reda-plugin]').length) {
            console.log('Script para "Menú Lateral Usuario" cargado (Móvil).');

            const path = window.location.pathname;

            // --- REESTRUCTURACIÓN DE ALOJAMIENTO (MÓVIL) ---
            const linkPropiedadesMobile = $(containerMobile).find('a[href*="/properties"]').parent('li');
            const linkReservacionesMobile = $(containerMobile).find('a[href*="/my-bookings"]').parent('li');
            const linkViajesMobile = $(containerMobile).find('a[href*="/trips/active"]').parent('li');

            if (linkPropiedadesMobile.length && linkReservacionesMobile.length && linkViajesMobile.length && !$('#collapseAlojamientoMobile').length) {
                // MATCH TORBIAN STRUCTURE: a > li for collapsible
                const alojamientoHtmlMobile = `
                    <a data-toggle="collapse" href="#collapseAlojamientoMobile" role="button" data-reda-plugin class="reda-sub-menu-toggle">
                        <li class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-home mr-3"></i>${textoAlojamientos}</span>
                            <i class="fas fa-angle-right reda-sub-menu-arrow pr-3"></i>
                        </li>
                    </a>
                    <div class="collapse" id="collapseAlojamientoMobile">
                        <ul class="reda-sub-menu-list">
                            <li>
                                <a href="${APP_URL}/properties">
                                    <i class="far fa-list-alt mr-3"></i>${textoMisListados}
                                </a>
                            </li>
                            <li>
                                <a href="${APP_URL}/my-bookings">
                                    <i class="fa fa-bookmark mr-3"></i>${textoReservaciones}
                                </a>
                            </li>
                            <li>
                                <a href="${APP_URL}/trips/active">
                                    <i class="fa fa-suitcase mr-3"></i>${textoMisViajes}
                                </a>
                            </li>
                        </ul>
                    </div>
                `;

                linkPropiedadesMobile.before(alojamientoHtmlMobile);
                linkPropiedadesMobile.remove();
                linkReservacionesMobile.remove();
                linkViajesMobile.remove();
            }

            // MATCH TORBIAN STRUCTURE: li > a for simple links
            const menuInyectarMobile = `
                <li data-reda-plugin>
                    <a href="${APP_URL}/reda/negocios/index-experiencias">
                        <i class="fas fa-store mr-3"></i>${textoNegocios}
                    </a>
                </li>
            `;

            // Insertamos Negocios después de nuestro nuevo menú de Alojamiento móvil
            const nuevoMenuAlojamientoMobile = $('#collapseAlojamientoMobile');
            if (nuevoMenuAlojamientoMobile.length) {
                nuevoMenuAlojamientoMobile.after(menuInyectarMobile);
            } else {
                $(containerMobile).find('li:first').after(menuInyectarMobile);
            }

            // --- REESTRUCTURACIÓN DE RESEÑAS (MÓVIL) ---
            const reviewsCollapseMobile = $('#collapseExample');
            const reviewsToggleMobile = $(containerMobile).find('a[href="#collapseExample"]');

            if (reviewsCollapseMobile.length && reviewsToggleMobile.length) {
                const ul = reviewsCollapseMobile.find('ul').first();
                ul.empty();
                
                const nestedHtmlMobile = `
                    <a data-toggle="collapse" href="#collapseAlojamientosReviewsMobile" role="button" class="reda-sub-menu-toggle">
                        <li class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-home mr-3"></i>${textoAlojamientos}</span>
                            <i class="fas fa-angle-right reda-sub-menu-arrow pr-3"></i>
                        </li>
                    </a>
                    <div class="collapse" id="collapseAlojamientosReviewsMobile">
                        <ul class="reda-sub-menu-list">
                            <li><a href="${APP_URL}/users/reviews">${textoCalificacionesObtenidas}</a></li>
                            <li><a href="${APP_URL}/users/reviews_by_you">${textoCalificacionesRealizadas}</a></li>
                        </ul>
                    </div>
                    <a data-toggle="collapse" href="#collapseNegociosReviewsMobile" role="button" class="reda-sub-menu-toggle">
                        <li class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-store mr-3"></i>${textoNegocios}</span>
                            <i class="fas fa-angle-right reda-sub-menu-arrow pr-3"></i>
                        </li>
                    </a>
                    <div class="collapse" id="collapseNegociosReviewsMobile">
                        <ul class="reda-sub-menu-list">
                            <li><a href="${APP_URL}/reda/negocios/mis-calificaciones/qr"><i class="fas fa-qrcode mr-3"></i>${textoQrCalificaciones}</a></li>
                            <li><a href="${APP_URL}/reda/negocios/mis-calificaciones/listado"><i class="fas fa-star mr-3"></i>${textoListadoCalificaciones}</a></li>
                        </ul>
                    </div>
                `;
                ul.append(nestedHtmlMobile);

                // MOVER ENTRE FAVORITOS Y PAGOS
                const linkFavoritosMobile = $(containerMobile).find('a[href*="/user/favourite"]').parent('li');
                if (linkFavoritosMobile.length) {
                    linkFavoritosMobile.after(reviewsCollapseMobile);
                    linkFavoritosMobile.after(reviewsToggleMobile);
                }
            }
        }

        // --- GESTIÓN DE FLECHAS PARA SUB-MENÚS ---
        $(document).on('show.bs.collapse', '.collapse', function (e) {
            e.stopPropagation();
            $(this).prev('.reda-sub-menu-toggle, [data-reda-plugin]').find('.reda-sub-menu-arrow').removeClass('fa-angle-right').addClass('fa-angle-down');
        });
        $(document).on('hide.bs.collapse', '.collapse', function (e) {
            e.stopPropagation();
            $(this).prev('.reda-sub-menu-toggle, [data-reda-plugin]').find('.reda-sub-menu-arrow').removeClass('fa-angle-down').addClass('fa-angle-right');
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

        // --- GESTIÓN MANUAL DE ESTADO ACTIVO (MÓVIL) ---
        // Deshabilitado por requerimiento: Se busca que el menú inicie "frío" (todo verde normal y colapsado)
        /*
        const gestionarActivoMovil = () => {
            const path = window.location.pathname;
            $('.mobile-side a').each(function() {
                const href = $(this).attr('href');
                if (!href || href === '#' || href === 'javascript:void(0)') return;

                // Normalizar href para comparación (quitar dominio si existe)
                const urlObj = new URL(href, window.location.origin);
                const linkPath = urlObj.pathname;

                // Coincidencia exacta o parcial (para rutas con parámetros)
                const isMatch = (path === linkPath) || (path.startsWith(linkPath) && linkPath !== '/');

                if (isMatch) {
                    $(this).addClass('active reda-active-option');
                    $(this).closest('li').addClass('active reda-active-option');
                    
                    // Si está dentro de un colapsable, abrirlo y marcar el padre
                    const parentCollapse = $(this).closest('.collapse');
                    if (parentCollapse.length) {
                        parentCollapse.addClass('show');
                        const toggle = parentCollapse.prev('.reda-sub-menu-toggle, [data-toggle="collapse"]');
                        toggle.addClass('active reda-active-option');
                        toggle.find('.reda-sub-menu-arrow').removeClass('fa-angle-right').addClass('fa-angle-down');
                    }
                }
            });
        };

        // Ejecutar al cargar y cuando se abre el modal por si acaso
        gestionarActivoMovil();
        $(document).on('shown.bs.modal', '#left_modal', gestionarActivoMovil);
        */

    })(jQuery);
}
menuLateralUsuario();
