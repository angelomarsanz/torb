export const menuLateralUsuario = () =>
{
    (function( $ ) {
        "use strict";
        $(function() {
            // 1. Verificar si el contenedor específico de tu vista existe
            // (Asegúrate de que en tu vista de plugin tengas un <div id="index_experiencias"></div>)
            const containerId = '#reviewIcon';

            if ($(containerId).length) {

                // 2. Definir la nueva opción de "Negocios"
                const nuevaOpcionNegocios = `
                    <a class="text-color font-weight-500 mt-1 nav-item-plugin" href="https://pruebas.redetronic.com/reda/index-experiencias">
                        <li class="list-group-item vbg-default-hover pl-25 border-0 text-15 p-4">
                            <i class="fas fa-briefcase mr-3 text-18 align-middle"></i>
                            Negocios
                        </li>
                    </a>
                `;
                // 3. Localizar la opción "Listings" (Alojamientos) por su enlace
                // y realizar la inserción dinámica
                const opcionReferencia = $('a[href*="/properties"]');

                if (opcionReferencia.length) {
                    opcionReferencia.after(nuevaOpcionNegocios);
                } else {
                    // Opción de respaldo: Si no encuentra "properties",
                    // lo agrega al principio de la lista
                    $('.list-group-flush').prepend(nuevaOpcionNegocios);
                }
            }
        });
    })(jQuery);
}
menuLateralUsuario();
