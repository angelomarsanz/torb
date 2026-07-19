import { 
    todosSvg, 
    abiertosSvg, 
    enRevisionSvg, 
    esperandoRespuestaSvg, 
    resueltosSvg, 
    cerradosSvg 
} from '../../../general/iconos';

(function($) {
    "use strict";

    const containerId = '#indexDisputas';

    /**
     * Inyecta dinámicamente las pestañas de estatus en la cabecera del listado.
     */
    const inyectarPestanasEstatus = () => {
        const header = $('#disputas-tabs-header');
        if (!header.length) return;

        // Seguridad para traducciones
        const trans = window.RedaAlojamientoJson || {};

        const estatus = [
            { id: 'todos', nombre: trans["Todos"] || "Todos", icono: todosSvg, contador: 0 },
            { id: 'abiertos', nombre: trans["Abiertos"] || "Abiertos", icono: abiertosSvg, contador: 0 },
            { id: 'revision', nombre: trans["En revisión"] || "En revisión", icono: enRevisionSvg, contador: 0 },
            { id: 'espera', nombre: trans["Esperando respuesta"] || "Esperando respuesta", icono: esperandoRespuestaSvg, contador: 0 },
            { id: 'resueltos', nombre: trans["Resueltos"] || "Resueltos", icono: resueltosSvg, contador: 0 },
            { id: 'cerrados', nombre: trans["Cerrados"] || "Cerrados", icono: cerradosSvg, contador: 0 }
        ];

        let html = `<div class="d-flex flex-wrap border-bottom pb-2 reda-tabs-nav">`;
        
        estatus.forEach((e, index) => {
            const isActive = index === 0 ? 'active' : '';
            html += `
                <div class="disputa-tab-item px-3 py-2 mr-2 pointer text-center ${isActive}" data-status="${e.id}">
                    <div class="d-flex align-items-center justify-content-center mb-1 status-icon">
                        ${e.icono}
                    </div>
                    <div class="d-flex align-items-center justify-content-center">
                        <span class="text-14 font-weight-600">${e.nombre}</span>
                        <span class="badge badge-pill badge-success ml-2 text-10 status-counter" id="counter-${e.id}">${e.contador}</span>
                    </div>
                </div>
            `;
        });

        html += `</div>`;
        header.html(html);
    };

    /**
     * Simula o ejecuta la carga de mediaciones según el estatus seleccionado.
     */
    const cargarMediaciones = async (status) => {
        if (window.RedaNotificaciones && typeof window.RedaNotificaciones.esperar === 'function') {
            window.RedaNotificaciones.esperar();
        }

        console.log(`Cargando mediaciones para el estatus: ${status}`);

        // Seguridad para traducciones
        const trans = window.RedaAlojamientoJson || {};

        // TODO: Implementar petición AJAX real aquí en el siguiente paso
        setTimeout(() => {
            if (window.RedaNotificaciones && typeof window.RedaNotificaciones.ocultar === 'function') {
                window.RedaNotificaciones.ocultar();
            }
            $('#disputas-list-container').html(`
                <div class="text-center py-5">
                    <p class="text-muted text-16">${trans["Próximamente: Listado de mediaciones para"] || "Próximamente: Listado de mediaciones para"} <strong>${status}</strong></p>
                </div>
            `);
        }, 800);
    };

    $(function() {
        if ($(containerId).length) {
            console.log('Script para "Index Disputas" (Dashboard) cargado correctamente.');
            inyectarPestanasEstatus();

            // Manejo de clicks en las pestañas
            $(document).on('click', '.disputa-tab-item', function() {
                const item = $(this);
                if (item.hasClass('active')) return;

                $('.disputa-tab-item').removeClass('active');
                item.addClass('active');

                const status = item.attr('data-status');
                cargarMediaciones(status);
            });

            // Carga inicial (Todos)
            cargarMediaciones('todos');
        }
    });

})(jQuery);
