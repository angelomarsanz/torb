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
                                <button class="btn btn-success btn-block text-14 font-weight-700">
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
        console.log('Se cargó el archivo javascript mensajes.js')
        if ($('#messages').length && $('#booking').length) {
            console.log('Dentro de la vista de bandeja de entrada (inbox)');
            // Primera ejecución al cargar la página
            inyectarCajaMediacionReda();

            // Usamos MutationObserver para detectar cambios en el contenedor #booking
            // que se actualiza vía AJAX en el archivo original inbox.js
            const targetNode = document.getElementById('booking');
            if (targetNode) {
                const observer = new MutationObserver((mutationsList) => {
                    for (let mutation of mutationsList) {
                        if (mutation.type === 'childList') {
                            inyectarCajaMediacionReda();
                        }
                    }
                });
                // Observamos cambios en los hijos del contenedor #booking
                observer.observe(targetNode, { childList: true, subtree: true });
            }
        }
    });

})(jQuery);
