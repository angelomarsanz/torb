<!-- Modal para el Formulario de Reservación -->
<div class="modal fade reda-modal-reserva" id="modalReservar" tabindex="-1" role="dialog" aria-labelledby="modalReservarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modalReservarLabel">{{ __('Reservar') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalReservarBody">
                {{-- Aquí se inyectará el formulario mediante JS --}}
            </div>
        </div>
    </div>
</div>
