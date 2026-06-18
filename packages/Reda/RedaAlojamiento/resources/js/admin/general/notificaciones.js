/**
 * Muestra un modal de notificación moderno.
 * @param {string} titulo - Título del modal.
 * @param {string} mensaje - Mensaje a mostrar (soporta <br>).
 * @param {string} tipo - 'exito', 'error', 'info'.
 * @param {boolean} recargar - Si es true, recarga la página al cerrar el modal.
 */
window.mostrarNotificacion = (titulo, mensaje, tipo = 'info', recargar = false) => {
    (function( $ ) {
        "use strict";
        const $modal = $('#modal-notificacion');
        if (!$modal.length) {
            console.error('No se encontró el modal de notificación (#modal-notificacion) en el DOM.');
            return;
        }

        const $titulo = $('#notificacion-titulo');
        const $mensaje = $('#notificacion-mensaje');
        const $icono = $('#notificacion-icono');

        // Configuración de iconos y colores según el tipo
        let iconoHtml = '';
        switch (tipo) {
            case 'exito':
                iconoHtml = '<i class="fa fa-check-circle fa-4x text-success"></i>';
                $titulo.text(titulo || window.RedaAlojamiento.general.exito);
                break;
            case 'error':
                iconoHtml = '<i class="fa fa-times-circle fa-4x text-danger"></i>';
                $titulo.text(titulo || window.RedaAlojamiento.general.error);
                break;
            default:
                iconoHtml = '<i class="fa fa-info-circle fa-4x text-primary"></i>';
                $titulo.text(titulo || window.RedaAlojamiento.general.notificacion);
        }

        $icono.html(iconoHtml);
        $mensaje.html(mensaje);

        // Manejo de la recarga al cerrar
        $modal.off('hidden.bs.modal').on('hidden.bs.modal', function () {
            if (recargar) {
                location.reload();
            }
        });

        $modal.modal('show');
    })(jQuery);
}

// --- GESTIÓN DE BFCACHE (PARA ELIMINAR EL MODAL AL REGRESAR ATRÁS EN MÓVILES) ---
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        (function( $ ) {
            "use strict";
            const $modal = $('#modal-notificacion');
            if ($modal.length) {
                $modal.modal('hide');
            }
        })(jQuery);
    }
});

/**
 * Muestra un modal de confirmación.
 * @param {string} mensaje - Mensaje de la pregunta.
 * @param {function} callback - Función que se ejecuta si el usuario confirma.
 * @param {string} titulo - Título opcional.
 * @param {string} textoBoton - Texto del botón de acción (ej: 'Eliminar').
 */
window.mostrarConfirmacion = (mensaje, callback, titulo = '', textoBoton = '') => {
    (function( $ ) {
        "use strict";
        const $modal = $('#modal-confirmacion');
        const $btnConfirmar = $('#btn-confirmar-si');

        $('#confirmacion-mensaje').html(mensaje);
        if (titulo) $('#confirmacion-titulo').text(titulo);
        if (textoBoton) $btnConfirmar.find('.btn-text').text(textoBoton);

        // Limpiar eventos previos y asignar el nuevo callback
        $btnConfirmar.off('click').on('click', async function() {
            // Mostrar spinner en el botón de confirmación
            const $btn = $(this);
            $btn.prop('disabled', true);
            $btn.find('.fa-spinner').removeClass('d-none');

            if (callback && typeof callback === 'function') {
                await callback();
            }

            // Restaurar botón y cerrar
            $btn.prop('disabled', false);
            $btn.find('.fa-spinner').addClass('d-none');
            $modal.modal('hide');
        });

        $modal.modal('show');
    })(jQuery);
}
