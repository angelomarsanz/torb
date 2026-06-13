(function( $ ) {
    "use strict";

    const containerId = '#listado_productos_servicios';

    if ($(containerId).length) {
        console.log('Script para Listado de Productos y Servicios cargado correctamente');

        /**
         * Maneja el truncamiento y expansión de la descripción del negocio.
         */
        const manejarExpansionDescripcion = () => {
            $('.negocio-detalle-desc-wrapper').each(function() {
                const $wrapper = $(this);
                const $desc = $wrapper.find('.negocio-detalle-desc');
                const $btn = $wrapper.find('.btn-leer-mas-desc');

                // Verificamos si el texto realmente se trunca
                setTimeout(() => {
                    const elemento = $desc[0];
                    if (elemento && elemento.scrollHeight > elemento.offsetHeight) {
                        $btn.show();
                    } else {
                        $btn.hide();
                    }
                }, 250);
            });
        };

        $(document).on('click', '.btn-leer-mas-desc', function() {
            const $btn = $(this);
            const $desc = $btn.siblings('.negocio-detalle-desc');
            $desc.addClass('expanded');
            $btn.hide();
        });

        /**
         * Inicializa el mapa de Google en modo solo lectura para mostrar la ubicación del negocio.
         */
        function initMapDetalle() {
            if (typeof google === 'undefined' || !window.datosUbicacionNegocio) return;

            const lat = parseFloat(window.datosUbicacionNegocio.lat);
            const lng = parseFloat(window.datosUbicacionNegocio.lng);
            const titulo = window.datosUbicacionNegocio.titulo || 'Negocio';

            if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) {
                console.warn('Coordenadas de ubicación no válidas');
                $('#mapa_detalle_negocio').hide();
                return;
            }

            const myLatLng = { lat: lat, lng: lng };
            const map = new google.maps.Map(document.getElementById("mapa_detalle_negocio"), {
                zoom: 15,
                center: myLatLng,
                disableDefaultUI: false,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                zoomControl: true,
            });

            new google.maps.Marker({
                position: myLatLng,
                map,
                title: titulo,
            });
        }

        /**
         * Obtiene el detalle de la actividad vía Ajax.
         */
        const obtenerDetalleActividad = (id) => {
            return new Promise((resolve) => {
                $.ajax({
                    url: APP_URL + '/reda/negocios/experiencias/actividades/detalle/' + id,
                    type: 'GET',
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

                        const mensajeErrorBase = window.RedaAlojamientoJson["Error en el servidor de Torbian"] || 'Error en el servidor de Torbian';
                        const detalleError = respuestaServidor.message ? `<br />${respuestaServidor.message}` : '';
                        
                        let respuesta = {
                            'success': false,
                            'message' : window.RedaAlojamientoJson["Error al recuperar detalle"] || 'Error al recuperar detalle',
                            'mensaje_usuario': respuestaServidor.mensaje_usuario ?? `${mensajeErrorBase}.${detalleError}`,
                            'respuesta': respuestaServidor.respuesta || '',
                            'code': x.status !== 0 ? x.status : 504,
                        };
                        resolve(respuesta);
                    }
                });
            });
        };

        $(function() {
            // Inicializar el mapa
            initMapDetalle();

            // Manejo de Filtros Desktop
            $('#filtro_tipo_actividad').on('change', function() {
                const tipo = $(this).val();
                $('#filtro_tipo_actividad_movil').val(tipo); // Sincronizar con móvil
                filtrarActividades(tipo);
            });

            // Manejo de Filtros Móvil (Modal)
            $('#modalBusquedaActividades .btn-primary').on('click', function() {
                const tipo = $('#filtro_tipo_actividad_movil').val();
                $('#filtro_tipo_actividad').val(tipo); // Sincronizar con desktop
                filtrarActividades(tipo);
                $('#modalBusquedaActividades').modal('hide');
            });

            // --- CLICK EN PRODUCTO/SERVICIO PARA VER DETALLE ---
            $(document).on('click', '.producto-card', async function(e) {
                const id = $(this).data('id');
                if (!id) return;

                // Reset modal content with loader
                $('#bodyDetalleActividad').html(`
                    <div class="text-center p-5">
                        <i class="fa fa-spinner fa-spin fa-3x text-success"></i>
                    </div>
                `);
                
                $('#modalDetalleActividad').modal('show');

                const response = await obtenerDetalleActividad(id);

                if (response.success) {
                    $('#bodyDetalleActividad').html(response.respuesta.html);
                } else {
                    $('#bodyDetalleActividad').html(`
                        <div class="alert alert-danger m-4">
                            ${response.mensaje_usuario}
                        </div>
                    `);
                }
            });
        });

        /**
         * Filtra los productos/servicios en el frontend basándose en el tipo.
         * @param {string} tipo - 'producto', 'servicio' o '' (todos)
         */
        function filtrarActividades(tipo) {
            console.log('Filtrando por tipo:', tipo);

            $('.producto-card').each(function() {
                const tipoCard = $(this).data('tipo-actividad');

                if (tipo === '' || tipo === tipoCard) {
                    $(this).fadeIn(300);
                } else {
                    $(this).hide();
                }
            });

            // Si una sección de carrusel queda vacía, podríamos ocultar el título de la sección
            $('.seccion-productos').each(function() {
                const cardsVisibles = $(this).find('.producto-card:visible').length;
                if (cardsVisibles === 0) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        }
    }

})(jQuery);
