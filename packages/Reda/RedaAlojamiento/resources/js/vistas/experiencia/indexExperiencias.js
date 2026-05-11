import { eliminarExperiencia } from './eliminarExperiencia.js';

(function( $ ) {
  "use strict";
  const containerId = '#index_experiencias';
  if ($(containerId).length) {
    console.log('Script para "Index Experiencias" cargado revisión 10-05-2026.');
    $(function() {
        $(document).on('click', '.btn-delete-experiencia', async function(e) {
            e.preventDefault();

            const id = $(this).data('id');
            const btn = $(this);

            if (confirm('¿Estás seguro de que deseas eliminar este negocio? Esta acción borrará todas las fotos, actividades y registros relacionados de forma permanente.')) {
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                const respuestaEliminarExperiencia = await eliminarExperiencia(id);
                if (respuestaEliminarExperiencia.success) {
                    // Animación para remover la tarjeta de la vista
                    btn.closest('.card').fadeOut(500, function() {
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
