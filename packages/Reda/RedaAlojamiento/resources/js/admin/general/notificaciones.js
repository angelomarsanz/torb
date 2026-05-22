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
