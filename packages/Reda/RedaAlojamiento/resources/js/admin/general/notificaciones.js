/**
 * Objeto global para gestionar notificaciones y animaciones de carga del plugin Reda (Versión Admin - Bootstrap 5).
 */
window.RedaNotificaciones = {

    /**
     * Muestra una animación de espera (spinner) bloqueando la interacción.
     */
    esperar: function() {
        (function( $ ) {
            "use strict";
            const $modal = $('#modal-notificacion');
            if (!$modal.length) return;

            const $titulo = $('#notificacion-titulo');
            const $mensaje = $('#notificacion-mensaje');
            const $icono = $('#notificacion-icono');
            const $footer = $modal.find('.modal-footer');

            // Configuración para estado de carga
            $icono.html('<i class="fa fa-spinner fa-spin fa-4x text-success"></i>');
            $titulo.text(window.RedaAlojamientoJson["Por favor espere"] || "Por favor espere");
            $mensaje.text(window.RedaAlojamientoJson["Estamos procesando su solicitud..."] || "Estamos procesando su solicitud...");
            
            // Ocultamos el botón de aceptar y el botón de cerrar para que sea un bloqueo real
            $footer.addClass('d-none');
            $modal.find('.btn-close').addClass('d-none');

            // Evitar que se cierre al hacer clic fuera o presionar ESC
            // En Bootstrap 5 con jQuery bridge:
            $modal.modal({
                backdrop: 'static',
                keyboard: false
            }).modal('show');
        })(jQuery);
    },

    /**
     * Muestra un modal de notificación (Éxito, Error, Info).
     */
    notificar: function(titulo, mensaje, tipo = 'info', recargar = false) {
        (function( $ ) {
            "use strict";
            const $modal = $('#modal-notificacion');
            if (!$modal.length) return;

            const $titulo = $('#notificacion-titulo');
            const $mensaje = $('#notificacion-mensaje');
            const $icono = $('#notificacion-icono');
            const $footer = $modal.find('.modal-footer');

            // Restauramos controles ocultos por 'esperar'
            $footer.removeClass('d-none');
            $modal.find('.btn-close').removeClass('d-none');

            let iconoHtml = '';
            switch (tipo) {
                case 'exito':
                    iconoHtml = '<i class="fa fa-check-circle fa-4x text-success"></i>';
                    $titulo.text(titulo || (window.RedaAlojamiento?.general?.exito || "¡Éxito!"));
                    break;
                case 'error':
                    iconoHtml = '<i class="fa fa-times-circle fa-4x text-danger"></i>';
                    $titulo.text(titulo || (window.RedaAlojamiento?.general?.error || "Error"));
                    break;
                default:
                    iconoHtml = '<i class="fa fa-info-circle fa-4x text-primary"></i>';
                    $titulo.text(titulo || (window.RedaAlojamiento?.general?.notificacion || "Notificación"));
            }

            $icono.html(iconoHtml);
            $mensaje.html(mensaje);

            $modal.off('hidden.bs.modal').on('hidden.bs.modal', function () {
                if (recargar) location.reload();
            });

            // Si ya estaba abierto, solo actualizamos contenido, si no, lo mostramos
            $modal.modal('show');
        })(jQuery);
    },

    /**
     * Oculta el modal de notificación si está abierto.
     */
    ocultar: function() {
        (function( $ ) {
            "use strict";
            const $modal = $('#modal-notificacion');
            if ($modal.length) {
                $modal.modal('hide');
            }
        })(jQuery);
    }
};

/**
 * Muestra un modal de notificación moderno (Mantiene compatibilidad con funciones antiguas).
 */
window.mostrarNotificacion = (titulo, mensaje, tipo = 'info', recargar = false) => {
    window.RedaNotificaciones.notificar(titulo, mensaje, tipo, recargar);
}

// --- GESTIÓN DE BFCACHE (PARA ELIMINAR EL MODAL AL REGRESAR ATRÁS EN MÓVILES) ---
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.RedaNotificaciones.ocultar();
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
