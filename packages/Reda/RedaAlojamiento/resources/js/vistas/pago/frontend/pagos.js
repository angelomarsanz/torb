(function( $ ) {
    "use strict";

    const formId = '#payment-form';

    if ($(formId).length) {
        $(function() {
            /**
             * Escuchamos el evento submit de forma pasiva.
             * No llamamos a .valid() para no interferir con otros scripts.
             * Solo actuamos si el formulario realmente va a enviarse.
             */
            $(formId).on('submit', function(e) {
                const $form = $(this);
                
                // Si algo canceló el envío (como la validación original), no hacemos nada
                if (e.isDefaultPrevented()) {
                    return;
                }

                // Evitamos que el modal se muestre dos veces o bloquee reintentos legítimos
                if ($form.data('modal-mostrado') === true) {
                    return;
                }

                // Solo mostramos el modal si el formulario pasa la validación de JQuery
                // Pero lo hacemos de forma que no bloquee el hilo principal
                if ($form.valid()) {
                    $form.data('modal-mostrado', true);

                    if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
                        // Usamos un pequeño delay para asegurar que el submit del navegador ya inició
                        setTimeout(() => {
                            window.RedaNotificaciones.esperar();
                            
                            const $modal = $('#modal-notificacion');
                            $modal.addClass('modal-procesando');

                            const mensajeProcesando = window.RedaAlojamientoJson["Su reserva está siendo procesada"] || "Su reserva está siendo procesada";
                            $('#notificacion-mensaje').text(mensajeProcesando);
                        }, 50);
                    }
                }
            });
        });
    }
})(jQuery);
