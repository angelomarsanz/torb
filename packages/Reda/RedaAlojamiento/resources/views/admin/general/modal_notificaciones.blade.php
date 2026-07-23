<!-- Modal para Notificaciones (Éxito / Error / Info) -->
<div class="modal fade reda-modal-custom-notificacion" id="modal-notificacion" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content reda-modal-custom-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div id="notificacion-icono" class="mb-3">
                    <!-- El icono se insertará vía JS -->
                </div>
                <h4 id="notificacion-titulo" class="modal-title mb-2 fw-bold"></h4>
                <p id="notificacion-mensaje" class="text-muted f-14 mb-0"></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" id="btn-cerrar-notificacion" class="btn btn-primary btn-flat px-4 reda-modal-custom-btn-pill" data-bs-dismiss="modal">
                    {{ __('reda-alojamiento::messages.general.aceptar') }}
                </button>
            </div>
        </div>
    </div>
</div>

