@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>{{ __('Detalle del Ticket #') }}{{ $ticket->id }}</h1>
        @include('admin.common.breadcrumb')
    </section>

    <section class="content">
        <div class="row" id="show_soporte_tecnico">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ $ticket->tema }}</h3>
                    </div>
                    <div class="box-body">
                        <!-- Información General (Desktop) -->
                        <div class="row d-none d-md-flex mb-4">
                            <div class="col-md-3">
                                <strong><i class="fa fa-user margin-r-5"></i> {{ __('Usuario') }}</strong>
                                <div class="d-flex align-items-center mt-1">
                                    @if($ticket->user)
                                        <div class="symbol symbol-30px symbol-circle me-2">
                                            <span class="symbol-label bg-light-primary text-primary fw-bold">{{ substr(trim($ticket->user->first_name), 0, 1) }}</span>
                                        </div>
                                        <span class="text-muted">
                                            {{ explode(' ', trim($ticket->user->first_name))[0] }} {{ explode(' ', trim($ticket->user->last_name))[0] }}
                                        </span>
                                    @else
                                        <span class="text-muted">{{ __('N/A') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fa fa-calendar margin-r-5"></i> {{ __('Fecha') }}</strong>
                                <p class="text-muted mt-1">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fa fa-warning margin-r-5"></i> {{ __('Prioridad') }}</strong>
                                <div class="mt-1">
                                    @php
                                        $prioridadClass = [
                                            'Alta' => 'text-danger',
                                            'Media' => 'text-warning',
                                            'Baja' => 'text-info'
                                        ][$ticket->prioridad] ?? 'text-secondary';
                                    @endphp
                                    <span class="{{ $prioridadClass }} fw-bold">
                                        <i class="fa fa-circle fs-9 me-1"></i>{{ $ticket->prioridad ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <strong><i class="fa fa-check-circle margin-r-5"></i> {{ __('Estatus') }}</strong>
                                <div class="mt-1">
                                    <span class="text-primary fw-bold">{{ $ticket->estatus ?? 'Abierto' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Información General (Móvil) -->
                        <div class="d-md-none mb-4">
                            <div class="card p-3 shadow-sm soporte-tecnico-card soporte-tecnico-card-show">
                                <div class="row">
                                    <div class="col-12 mb-3 d-flex align-items-center">
                                        @if($ticket->user)
                                            <div class="symbol symbol-30px symbol-circle me-2">
                                                <span class="symbol-label bg-light-primary text-primary fw-bold">{{ substr(trim($ticket->user->first_name), 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Usuario') }}</small>
                                                <span class="f-14 fw-bold">
                                                    {{ explode(' ', trim($ticket->user->first_name))[0] }} {{ explode(' ', trim($ticket->user->last_name))[0] }}
                                                </span>
                                            </div>
                                        @else
                                            <div>
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Usuario') }}</small>
                                                <span class="f-14">{{ __('N/A') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-6 mb-2">
                                        <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Estatus') }}</small>
                                        <span class="text-primary fw-bold">{{ $ticket->estatus ?? 'Abierto' }}</span>
                                    </div>
                                    <div class="col-6 mb-2 text-right">
                                        <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Prioridad') }}</small>
                                        <span class="{{ $prioridadClass }} fw-bold">
                                            <i class="fa fa-circle fs-9 me-1"></i>{{ $ticket->prioridad ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Fecha') }}</small>
                                        <span class="f-12 text-muted">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <strong><i class="fa fa-file-text-o margin-r-5"></i> {{ __('Mensaje') }}</strong>
                        </div>
                        <div class="well well-sm soporte-tecnico-well-custom">
                            {!! nl2br(e($ticket->mensaje_usuario ?? __('Sin descripción disponible.'))) !!}
                        </div>

                        @if(isset($ticket->link_error) && !empty($ticket->link_error))
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-primary btn-flat btn-lg px-5" id="btn_gestionar_ticket"
                                        data-link-error="{{ json_encode($ticket->link_error) }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-recurso-existe="{{ $ticket->recurso_existe ? '1' : '0' }}"
                                        data-estatus="{{ $ticket->estatus }}">
                                    <i class="fa fa-gears me-2"></i> {{ __('Gestionar ticket') }}
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="box-footer">
                        <a href="{{ route('reda.admin.general.soporte_tecnico.index') }}" class="btn btn-default btn-flat btn-volver-listado">
                            <i class="fa fa-arrow-left"></i> {{ __('Volver al listado') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Dinámico para Gestión de Tickets -->
    <div class="modal fade" id="modal_gestionar_ticket" tabindex="-1" role="dialog" aria-labelledby="modalGestionarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGestionarLabel">{{ __('Gestión de Ticket de Soporte') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenido_modal_gestionar">
                    <!-- El contenido se cargará dinámicamente con JavaScript -->
                    <div class="text-center p-5">
                        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">{{ __('Cargando opciones de gestión...') }}</p>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div id="acciones_dinamicas_modal">
                        <!-- Los botones de acción específicos se cargarán aquí desde JS -->
                    </div>
                    <button type="button" class="btn btn-secondary btn-flat" data-bs-dismiss="modal">{{ __('Cerrar') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('public/js/reda/admin/general/soporte_tecnico/showSoporteTecnico.min.js?v=' . time()) }}"></script>
@endpush

