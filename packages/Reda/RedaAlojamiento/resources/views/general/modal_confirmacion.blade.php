<!-- Modal para Confirmación de Acciones (Eliminar, etc) - Versión Frontend (Bootstrap 4.5) -->
<div class="modal fade" id="modal-confirmacion" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="mb-3">
                    <i class="fa fa-exclamation-triangle fa-4x text-warning"></i>
                </div>
                <h4 id="confirmacion-titulo" class="modal-title mb-2 font-weight-bold">{{ __('reda-alojamiento::messages.general.confirmar_accion') }}</h4>
                <p id="confirmacion-mensaje" class="text-muted f-14 mb-0"></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-light px-4" style="border-radius: 20px;" data-dismiss="modal">
                    {{ __('reda-alojamiento::messages.general.cancelar') }}
                </button>
                <button type="button" id="btn-confirmar-si" class="btn btn-danger px-4" style="border-radius: 20px;">
                    <span class="btn-text">{{ __('reda-alojamiento::messages.general.eliminar') }}</span>
                    <i class="fa fa-spinner fa-spin d-none"></i>
                </button>
            </div>
        </div>
    </div>
</div>
