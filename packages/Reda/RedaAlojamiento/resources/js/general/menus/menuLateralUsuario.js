export const menuLateralUsuario = () =>
{
    (function( $ ) {
        "use strict";
        const containerId = '#reviewIcon';

        if ($(containerId).length) {

            console.log('Script para "Menú Lateral Usuario" cargado con nueva estructura jerárquica.');

            // 1. Definir los bloques de opciones
            // --- Grupo Negocios ---
            const bloqueNegocios = `
                <div class="mt-4 mb-2 pl-25 text-muted font-weight-700 text-12 text-uppercase" style="letter-spacing: 1px;">
                    Negocios
                </div>
                <a class="text-color font-weight-500 nav-item-plugin" href="${APP_URL}/reda/negocios/index-experiencias">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-3">
                        <i class="fas fa-list-ul mr-3 text-18 align-middle"></i>
                        Listado de negocios
                    </li>
                </a>
            `;

            // --- Grupo Calificaciones ---
            const bloqueCalificaciones = `
                <div class="mt-4 mb-2 pl-25 text-muted font-weight-700 text-12 text-uppercase" style="letter-spacing: 1px;">
                    Calificaciones
                </div>
                <a class="text-color font-weight-500 nav-item-plugin" href="${APP_URL}/reda/negocios/mis-calificaciones/qr">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-3">
                        <i class="fas fa-qrcode mr-3 text-18 align-middle"></i>
                        QR calificaciones
                    </li>
                </a>
                <a class="text-color font-weight-500 nav-item-plugin" href="${APP_URL}/reda/negocios/mis-calificaciones/listado">
                    <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-3">
                        <i class="fas fa-star mr-3 text-18 align-middle"></i>
                        Listado de calificaciones
                    </li>
                </a>
            `;

            const menuInyectar = bloqueNegocios + bloqueCalificaciones;

            // 2. Localizar la opción "Listings" (Alojamientos) por su enlace
            // y realizar la inserción dinámica
            const opcionReferencia = $('a[href*="/properties"]');

            $(function() {
                if (opcionReferencia.length) {
                    opcionReferencia.after(menuInyectar);
                } else {
                    // Opción de respaldo: Si no encuentra "properties",
                    // lo agrega al principio de la lista
                    $('.list-group-flush').prepend(menuInyectar);
                }
            });
        }
    })(jQuery);
}
menuLateralUsuario();
