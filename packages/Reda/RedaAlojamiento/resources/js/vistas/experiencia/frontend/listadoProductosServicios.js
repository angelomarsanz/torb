(function( $ ) {
    "use strict";

    const containerId = '#listado_productos_servicios';

    if ($(containerId).length) {
        console.log('Script para Listado de Productos y Servicios cargado correctamente');

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
