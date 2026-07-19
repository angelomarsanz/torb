import { mediacionSvg } from './iconos';

(function($) {
    "use strict";

    /**
     * Inyecta el cuadro de mediación en la barra lateral de la reserva.
     */
    const inyectarCajaMediacionReda = () => {
        const containerId = '#booking';
        const targetContainer = $(containerId);

        if (targetContainer.length) {
            // Si ya existe la caja, no re-inyectamos, pero podríamos querer actualizarla si cambia el booking.
            // Para simplicidad en el inbox donde el booking puede cambiar al cambiar de chat:
            if ($('#caja-mediacion-reda').length) {
                // Si el booking ID en el contenedor no coincide con el de la caja, la quitamos para re-inyectar.
                const currentBookingId = $('.send-btn').attr('data-booking') || '';
                if ($('#btn-solicitar-mediacion-reda').attr('data-reservacion-id') !== currentBookingId && 
                    !$('#caja-mediacion-reda').find('.info-mediacion-activa').length) {
                    $('#caja-mediacion-reda').remove();
                } else if ($('#caja-mediacion-reda').attr('data-booking-id') !== currentBookingId) {
                     $('#caja-mediacion-reda').remove();
                } else {
                    return;
                }
            }

            const paymentText = window.RedaAlojamientoJson["Pago"] || "Pago";
            const paymentHeader = targetContainer.find('h5:contains("' + paymentText + '")');

            if (paymentHeader.length) {
                const sendBtn = $('.send-btn');
                const bookingId = sendBtn.attr('data-booking') || '';
                const otherUserId = sendBtn.attr('data-receiver') || '';
                const myUserId = window.USER_ID || '';

                if (!bookingId) return;

                let anfitrionId = '';
                let turistaId = '';

                const isHostView = $('.active-sidebar:contains("' + (window.RedaAlojamientoJson["Mis Reservas"] || "Bookings") + '")').length > 0;
                const isTouristView = $('.active-sidebar:contains("' + (window.RedaAlojamientoJson["Mis Viajes"] || "Trips") + '")').length > 0;

                if (isHostView) {
                    anfitrionId = myUserId;
                    turistaId = otherUserId;
                } else if (isTouristView) {
                    anfitrionId = otherUserId;
                    turistaId = myUserId;
                } else {
                    anfitrionId = otherUserId; 
                    turistaId = myUserId;
                }

                if (bookingId) {
                    targetContainer.attr('data-reservacion-id', bookingId);
                    targetContainer.attr('data-anfitrion-id', anfitrionId);
                    targetContainer.attr('data-turista-id', turistaId);
                }

                const mediacionText = window.RedaAlojamientoJson["Mediación"] || "Mediación";
                
                // Primero inyectamos la caja básica (estado vacío o cargando)
                const cajaHtml = `
                    <div id="caja-mediacion-reda" class="row mt-3 mb-1" data-booking-id="${bookingId}">
                        <div class="col-md-12">
                            <div class="border rounded p-3 bg-light shadow-sm content-caja-mediacion">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="text-success mr-2 d-flex align-items-center">
                                        ${mediacionSvg}
                                    </div>
                                    <h5 class="text-16 font-weight-700 m-0">${mediacionText}</h5>
                                </div>
                                <div class="caja-contenido-dinamico">
                                    <div class="text-center"><div class="spinner-border spinner-border-sm text-success" role="status"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                paymentHeader.closest('.row').before(cajaHtml);

                // Consultar si existe mediación
                $.ajax({
                    url: APP_URL + '/reda/disputas/check/' + bookingId,
                    type: 'GET',
                    success: function(response) {
                        const container = $('#caja-mediacion-reda').find('.caja-contenido-dinamico');
                        if (response.exists) {
                            const d = response.data;
                            const htmlActiva = `
                                <div class="info-mediacion-activa">
                                    <p class="text-14 mb-1"><strong>Proceso de mediación con ID #${d.id}</strong></p>
                                    <p class="text-12 mb-1">Fecha: ${d.fecha}</p>
                                    <p class="text-12 mb-1">Estatus: ${d.estado}</p>
                                    <p class="text-12 mb-0">Paso: ${d.paso_actual}</p>
                                </div>
                            `;
                            container.html(htmlActiva);
                        } else {
                            const sinMediacionText = window.RedaAlojamientoJson["Sin mediación activa"] || "Sin mediación activa";
                            const ayudaText = window.RedaAlojamientoJson["Si tienes problema con esta reserva, puedes solicitar ayuda a nuestro equipo"] || "Si tienes problema con esta reserva, puedes solicitar ayuda a nuestro equipo";
                            const solicitarText = window.RedaAlojamientoJson["Solicitar mediación"] || "Solicitar mediación";

                            const htmlSolicitar = `
                                <h6 class="text-14 font-weight-700 mb-1">${sinMediacionText}</h6>
                                <p class="text-12 text-muted mb-3">${ayudaText}</p>
                                <button id="btn-solicitar-mediacion-reda" 
                                    class="btn btn-success btn-block text-14 font-weight-700"
                                    data-reservacion-id="${bookingId}"
                                    data-anfitrion-id="${anfitrionId}"
                                    data-turista-id="${turistaId}">
                                    ${solicitarText}
                                </button>
                            `;
                            container.html(htmlSolicitar);
                        }
                    },
                    error: function() {
                        $('#caja-mediacion-reda').find('.caja-contenido-dinamico').html('<p class="text-12 text-danger">Error al cargar mediación</p>');
                    }
                });
            }
        }
    };

    /**
     * Carga e inyecta el modal de mediación si no existe.
     */
    const cargarModalMediacion = () => {
        if ($('#modal-mediacion-reda').length) return;

        $.ajax({
            url: APP_URL + '/reda/disputas/get-modal',
            type: 'GET',
            success: function(html) {
                $('body').append(html);
                configurarEventosModal();
            }
        });
    };

    /**
     * Configura los eventos del formulario dentro del modal.
     */
    const configurarEventosModal = () => {
        // Manejo de nombre de archivos en el input custom-file
        $(document).on('change', '#documentos', function() {
            let files = $(this)[0].files;
            let label = files.length > 1 ? files.length + ' archivos seleccionados' : files[0].name;
            $(this).next('.custom-file-label').html(label);
        });

        // Envío del formulario
        $(document).on('submit', '#form-mediacion-reda', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = $('#btn-enviar-mediacion');
            const spinner = btn.find('.spinner-border');

            const formData = new FormData(this);

            btn.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: APP_URL + '/reda/disputas/store',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#modal-mediacion-reda').modal('hide');
                        if (typeof swal !== 'undefined') {
                            swal("¡Éxito!", response.message, "success");
                        } else {
                            alert(response.message);
                        }
                        // Limpiar formulario
                        form[0].reset();
                        form.find('.custom-file-label').html('Elegir archivos');
                        
                        // Actualizar la caja de mediación después de crear una exitosamente
                        inyectarCajaMediacionReda();
                    }
                },
                error: function(xhr) {
                    let msg = "Hubo un error al procesar su solicitud.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof swal !== 'undefined') {
                        swal("Error", msg, "error");
                    } else {
                        alert(msg);
                    }
                },
                complete: function() {
                    btn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });
    };

    $(function() {
        if ($('#messages').length && $('#booking').length) {
            inyectarCajaMediacionReda();
            cargarModalMediacion();

            // Abrir modal al hacer clic
            $(document).on('click', '#btn-solicitar-mediacion-reda', function() {
                const btn = $(this);
                const bookingId = btn.attr('data-reservacion-id');
                const anfitrionId = btn.attr('data-anfitrion-id');
                const turistaId = btn.attr('data-turista-id');

                $('#reda-booking-id').val(bookingId);
                $('#reda-anfitrion-id').val(anfitrionId);
                $('#reda-turista-id').val(turistaId);

                $('#modal-mediacion-reda').modal('show');
            });

            const targetNode = document.getElementById('booking');
            if (targetNode) {
                const observer = new MutationObserver((mutationsList) => {
                    for (let mutation of mutationsList) {
                        if (mutation.type === 'childList') {
                            // Usamos un pequeño delay para asegurar que el contenido se ha actualizado (especialmente send-btn)
                            setTimeout(inyectarCajaMediacionReda, 100);
                        }
                    }
                });
                observer.observe(targetNode, { childList: true, subtree: true });
            }
        }
    });

})(jQuery);
