// resources/js/reda/vistas/administrativo/administrativos/indexAdministrativo.js

$(function() {
    // 1. Verificar si el div específico de esta vista existe en la página.
    const containerId = '#indexAdministrativos';
    if ($(containerId).length) {

        // --- Aquí va todo el código de la vista de Administrativos ---

        console.log('Script para "index de Administrativos" cargado.');

        // 2. Crear el botón y el modal con jQuery y Bootstrap 4.5
        const modalId = 'modalAdministrativo';

        // HTML del botón
        const buttonHtml = `
            <button type="button" class="btn btn-primary btn-reda-modal" data-toggle="modal" data-target="#${modalId}">
              Lanzar Modal de Administrativo
            </button>
        `;

        // HTML del Modal (usando sintaxis de Bootstrap 4)
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Módulo Administrativo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    Hola, estás posicionado en la vista indexAdministrativo.
                  </div>
                </div>
              </div>
            </div>
        `;

        // 3. Añadir el botón al contenedor y el modal al body.
        $(containerId).append(buttonHtml);
        $('body').append(modalHtml);
    }
});
