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

        // Abrir modal de búsqueda
        $(document).on('click', '.btn-abrir-busqueda', function(e) {
            e.preventDefault();
            $('#modal_busqueda_soporte').modal('show');
        });

        // Búsqueda puntual (ID o Nombre)
        $(document).on('click', '.btn-buscar-puntual', function(e) {
            e.preventDefault();
            const valor = $('#input_puntual').val().trim();
            const tipo = $(this).data('tipo');

            if (!valor) {
                $('#input_puntual').focus();
                return;
            }

            // Limpiar campos de búsqueda avanzados para que sea una búsqueda puntual real
            $('#form_busqueda_soporte').find('select, input[type="date"]').val('');
            $('#search_id').val('');
            $('#search_nombre').val('');

            if (tipo === 'id') {
                $('#search_id').val(valor);
            } else {
                $('#search_nombre').val(valor);
            }

            // Mostrar animación e iniciar búsqueda (submit del form)
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }
            $('#form_busqueda_soporte').submit();
        });

        // Animación de espera al enviar el formulario de búsqueda general
        $(document).on('submit', '#form_busqueda_soporte', function() {
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                window.RedaNotificaciones.esperar();
            }
        });

        // Evento para mostrar animación de espera al ver detalle de ticket
        $(document).on('click', '.btn-ver-ticket', function(e) {
            // Solo si es un link con href válido y no se abre en pestaña nueva
            if (this.href && !this.target && !e.ctrlKey && !e.metaKey) {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }
            }
        });

        // Animación de espera al hacer clic en los controles de paginación
        $(document).on('click', '.pagination a', function(e) {
            // Solo si es un link con href válido
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

