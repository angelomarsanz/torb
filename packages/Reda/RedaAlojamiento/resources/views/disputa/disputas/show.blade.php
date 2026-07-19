@extends('template')

@section('main')
<div class="margin-top-85">
    <div class="row m-0">
        @include('users.sidebar')
        <div class="col-lg-10 p-0 mb-5 min-height">
            <div class="main-panel">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 p-0 mb-3">
                            <div class="list-bacground mt-4 rounded-3 p-4 border">
                                <span class="text-18 pt-4 pb-4 font-weight-700">{{ __('Detalle de Mediación') }} #{{ $disputa->id }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 reda-mediation-box p-4 bg-white">
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
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex flex-column">
                                        <span class="reda-mediation-label">{{ __('Fecha Apertura') }}</span>
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
                                <div class="col-md-12 mb-4">
                                    <div class="d-flex flex-column">
                                        <span class="reda-mediation-label">{{ __('Motivo') }}</span>
                                        <span class="reda-mediation-value">{{ $disputa->motivo }}</span>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-0">
                                    <div class="d-flex flex-column">
                                        <span class="reda-mediation-label">{{ __('Descripción') }}</span>
                                        <div class="reda-mediation-desc-box">{{ $disputa->descripcion }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('reda.disputas.index') }}" class="btn btn-outline-success px-4 font-weight-700">
                                    {{ __('Volver al listado') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
