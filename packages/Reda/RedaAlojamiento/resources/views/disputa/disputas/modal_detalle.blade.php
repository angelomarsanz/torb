<div class="modal fade" id="modal-detalle-mediacion-reda" tabindex="-1" role="dialog" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title reda-mediation-title" id="modalDetalleLabel">{{ __('Detalle de Mediación') }} #{{ $disputa->id }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex flex-column">
                            <span class="reda-mediation-label">{{ __('Estatus') }}</span>
                            <div>
                                <span class="badge badge-success reda-mediation-badge">{{ $disputa->estado }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex flex-column">
                            <span class="reda-mediation-label">{{ __('Paso Actual') }}</span>
                            <span class="reda-mediation-value">{{ $disputa->paso_actual }}</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex flex-column">
                            <span class="reda-mediation-label">{{ __('Fecha de Apertura') }}</span>
                            <span class="reda-mediation-value font-weight-normal">{{ $disputa->fecha_apertura ? $disputa->fecha_apertura->format('d/m/Y H:i') : __('N/A') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex flex-column">
                            <span class="reda-mediation-label">{{ __('Prioridad') }}</span>
                            @php
                                $prioridadClass = 'text-info';
                                if($disputa->prioridad == 'Alta') $prioridadClass = 'text-danger';
                                elseif($disputa->prioridad == 'Media') $prioridadClass = 'text-warning';
                            @endphp
                            <span class="reda-mediation-value {{ $prioridadClass }}">{{ __($disputa->prioridad) }}</span>
                        </div>
                    </div>
                </div>

                <hr class="my-2">

                <div class="row mt-3">
                    <div class="col-md-12 mb-4">
                        <div class="d-flex flex-column">
                            <span class="reda-mediation-label">{{ __('Motivo de la Mediación') }}</span>
                            <span class="reda-mediation-value">{{ $disputa->motivo }}</span>
                        </div>
                    </div>
                    <div class="col-md-12 mb-0">
                        <div class="d-flex flex-column">
                            <span class="reda-mediation-label">{{ __('Descripción Detallada') }}</span>
                            <div class="reda-mediation-desc-box">{{ $disputa->descripcion }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer reda-mediation-footer border-0">
                <button type="button" class="btn btn-secondary px-4 font-weight-700" data-dismiss="modal">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>
</div>
