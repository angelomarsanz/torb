<div class="modal fade modal-listado-infinito" id="modalListadoInfinito" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" id="modalDialogInfinito">
        <div class="modal-content border-0 shadow-lg modal-content-infinito-default" id="modalContentInfinito">
            <div class="modal-header border-0 px-4 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="modal-title font-weight-700 text-24 mb-0" id="tituloModalInfinito">{{ __('Listado') }}</h5>
                <button type="button" class="close btn-regresar-infinito" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
            <div class="modal-body p-4 overflow-auto" id="bodyModalInfinito">
                <div id="contenedor_items_infinito" class="row">
                    <!-- Aquí se cargarán los items -->
                </div>
                <div id="loader_infinito" class="text-center py-4 w-100 loader-infinito">
                    <i class="fas fa-spinner fa-spin fa-3x text-success"></i>
                    <p class="mt-2 font-weight-600 text-muted">{{ __('Cargando más...') }}</p>
                </div>
                <div id="no_more_infinito" class="text-center py-4 w-100 text-muted no-more-infinito">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>{{ __('Has llegado al final de la lista') }}</p>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 justify-content-center d-lg-none">
                <button type="button" class="btn btn-outline-dark rounded-pill px-5 font-weight-700" data-dismiss="modal">
                    {{ __('Regresar') }}
                </button>
            </div>
        </div>
    </div>
</div>
