/**
 * Función que realiza la petición para guardar una nueva categoría de negocio.
 * @param {string} url - URL del endpoint.
 * @param {object} formData - Datos serializados del formulario.
 * @returns {Promise}
 */
const storeOpcionTipoNegocio = (url, formData) => {
    return new Promise((resolve) => {
        (function( $ ) {
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(data) {
                    console.log('storeOpcionTipoNegocio, data:', data);
                    resolve(data);
                },
                error: function (x, xs, xt) {
                    let respuestaServidor = {};
                    try {
                        respuestaServidor = JSON.parse(x.responseText);
                    } catch (e) {
                        respuestaServidor = {};
                    }

                    const mensajeErrorBase = window.RedaAlojamiento?.general?.error_en_el_servidor_de_Torbian || 'Error en el servidor de Torbian';
                    const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';

                    resolve({
                        'success': false,
                        'message' : 'Error guardando categoría',
                        'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}${detalleError}`,
                        'respuesta': respuestaServidor.respuesta || '',
                        'code': x.status !== 0 ? x.status : 504,
                    });
                }
            })
        })(jQuery);
    });
}

(function( $ ) {
    "use strict";
    const containerId = '#opciones_tipos_de_negocios';
    if ($(containerId).length) {
        console.log('Script para "Opciones Tipo de Negocios" cargado correctamente.');
        $(function() {
            // Abrir modal al hacer clic en "Agregar Nueva"
            $(document).on('click', '#btn-add-category', function(e) {
                e.preventDefault();
                $('#form-add-category')[0].reset();

                const $btn = $('#btn-save-category');
                $btn.prop('disabled', false);
                $btn.find('.btn-text').text(window.RedaAlojamiento.general.guardar);
                $btn.find('.fa-spinner').addClass('d-none');

                $('#modal-add-category').modal('show');
            });

            // Manejo del envío del formulario mediante Ajax
            $('#form-add-category').on('submit', async function(e) {
                e.preventDefault();

                const $form = $(this);
                const $btn = $('#btn-save-category');
                const clave = $('#clave').val().trim();
                const nombre = $('#nombre').val().trim();

                // Validación básica manual (aunque el HTML5 tiene required)
                if (clave === '' || nombre === '') {
                    alert(window.RedaAlojamiento.general.ambos_campos_son_obligatorios);
                    return;
                }

                // Bloqueamos el botón y mostramos spinner
                $btn.prop('disabled', true);
                $btn.find('.btn-text').text(window.RedaAlojamiento.general.guardando);
                $btn.find('.fa-spinner').removeClass('d-none');

                const response = await storeOpcionTipoNegocio($form.attr('action'), $form.serialize());

                console.log('storeOpcionTipoNegocio, response: ', response)

                const mensajeRaw = response.mensaje_usuario || (response.success ? response.message : 'Error inesperado en el servidor') || '';
                const mensajeFinal = mensajeRaw.replace(/<br\s*\/?>/gi, '\n');

                if (response.success) {
                    $('#modal-add-category').modal('hide');
                    alert(mensajeFinal);
                    location.reload();
                } else {
                    $('#modal-add-category').modal('hide');
                    alert(mensajeFinal);
                }
                $btn.prop('disabled', false);
                $btn.find('.btn-text').text(window.RedaAlojamiento.general.guardar);
                $btn.find('.fa-spinner').addClass('d-none');
            });

            // Opcional: Limpiar espacios en la clave mientras el usuario escribe
            $('#clave').on('input', function() {
                $(this).val($(this).val().toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, ''));
            });

            console.log('Módulo de Opciones de Negocios inicializado.');
        });
    }
})(jQuery);
