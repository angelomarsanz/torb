(function( $ ) {
    "use strict";

    /**
     * Identificador del contenedor principal de la vista de calificación frontend.
     */
    const containerId = '#calificacion_experiencia_frontend';

    if ($(containerId).length) {
        console.log('Script para Calificación de Experiencia Frontend cargado correctamente');

        $(function() {
            /**
             * Estado local para las estrellas seleccionadas por el usuario.
             */
            let selectedStars = 0;

            // --- GESTIÓN INTERACTIVA DE ESTRELLAS DE CALIFICACIÓN ---

            /**
             * Efecto visual al pasar el mouse sobre las estrellas (hover).
             * Resalta temporalmente hasta la estrella señalada.
             */
            $('.star-item').on('mouseenter', function() {
                const val = $(this).data('value');
                pintarEstrellas(val);
            }).on('mouseleave', function() {
                // Restaura el estado a la calificación fijada previamente
                pintarEstrellas(selectedStars);
            });

            /**
             * Acción al hacer clic en una estrella para fijar la calificación.
             * Actualiza el input oculto y limpia mensajes de error previos.
             */
            $('.star-item').on('click', function() {
                selectedStars = $(this).data('value');
                $('#input-estrellas').val(selectedStars);
                $('#error-estrellas').addClass('d-none');
                pintarEstrellas(selectedStars);
            });

            /**
             * Función auxiliar para actualizar el estilo visual de las estrellas.
             * @param {number} num - Cantidad de estrellas a resaltar.
             */
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

            // --- CONTADOR DE CARACTERES PARA EL COMENTARIO ---

            /**
             * Actualiza dinámicamente el contador de caracteres del textarea.
             * Aplica color de error si se excede el límite de 1000.
             */
            $('#comentario').on('input', function() {
                const count = $(this).val().length;
                $('#char-count').text(`${count} / 1000`);
                if (count > 1000) {
                    $('#char-count').addClass('text-danger');
                } else {
                    $('#char-count').removeClass('text-danger');
                }
            });

            // --- PROCESAMIENTO DEL ENVÍO DE CALIFICACIÓN (AJAX) ---

            /**
             * Ejecuta la petición AJAX para guardar la calificación en el servidor.
             * Sigue la estructura de respuesta estandarizada del plugin REDA.
             * @param {string} formData - Datos serializados del formulario.
             * @returns {Promise} - Resolución con la respuesta del servidor.
             */
            const guardarCalificacionAjax = (formData) => {
                return new Promise((resolve) => {
                    (function( $ ) {
                        $.ajax({
                            url: $('#form-calificacion').attr('action'),
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            success: function(data) {
                                resolve(data);
                            },
                            error: function (x, xs, xt) {
                                // 1. Intentamos obtener el JSON de error enviado por el servidor (ej: 400 o 500)
                                let respuestaServidor = {};
                                try {
                                    respuestaServidor = JSON.parse(x.responseText);
                                } catch (e) {
                                    respuestaServidor = {};
                                }
                                console.log('Error detallado del servidor:', respuestaServidor);

                                const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                                const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                                // 2. Construimos un objeto de respuesta amigable siguiendo GEMINI.md
                                let respuesta = {
                                    'success': false,
                                    'message' : window.RedaAlojamientoJson["Error guardando calificación"] || 'Error guardando calificación',
                                    'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                                    'respuesta': respuestaServidor.respuesta || '',
                                    'code': x.status !== 0 ? x.status : 504,
                                };
                                resolve(respuesta);
                            }
                        });
                    })(jQuery);
                });
            };

            /**
             * Manejador del evento submit del formulario de calificación.
             * Valida que se haya seleccionado al menos una estrella antes de enviar.
             */
            $('#form-calificacion').on('submit', async function(e) {
                e.preventDefault();

                // Validación de seguridad: debe existir una puntuación mayor a cero
                if (selectedStars === 0) {
                    $('#error-estrellas').removeClass('d-none');
                    $('html, body').animate({
                        scrollTop: $('.rating-stars-container').offset().top - 150
                    }, 500);
                    return;
                }

                // UI: Feedback de carga (spinner y deshabilitar botón)
                const $btn = $('#btn-enviar-calificacion');
                $btn.prop('disabled', true);
                $btn.find('.fa-spinner').removeClass('d-none');
                $btn.find('.btn-text').addClass('d-none');

                const formData = $(this).serialize();
                const response = await guardarCalificacionAjax(formData);

                if (response.success) {
                    // Muestra modal de agradecimiento si todo salió bien
                    $('#modalExitoCalificacion').modal('show');
                } else {
                    // Muestra el error mediante el sistema de notificaciones del plugin
                    if (typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion(window.RedaAlojamientoJson["Error"] || "Error", response.mensaje_usuario, 'error');
                    } else {
                        alert(response.mensaje_usuario);
                    }
                    
                    // Restaura el botón para permitir un nuevo intento
                    $btn.prop('disabled', false);
                    $btn.find('.fa-spinner').addClass('d-none');
                    $btn.find('.btn-text').removeClass('d-none');
                }
            });
        });
    }
})(jQuery);
