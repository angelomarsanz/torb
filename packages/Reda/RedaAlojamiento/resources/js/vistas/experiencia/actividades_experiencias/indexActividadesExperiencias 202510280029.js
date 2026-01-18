// resources/js/reda/vistas/experiencia/actividades_experiencia/index.js

$(function() {
    // 1. Verificar si el div específico de esta vista existe en la página.
    const containerId = '#actividades_experiencia';
    if ($(containerId).length) {

        // --- Aquí va todo el código de la vista de Actividades y Experiencias ---

        console.log('Script para "Actividades y Experiencias" cargado.');

        // 2. Crear contenido inicial dinámico (usando concatenación para evitar errores de parsing).
        const initialContentHtml =
            '<div class="card">' +
            '  <div class="card-header">' +
            '    Gestión de Actividades' +
            '  </div>' +
            '  <div class="card-body">' +
            '    <h5 class="card-title">Contenido dinámico</h5>' +
            '    <p class="card-text">Este contenido ha sido insertado dinámicamente con JavaScript.</p>' +
            '    <a href="#" class="btn btn-primary">Agregar Actividad</a>' +
            '  </div>' +
            '</div>';

        // 3. Añadir el contenido al contenedor.
        $(containerId).html(initialContentHtml);
    }
});