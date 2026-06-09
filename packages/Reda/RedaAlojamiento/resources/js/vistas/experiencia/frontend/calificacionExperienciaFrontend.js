(function( $ ) {
    "use strict";

    const containerId = '#calificacion_experiencia_frontend';

    if ($(containerId).length) {
        console.log('Script para Calificación de Experiencia Frontend cargado correctamente');

        $(function() {
            let selectedStars = 0;

            // --- LÓGICA DE ESTRELLAS ---

            // Hover efecto
            $('.star-item').on('mouseenter', function() {
                const val = $(this).data('value');
                pintarEstrellas(val);
            }).on('mouseleave', function() {
                pintarEstrellas(selectedStars);
            });

            // Click para fijar valor
            $('.star-item').on('click', function() {
                selectedStars = $(this).data('value');
                $('#input-estrellas').val(selectedStars);
                $('#error-estrellas').addClass('d-none');
                pintarEstrellas(selectedStars);
            });

            function pintarEstrellas(num) {
                $('.star-item').each(function() {
                    const val = $(this).data('value');
                    if (val <= num) {
                        $(this).removeClass('far').addClass('fas active');
                    } else {
                        $(this).removeClass('fas active').addClass('far');
                    }
                });
            }

            // --- CONTADOR DE CARACTERES ---
            $('#comentario').on('input', function() {
                const count = $(this).val().length;
                $('#char-count').text(`${count} / 1000`);
                if (count > 1000) {
                    $('#char-count').addClass('text-danger');
                } else {
                    $('#char-count').removeClass('text-danger');
                }
            });

            // --- ENVÍO DEL FORMULARIO ---

            const guardarCalificacionAjax = (formData) => {
                return new Promise((resolve) => {
                    $.ajax({
                        url: $('#form-calificacion').attr('action'),
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: (data) => resolve(data),
                        error: function (x, xs, xt) {
                            let respuestaServidor = {};
                            try {
                                respuestaServidor = JSON.parse(x.responseText);
                            } catch (e) {
                                respuestaServidor = {};
                            }
                            console.log('respuestaServidor', respuestaServidor);

                            const mensajeErrorBase = window.RedaAlojamientoJson?.["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                            const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                            resolve({
                                'success': false,
                                'message' : 'Error guardando calificación',
                                'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                'respuesta': respuestaServidor.respuesta || '',
                                'code': x.status !== 0 ? x.status : 504,
                            });
                        }
                    });
                });
            };

            $('#form-calificacion').on('submit', async function(e) {
                e.preventDefault();

                // Validación manual de estrellas
                if (selectedStars === 0) {
                    $('#error-estrellas').removeClass('d-none');
                    $('html, body').animate({
                        scrollTop: $('.rating-stars-container').offset().top - 150
                    }, 500);
                    return;
                }

                const $btn = $('#btn-enviar-calificacion');
                $btn.prop('disabled', true);
                $btn.find('.fa-spinner').removeClass('d-none');
                $btn.find('.btn-text').addClass('d-none');

                const formData = $(this).serialize();
                const response = await guardarCalificacionAjax(formData);

                if (response.success) {
                    $('#modalExitoCalificacion').modal('show');
                } else {
                    alert(response.mensaje_usuario);
                    $btn.prop('disabled', false);
                    $btn.find('.fa-spinner').addClass('d-none');
                    $btn.find('.btn-text').removeClass('d-none');
                }
            });
        });
    }
})(jQuery);
