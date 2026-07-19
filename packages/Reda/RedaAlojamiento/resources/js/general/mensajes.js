import { mediacionSvg } from './iconos';

(function($) {
    "use strict";

    /**
     * Inyecta el cuadro de mediación en la barra lateral de la reserva.
     * Se busca la sección de "Pago" para insertar el cuadro justo antes.
     */
    const inyectarCajaMediacionReda = () => {
        const containerId = '#booking';
        const targetContainer = $(containerId);

        if (targetContainer.length) {
            // Evitar duplicados si ya existe el cuadro
            if ($('#caja-mediacion-reda').length) return;

            // Buscar el encabezado de "Pago" (Payment).
            // Se usa la traducción del archivo es.json del proyecto original o el fallback.
            const paymentText = window.RedaAlojamientoJson["Pago"] || "Pago";
            const paymentHeader = targetContainer.find('h5:contains("' + paymentText + '")');

            if (paymentHeader.length) {
                // Obtener los IDs dinámicamente de los elementos existentes en la vista original
                // El botón de enviar mensaje (.send-btn) ya contiene el booking_id y el receiver_id (la otra parte)
                const sendBtn = $('.send-btn');
                const bookingId = sendBtn.attr('data-booking') || '';
                const otherUserId = sendBtn.attr('data-receiver') || '';
                const myUserId = window.USER_ID || '';

                /**
                 * Heurística para identificar Anfitrión (host_id) vs Turista (user_id):
                 * Intentamos determinar el rol del usuario actual basándonos en el menú lateral.
                 */
                let anfitrionId = '';
                let turistaId = '';

                // Miramos si en el sidebar el link activo es Trips o Bookings
                // vRent logic: data-receiver en el Inbox es siempre la contraparte.
                const isHostView = $('.active-sidebar:contains("' + (window.RedaAlojamientoJson["Mis Reservas"] || "Bookings") + '")').length > 0;
                const isTouristView = $('.active-sidebar:contains("' + (window.RedaAlojamientoJson["Mis Viajes"] || "Trips") + '")').length > 0;

                if (isHostView) {
                    anfitrionId = myUserId;
                    turistaId = otherUserId;
                } else if (isTouristView) {
                    anfitrionId = otherUserId;
                    turistaId = myUserId;
                } else {
                    // Fallback si no hay sidebar activo (ej. navegación directa)
                    // Por ahora los dejamos asignados para que el modal los procese
                    anfitrionId = otherUserId; 
                    turistaId = myUserId;
                }

                if (bookingId) {
                    targetContainer.attr('data-reservacion-id', bookingId);
                    targetContainer.attr('data-anfitrion-id', anfitrionId);
                    targetContainer.attr('data-turista-id', turistaId);
                }

                const mediacionText = window.RedaAlojamientoJson["Mediación"] || "Mediación";
                const sinMediacionText = window.RedaAlojamientoJson["Sin mediación activa"] || "Sin mediación activa";
                const ayudaText = window.RedaAlojamientoJson["Si tienes problema con esta reserva, puedes solicitar ayuda a nuestro equipo"] || "Si tienes problema con esta reserva, puedes solicitar ayuda a nuestro equipo";
                const solicitarText = window.RedaAlojamientoJson["Solicitar mediación"] || "Solicitar mediación";

                const cajaHtml = `
                    <div id="caja-mediacion-reda" class="row mt-3 mb-1">
                        <div class="col-md-12">
                            <div class="border rounded p-3 bg-light shadow-sm">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="text-success mr-2 d-flex align-items-center">
                                        ${mediacionSvg}
                                    </div>
                                    <h5 class="text-16 font-weight-700 m-0">${mediacionText}</h5>
                                </div>
                                <h6 class="text-14 font-weight-700 mb-1">${sinMediacionText}</h6>
                                <p class="text-12 text-muted mb-3">${ayudaText}</p>
                                <button id="btn-solicitar-mediacion-reda" 
                                    class="btn btn-success btn-block text-14 font-weight-700"
                                    data-reservacion-id="${bookingId}"
                                    data-anfitrion-id="${anfitrionId}"
                                    data-turista-id="${turistaId}">
                                    ${solicitarText}
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                // Insertar antes de la fila que contiene el encabezado de Pago
                paymentHeader.closest('.row').before(cajaHtml);
            }
        }
    };

    $(function() {
        // Verificar si estamos en la vista de bandeja de entrada (inbox)
        if ($('#messages').length && $('#booking').length) {
            inyectarCajaMediacionReda();

            const targetNode = document.getElementById('booking');
            if (targetNode) {
                const observer = new MutationObserver((mutationsList) => {
                    for (let mutation of mutationsList) {
                        if (mutation.type === 'childList') {
                            inyectarCajaMediacionReda();
                        }
                    }
                });
                observer.observe(targetNode, { childList: true, subtree: true });
            }
        }
    });

})(jQuery);
