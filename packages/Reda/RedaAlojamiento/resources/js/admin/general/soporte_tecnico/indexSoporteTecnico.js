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

            // Limpiar el formulario antes de mostrar el modal
            const $form = $('#form_busqueda_soporte');
            if ($form.length) {
                // Limpiamos todos los inputs de texto, número y fecha
                $form.find('input[type="text"], input[type="number"], input[type="date"]').val('');
                
                // Limpiamos los selects (y disparamos change por si se usa Select2)
                $form.find('select').val('').trigger('change');
            }

            $('#modal_busqueda_soporte').modal('show');
        });

        /**
         * Lógica de filtros excluyentes:
         * Si el usuario escribe en ID, se limpian los demás.
         * Si el usuario escribe/selecciona en otros, se limpia el ID.
         */
        
        // Al escribir en el ID
        $(document).on('input', '#search_id', function() {
            if ($(this).val().trim() !== '') {
                const $form = $('#form_busqueda_soporte');
                // Limpiamos los otros inputs
                $form.find('input[name="nombre_usuario"], input[name="nombre_comercio"], input[type="date"]').val('');
                // Limpiamos los selects
                $form.find('select').val('').trigger('change');
            }
        });

        // Al interactuar con cualquier otro campo que no sea ID
        $(document).on('input change', '#form_busqueda_soporte input:not(#search_id), #form_busqueda_soporte select', function() {
            const $valor = $(this).val();
            // Si el campo tiene valor, limpiamos el ID
            if ($valor && $valor !== '') {
                $('#search_id').val('');
            }
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
