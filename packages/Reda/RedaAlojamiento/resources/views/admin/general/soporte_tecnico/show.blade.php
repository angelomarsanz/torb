@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>{{ __('Detalle del Ticket #') }}{{ $ticket->id }}</h1>
        @include('admin.common.breadcrumb')
    </section>

    <section class="content">
        <div class="row" id="show_soporte_tecnico">
            <div class="col-md-8 col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ $ticket->tema }}</h3>
                    </div>
                    <div class="box-body">
                        <!-- Información General (Desktop) -->
                        <div class="row d-none d-md-flex mb-4">
                            <div class="col-md-3">
                                <strong><i class="fa fa-user margin-r-5"></i> {{ __('Usuario') }}</strong>
                                <p class="text-muted">{{ $ticket->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fa fa-calendar margin-r-5"></i> {{ __('Fecha') }}</strong>
                                <p class="text-muted">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fa fa-warning margin-r-5"></i> {{ __('Prioridad') }}</strong>
                                <p>
                                    @php
                                        $prioridadClass = [
                                            'Alta' => 'label-danger',
                                            'Media' => 'label-warning',
                                            'Baja' => 'label-info'
                                        ][$ticket->prioridad] ?? 'label-default';
                                    @endphp
                                    <span class="label {{ $prioridadClass }}">{{ $ticket->prioridad ?? 'N/A' }}</span>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fa fa-check-circle margin-r-5"></i> {{ __('Estatus') }}</strong>
                                <p><span class="label label-primary">{{ $ticket->estatus ?? 'Abierto' }}</span></p>
                            </div>
                        </div>

                        <!-- Información General (Móvil) -->
                        <div class="d-md-none mb-4">
                            <div class="card p-3 shadow-sm soporte-tecnico-card soporte-tecnico-card-show">
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Usuario') }}</small>
                                        <span class="f-14">{{ $ticket->user->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-6 mb-2 text-right">
                                        <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Estatus') }}</small>
                                        <span class="label label-primary">{{ $ticket->estatus ?? 'Abierto' }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Prioridad') }}</small>
                                        <span class="label {{ $prioridadClass }}">{{ $ticket->prioridad ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-6 text-right">
                                        <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Fecha') }}</small>
                                        <span class="f-12 text-muted">{{ $ticket->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <strong><i class="fa fa-file-text-o margin-r-5"></i> {{ __('Mensaje / Descripción') }}</strong>
                        <div class="well well-sm mt-2 soporte-tecnico-well-custom">
                            {!! nl2br(e($ticket->mensaje ?? $ticket->descripcion ?? __('Sin descripción disponible.'))) !!}
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('reda.admin.general.soporte_tecnico.index') }}" class="btn btn-default btn-flat">
                            <i class="fa fa-arrow-left"></i> {{ __('Volver al listado') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-xs-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('Acciones del Ticket') }}</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">{{ __('Gestione el estado de este ticket.') }}</p>
                        {{-- Aquí se podrían agregar formularios para cambiar estatus o responder --}}
                        <div class="alert alert-info f-12">
                            <i class="fa fa-info-circle"></i> {{ __('Funcionalidad de respuesta en desarrollo.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/reda/admin/general/soporte_tecnico/showSoporteTecnico.min.js') }}"></script>
@endpush
