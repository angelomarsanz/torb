<div class="modal fade" id="modal-detalle-mediacion-reda" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-700" id="modalDetalleLabel">{{ __('Detalle de Mediación') }} #{{ $disputa->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex flex-column">
                            <span class="text-muted small fw-600 text-uppercase letter-spacing-1">{{ __('Estatus') }}</span>
                            <div>
                                <span class="badge bg-success rounded-pill px-3 py-2">{{ $disputa->estado }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex flex-column">
                            <span class="text-muted small fw-600 text-uppercase letter-spacing-1">{{ __('Prioridad') }}</span>
                            <div>
                                @php
                                    $colorPrioridad = 'bg-info';
                                    if ($disputa->prioridad === 'Alta') $colorPrioridad = 'bg-danger';
                                    elseif ($disputa->prioridad === 'Media') $colorPrioridad = 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $colorPrioridad }} rounded-pill px-3 py-2">{{ $disputa->prioridad }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex flex-column">
                            <span class="text-muted small fw-600 text-uppercase letter-spacing-1">{{ __('Fecha de Apertura') }}</span>
                            <span class="fw-600">{{ $disputa->fecha_apertura ? $disputa->fecha_apertura->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex flex-column">
                            <span class="text-muted small fw-600 text-uppercase letter-spacing-1">{{ __('ID Reservación') }}</span>
                            <span class="fw-600">#{{ $disputa->booking_id }}</span>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12 mb-4">
                        <div class="d-flex flex-column">
                            <span class="text-muted small fw-600 text-uppercase letter-spacing-1">{{ __('Motivo de la Mediación') }}</span>
                            <span class="fs-5 fw-700 text-dark">{{ $disputa->motivo }}</span>
                        </div>
                    </div>
                    <div class="col-md-12 mb-0">
                        <div class="d-flex flex-column">
                            <span class="text-muted small fw-600 text-uppercase letter-spacing-1">{{ __('Descripción Detallada') }}</span>
                            <div class="p-3 bg-light rounded border mt-1">
                                {{ $disputa->descripcion }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary px-4 fw-700" data-bs-dismiss="modal">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>
</div>
