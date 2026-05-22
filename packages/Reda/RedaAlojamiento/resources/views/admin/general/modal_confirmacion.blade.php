<!-- Modal para Confirmación de Acciones (Eliminar, etc) -->
<div class="modal fade" id="modal-confirmacion" role="dialog" style="z-index: 1070;">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; border: none; shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="mb-3">
                    <i class="fa fa-exclamation-triangle fa-4x text-warning"></i>
                </div>
                <h4 id="confirmacion-titulo" class="modal-title mb-2 fw-bold">{{ __('reda-alojamiento::messages.general.confirmar_accion') }}</h4>
                <p id="confirmacion-mensaje" class="text-muted f-14 mb-0"></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn btn-default btn-flat px-4" style="border-radius: 20px;" data-bs-dismiss="modal">
                    {{ __('reda-alojamiento::messages.general.cancelar') }}
                </button>
                <button type="button" id="btn-confirmar-si" class="btn btn-danger btn-flat px-4" style="border-radius: 20px;">
                    <span class="btn-text">{{ __('reda-alojamiento::messages.general.eliminar') }}</span>
                    <i class="fa fa-spinner fa-spin d-none"></i>
                </button>
            </div>
        </div>
    </div>
</div>
