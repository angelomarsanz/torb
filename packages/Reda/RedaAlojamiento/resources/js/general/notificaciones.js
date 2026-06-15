/**
 * Objeto global para gestionar notificaciones y animaciones de carga del plugin Reda.
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
            
            // Ocultamos el botón de aceptar y la X de cerrar para que sea un bloqueo real
            $footer.addClass('d-none');
            $modal.find('.close').addClass('d-none');

            // Evitar que se cierre al hacer clic fuera o presionar ESC
            $modal.modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
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
            $modal.find('.close').removeClass('d-none');

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

            // Si ya estaba abierto (por ejemplo desde esperar), solo actualizamos contenido
            if (($modal.data('bs.modal') || {})._isShown) {
                // Ya se ve
            } else {
                $modal.modal('show');
            }
        })(jQuery);
    }
};

// Mantener compatibilidad con funciones globales previas si existen
window.mostrarNotificacion = (titulo, mensaje, tipo, recargar) => {
    window.RedaNotificaciones.notificar(titulo, mensaje, tipo, recargar);
};

/**
 * Muestra un modal de confirmación.
 */
window.mostrarConfirmacion = (mensaje, callback, titulo = '', textoBoton = '') => {
    (function( $ ) {
        "use strict";
        const $modal = $('#modal-confirmacion');
        const $btnConfirmar = $('#btn-confirmar-si');

        $('#confirmacion-mensaje').html(mensaje);
        if (titulo) $('#confirmacion-titulo').text(titulo);
        if (textoBoton) $btnConfirmar.find('.btn-text').text(textoBoton);

        $btnConfirmar.off('click').on('click', async function() {
            const $btn = $(this);
            $btn.prop('disabled', true);
            $btn.find('.fa-spinner').removeClass('d-none');

            if (callback && typeof callback === 'function') {
                await callback();
            }

            $btn.prop('disabled', false);
            $btn.find('.fa-spinner').addClass('d-none');
            $modal.modal('hide');
        });

        $modal.modal('show');
    })(jQuery);
}

// --- GESTIÓN DE CLICKS GLOBAL PARA EL PLUGIN (ANIMACIÓN DE CARGA) ---
$(function() {
    $(document).on('click', 'a[href*="/reda/negocios"]', function(e) {
        // Solo si es un link interno y no se está abriendo en pestaña nueva
        if (this.href && !this.target && !e.ctrlKey && !e.metaKey) {
             // Evitamos disparar si es el mismo ID de página (anclas #)
             if (!this.href.includes(window.location.pathname + '#')) {
                window.RedaNotificaciones.esperar();
            }
        }
    });
});
