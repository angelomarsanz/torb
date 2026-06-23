@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>{{ __('Soporte técnico') }}</h1>
        @include('admin.common.breadcrumb')
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box" id="index_soporte_tecnico">
                    <div class="box-header with-border d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h3 class="box-title">{{ __('Listado de Tickets') }}</h3>
                        <div class="box-tools position-static">
                            {{-- Aquí podrías agregar un botón para crear nuevo ticket si fuera necesario --}}
                        </div>
                    </div>
                    <div class="box-body">
                        <!-- Vista de Escritorio: Tabla tradicional -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-bordered table-striped table-hover f-14">
                                <thead>
                                    <tr class="soporte-tecnico-header-row">
                                        <th class="soporte-tecnico-w-5 text-center">{{ __('ID') }}</th>
                                        <th class="soporte-tecnico-w-15">{{ __('Usuario') }}</th>
                                        <th class="soporte-tecnico-w-25">{{ __('Tema') }}</th>
                                        <th class="soporte-tecnico-w-10 text-center">{{ __('Prioridad') }}</th>
                                        <th class="soporte-tecnico-w-10 text-center">{{ __('Estatus') }}</th>
                                        <th class="soporte-tecnico-w-15 text-center">{{ __('Fecha') }}</th>
                                        <th class="soporte-tecnico-w-10 text-center">{{ __('Acciones') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($tickets->count() > 0)
                                        @foreach($tickets as $ticket)
                                            <tr>
                                                <td class="text-center">{{ $ticket->id }}</td>
                                                <td>{{ $ticket->user->name ?? 'N/A' }}</td>
                                                <td>{{ $ticket->tema }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $prioridadClass = [
                                                            'Alta' => 'label-danger',
                                                            'Media' => 'label-warning',
                                                            'Baja' => 'label-info'
                                                        ][$ticket->prioridad] ?? 'label-default';
                                                    @endphp
                                                    <span class="label {{ $prioridadClass }}">{{ $ticket->prioridad ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="label label-primary">{{ $ticket->estatus ?? 'Abierto' }}</span>
                                                </td>
                                                <td class="text-center">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('reda.admin.general.soporte_tecnico.show', $ticket->id) }}" class="btn btn-xs btn-primary btn-flat" data-toggle="tooltip" title="{{ __('Ver Detalle') }}">
                                                        <i class="fa fa-eye"></i> {{ __('Ver Detalle') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center text-muted soporte-tecnico-no-results-td">
                                                <i class="fa fa-info-circle"></i> {{ __('No se encontraron tickets de soporte.') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- Vista Móvil: Lista de Tarjetas (Cards) -->
                        <div class="d-md-none">
                            @if($tickets->count() > 0)
                                @foreach($tickets as $ticket)
                                    <div class="card mb-3 shadow-sm border-light soporte-tecnico-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-blue soporte-tecnico-ticket-id">#{{ $ticket->id }}</span>
                                                <span class="label label-primary">{{ $ticket->estatus ?? 'Abierto' }}</span>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Usuario') }}</small>
                                                <span class="f-14">{{ $ticket->user->name ?? 'N/A' }}</span>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Tema') }}</small>
                                                <strong class="f-15">{{ $ticket->tema }}</strong>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                <div>
                                                    <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Prioridad') }}</small>
                                                    @php
                                                        $prioridadClass = [
                                                            'Alta' => 'label-danger',
                                                            'Media' => 'label-warning',
                                                            'Baja' => 'label-info'
                                                        ][$ticket->prioridad] ?? 'label-default';
                                                    @endphp
                                                    <span class="label {{ $prioridadClass }}">{{ $ticket->prioridad ?? 'N/A' }}</span>
                                                </div>
                                                <a href="{{ route('reda.admin.general.soporte_tecnico.show', $ticket->id) }}" class="btn btn-sm btn-primary btn-flat">
                                                    <i class="fa fa-eye"></i> {{ __('Ver Detalle') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted p-5 bg-light rounded">
                                    <i class="fa fa-info-circle fa-3x mb-3 soporte-tecnico-empty-icon"></i>
                                    <p>{{ __('No se encontraron tickets de soporte.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/reda/admin/general/soporte_tecnico/indexSoporteTecnico.min.js') }}"></script>
@endpush
