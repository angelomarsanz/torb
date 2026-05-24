<!-- Modal para Notificaciones (Éxito / Error / Info) - Versión Frontend (Bootstrap 4.5) -->
<div class="modal fade" id="modal-notificacion" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center pt-0">
                <div id="notificacion-icono" class="mb-3">
                    <!-- El icono se insertará vía JS -->
                </div>
                <h4 id="notificacion-titulo" class="modal-title mb-2 font-weight-bold"></h4>
                <p id="notificacion-mensaje" class="text-muted f-14 mb-0"></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" id="btn-cerrar-notificacion" class="btn btn-primary px-4" style="border-radius: 20px;" data-dismiss="modal">
                    {{ __('reda-alojamiento::messages.general.aceptar') }}
                </button>
            </div>
        </div>
    </div>
</div>
