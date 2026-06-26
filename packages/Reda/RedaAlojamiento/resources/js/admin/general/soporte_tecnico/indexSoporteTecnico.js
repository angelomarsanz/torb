(function( $ ) {
    "use strict";

    /**
     * Script para el módulo de Soporte Técnico (Admin) - Vista Index
     * Maneja la inicialización de componentes de la interfaz de usuario para el listado de tickets.
     */
    const containerId = '#index_soporte_tecnico';

    /**
     * Inicializa los componentes necesarios en la vista
     */
    const inicializarSoporteTecnico = () => {
        // Inicialización de tooltips para las acciones (Ver detalle, etc.)
        if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover'
            });
        }

        // Inicialización de popovers para info técnica
        if ($.fn.popover) {
            $('[data-toggle="popover"]').popover({
                html: true,
                trigger: 'hover',
                placement: 'top',
                template: '<div class="popover shadow-sm border-primary" role="tooltip"><div class="arrow"></div><h3 class="popover-header bg-primary text-white border-0"></h3><div class="popover-body p-3"></div></div>'
            });
        }

        // Evento para mostrar animación de espera al ver detalle de ticket
        $(document).on('click', '.btn-ver-ticket', function(e) {
            // Solo si es un link con href válido y no se abre en pestaña nueva
            if (this.href && !this.target && !e.ctrlKey && !e.metaKey) {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }
            }
        });

        // Log de carga exitosa
        console.log(window.RedaAlojamientoJson["Módulo de Soporte Técnico (Admin) cargado correctamente"] || "Módulo de Soporte Técnico (Admin) cargado correctamente.");
    };

    // Ejecutar cuando el DOM esté listo y el contenedor exista
    if ($(containerId).length) {
        $(function() {
            inicializarSoporteTecnico();
        });
    }

})(jQuery);

