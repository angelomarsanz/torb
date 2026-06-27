// packages/Reda/RedaAlojamiento/resources/js/vistas/experiencia/listadoCalificaciones.js

(function($) {
    "use strict";

    const containerId = '#listado_calificaciones_duenio';
    if ($(containerId).length) {
        $(function() {
            // Cargar nombres de comercios para el datalist (búsqueda inteligente)
            cargarSugerenciasBusqueda();

            // Si se desea que la búsqueda sea inmediata al seleccionar de la lista
            $('#input-busqueda-comercios').on('input', function() {
                const val = $(this).val();
                const options = $('#lista-nombres-comercios option');
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === val) {
                        $('#form-busqueda-comercios').submit();
                        break;
                    }
                }
            });

            // Animación al enviar el formulario manualmente
            $('#form-busqueda-comercios').on('submit', function() {
                if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                    window.RedaNotificaciones.esperar();
                }
            });
        });
    }

    /**
     * Obtiene los nombres de los comercios vía AJAX y los agrega al datalist.
     */
    function cargarSugerenciasBusqueda() {
        $.ajax({
            url: APP_URL + '/reda/negocios/mis-calificaciones/get-nombres-comercios',
            type: 'GET',
            success: function(res) {
                if (res.success && Array.isArray(res.respuesta)) {
                    const $datalist = $('#lista-nombres-comercios');
                    $datalist.empty();
                    res.respuesta.forEach(function(nombre) {
                        $datalist.append($('<option>').attr('value', nombre));
                    });
                }
            },
            error: function(err) {
                console.error("Error al cargar sugerencias de búsqueda", err);
            }
        });
    }

})(jQuery);
