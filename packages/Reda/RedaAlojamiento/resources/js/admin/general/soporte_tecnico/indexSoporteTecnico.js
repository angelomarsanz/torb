(function( $ ) {
    "use strict";

    /**
     * Script para el módulo de Soporte Técnico (Admin) - Vista Index
     */
    const containerId = '#index_soporte_tecnico';

    if ($(containerId).length) {
        $(function() {
            // Inicialización de tooltips para las acciones
            $('[data-toggle="tooltip"]').tooltip();

            console.log(window.RedaAlojamientoJson["Módulo de Soporte Técnico (Admin) cargado correctamente"] || "Módulo de Soporte Técnico (Admin) cargado correctamente.");
        });
    }

})(jQuery);
