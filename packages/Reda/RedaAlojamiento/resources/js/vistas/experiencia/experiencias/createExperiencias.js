// resources/js/reda/vistas/experiencia/experiencias/createExperiencias.js

$(function() {
    // 1. Verificar si el div específico de esta vista existe en la página.
    const containerId = '#create_experiencia';
    if ($(containerId).length) {

        // --- Aquí va todo el código de la vista Create Experiencia ---

        console.log('Script para "Create Experiencia" cargado.');

        // 2. Crear el botón y el modal
        const modalId = 'modalCreateExperiencia';

        // HTML del Modal
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Crear Experiencia</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    Estás creando una experiencia.
                  </div>
                </div>
              </div>
            </div>
        `;

        // 3. Crear contenido inicial dinámico con el botón modificado
        const initialContentHtml = `
            <div class="card">
              <div class="card-header">
                Crear Experiencia
              </div>
              <div class="card-body">
                <h5 class="card-title">Contenido dinámico</h5>
                <p class="card-text">Este contenido ha sido insertado dinámicamente con JavaScript.</p>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#${modalId}">
                  Crear Experiencia
                </button>
              </div>
            </div>
        `;

        // 4. Añadir el contenido al contenedor y el modal al body.
        $(containerId).html(initialContentHtml);
        $('body').append(modalHtml);
    }
});