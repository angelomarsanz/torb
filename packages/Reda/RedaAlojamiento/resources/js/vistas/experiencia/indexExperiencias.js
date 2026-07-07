import { eliminarExperiencia } from './eliminarExperiencia.js';

(function( $ ) {
  "use strict";
  const containerId = '#index_experiencias';
  if ($(containerId).length || $('.btn-eliminar-experiencia').length) {
    console.log('Script para "Index Experiencias" cargado revisión 07-07-2026.');
    $(function() {
        $(document).on('click', '.btn-eliminar-experiencia', async function(e) {
            e.preventDefault();

            const id = $(this).data('id');
            const btn = $(this);

            // Corregido: Usar window.RedaAlojamiento que es lo que envía Blade
            const messages = window.RedaAlojamiento || {};
            const mensajeConfirmacion = messages["¿Estás seguro de que deseas eliminar este negocio? Esta acción borrará todas las fotos, actividades y registros relacionados de forma permanente."] || "¿Estás seguro de que deseas eliminar este negocio? Esta acción borrará todas las fotos, actividades y registros relacionados de forma permanente.";

            if (confirm(mensajeConfirmacion)) {
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                const respuestaEliminarExperiencia = await eliminarExperiencia(id);
                if (respuestaEliminarExperiencia.success) {
                    // Animación para remover la tarjeta de la vista
                    btn.closest('.col-md-12').fadeOut(500, function() {
                        $(this).remove();
                    });
                } else {
                    alert(respuestaEliminarExperiencia.mensaje_usuario);
                    btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                }
            }
        });
    });
  }
})(jQuery);
