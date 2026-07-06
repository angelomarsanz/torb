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
                        <div class="box-tools position-static d-flex align-items-center gap-2">
                            {{-- Barra de búsqueda que abre el modal --}}
                            <div class="input-group input-group-sm soporte-tecnico-search-wrapper">
                                <input type="text" class="form-control pull-right btn-abrir-busqueda cursor-pointer" placeholder="{{ __('Buscar...') }}" readonly>
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-abrir-busqueda"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
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
                                        <th class="soporte-tecnico-w-15">{{ __('Comercio') }}</th>
                                        <th class="soporte-tecnico-w-20">{{ __('Tema') }}</th>
                                        <th class="soporte-tecnico-w-10 text-center">{{ __('Gestor') }}</th>
                                        <th class="soporte-tecnico-w-10 text-center">{{ __('Prioridad') }}</th>
                                        <th class="soporte-tecnico-w-10 text-center">{{ __('Estatus') }}</th>
                                        <th class="soporte-tecnico-w-15 text-center">{{ __('Fecha') }}</th>
                                        <th class="soporte-tecnico-w-10 text-center">{{ __('Acciones') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($tickets->count() > 0)
                                        @foreach($tickets as $ticket)
                                            <tr class="align-middle">
                                                <td class="text-center fw-bold">{{ $ticket->id }}</td>
                                                <td>
                                                    @if($ticket->user)
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-30px symbol-circle me-2">
                                                                <span class="symbol-label bg-light-primary text-primary fw-bold">{{ substr($ticket->user->first_name, 0, 1) }}</span>
                                                            </div>
                                                            <span>{{ explode(' ', trim($ticket->user->first_name))[0] }} {{ explode(' ', trim($ticket->user->last_name))[0] }}</span>
                                                        </div>
                                                    @else
                                                        {{ __('N/A') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $ticket->nombre_comercio ?? __('N/A') }}
                                                </td>
                                                <td>
                                                    {{ $ticket->tema }}
                                                    @if($ticket->link_error)
                                                        <i class="fa fa-info-circle text-info ms-1 cursor-pointer btn-info-tecnica"
                                                           data-toggle="popover"
                                                           data-content='<ul class="list-unstyled mb-0 f-12">@if(is_array($ticket->link_error) || is_object($ticket->link_error))@foreach($ticket->link_error as $k => $v)<li><strong>{{ ucfirst(str_replace("_", " ", $k)) }}:</strong> {{ is_array($v) ? json_encode($v) : $v }}</li>@endforeach @else <li>{{ $ticket->link_error }}</li> @endif</ul>'
                                                           data-html="true"
                                                           data-trigger="hover"
                                                           title="{{ __('Detalles Técnicos') }}"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($ticket->gestor)
                                                        <span class="badge badge-light-success fw-bold">{{ $ticket->gestor->username }}</span>
                                                    @else
                                                        <span class="text-muted small">{{ __('N/A') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
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
                                                </td>
                                                <td class="text-center">
                                                    <span class="text-primary fw-bold">{{ $ticket->estatus ?? 'Abierto' }}</span>
                                                </td>
                                                <td class="text-center text-muted">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('reda.admin.general.soporte_tecnico.show', $ticket->id) }}" class="text-primary hover-primary btn-ver-ticket" data-toggle="tooltip" title="{{ __('Ver Detalle') }}">
                                                        <i class="fa fa-eye fs-5"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="text-center text-muted soporte-tecnico-no-results-td">
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
                                                <span class="text-primary fw-bold soporte-tecnico-ticket-id">#{{ $ticket->id }}</span>
                                                <span class="text-primary fw-bold">{{ $ticket->estatus ?? 'Abierto' }}</span>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Usuario') }}</small>
                                                <span class="f-14 fw-bold">
                                                    @if($ticket->user)
                                                        {{ explode(' ', trim($ticket->user->first_name))[0] }} {{ explode(' ', trim($ticket->user->last_name))[0] }}
                                                    @else
                                                        {{ __('N/A') }}
                                                    @endif
                                                </span>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Comercio') }}</small>
                                                <span class="f-14 fw-bold">{{ $ticket->nombre_comercio ?? __('N/A') }}</span>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Tema') }}</small>
                                                <strong class="f-15">{{ $ticket->tema }}</strong>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Gestionado por') }}</small>
                                                @if($ticket->gestor)
                                                    <span class="badge badge-light-success fw-bold">{{ $ticket->gestor->username }}</span>
                                                @else
                                                    <span class="text-muted small">{{ __('N/A') }}</span>
                                                @endif
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Fecha') }}</small>
                                                <span class="f-12 text-muted">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                <div>
                                                    <small class="text-muted d-block soporte-tecnico-label-small">{{ __('Prioridad') }}</small>
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
                                                <a href="{{ route('reda.admin.general.soporte_tecnico.show', $ticket->id) }}" class="text-primary btn-ver-ticket" data-toggle="tooltip" title="{{ __('Ver Detalle') }}">
                                                    <i class="fa fa-eye fs-4"></i>
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

                        <!-- Controles de Paginación -->
                        <div class="row justify-content-between pb-3 mt-4 mb-2">
                            <div class="col-sm-12 col-md-12 d-flex justify-content-center">
                                {{ $tickets->appends(request()->except('page'))->links('reda-alojamiento::admin.general.paginacion') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

{{-- Modal de Búsqueda --}}
<div class="modal fade" id="modal_busqueda_soporte" tabindex="-1" role="dialog" aria-labelledby="modalBusquedaLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg rounded-0">
            <div class="modal-header bg-primary text-white rounded-0">
                <h4 class="modal-title fw-bold" id="modalBusquedaLabel">
                    <i class="fa fa-search me-2"></i> {{ __('Búsqueda de tickets') }}
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_busqueda_soporte" action="{{ route('reda.admin.general.soporte_tecnico.index') }}" method="GET">
                <div class="modal-body p-4">
                    <!-- Búsqueda Puntual -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">{{ __('Búsqueda rápida') }}</label>
                        <div class="input-group mb-3 shadow-sm">
                            <span class="input-group-addon bg-white border-end-0"><i class="fa fa-keyboard-o text-muted"></i></span>
                            <input type="text" id="input_puntual" list="lista_busqueda_combinada" class="form-control border-start-0" placeholder="{{ __('ID, usuario o comercio...') }}">
                            <datalist id="lista_busqueda_combinada">
                                @if(isset($usuariosConTickets))
                                    @foreach($usuariosConTickets as $nombre)
                                        <option value="{{ $nombre }}">
                                    @endforeach
                                @endif
                                @if(isset($comerciosConTickets))
                                    @foreach($comerciosConTickets as $comercio)
                                        <option value="{{ $comercio }}">
                                    @endforeach
                                @endif
                            </datalist>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm btn-flat flex-grow-1 btn-buscar-puntual" data-tipo="id">
                                <i class="fa fa-hashtag me-1"></i> {{ __('Por ID del ticket') }}
                            </button>
                            <button type="button" class="btn btn-info btn-sm btn-flat flex-grow-1 btn-buscar-puntual" data-tipo="nombre">
                                <i class="fa fa-user me-1"></i> {{ __('Por nombre del usuario') }}
                            </button>
                            <button type="button" class="btn btn-success btn-sm btn-flat flex-grow-1 btn-buscar-puntual" data-tipo="comercio">
                                <i class="fa fa-building me-1"></i> {{ __('Por nombre del comercio') }}
                            </button>
                        </div>
                        {{-- Campos ocultos que se enviarán al servidor --}}
                        <input type="hidden" name="id" id="search_id">
                        <input type="hidden" name="nombre_usuario" id="search_nombre">
                        <input type="hidden" name="nombre_comercio" id="search_comercio">
                    </div>

                    <div class="separator separator-dashed border-gray-300 my-4"></div>

                    <!-- Filtros Avanzados -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">{{ __('Filtrar por Tema') }}</label>
                        <select name="tema" class="form-control select2 shadow-sm soporte-tecnico-select-tema">
                            <option value="">{{ __('Todos los temas') }}</option>
                            @if(isset($temas))
                                @foreach($temas as $tema)
                                    <option value="{{ $tema }}" {{ request('tema') == $tema ? 'selected' : '' }}>{{ $tema }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-2">{{ __('Prioridad') }}</label>
                            <select name="prioridad" class="form-control shadow-sm">
                                <option value="">{{ __('Todas') }}</option>
                                <option value="Baja" {{ request('prioridad') == 'Baja' ? 'selected' : '' }}>{{ __('Baja') }}</option>
                                <option value="Media" {{ request('prioridad') == 'Media' ? 'selected' : '' }}>{{ __('Media') }}</option>
                                <option value="Alta" {{ request('prioridad') == 'Alta' ? 'selected' : '' }}>{{ __('Alta') }}</option>
                                <option value="Urgente" {{ request('prioridad') == 'Urgente' ? 'selected' : '' }}>{{ __('Urgente') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-2">{{ __('Estatus') }}</label>
                            <select name="estatus" class="form-control shadow-sm">
                                <option value="">{{ __('Todos') }}</option>
                                <option value="Abierto" {{ request('estatus') == 'Abierto' ? 'selected' : '' }}>{{ __('Abierto') }}</option>
                                <option value="Cerrado" {{ request('estatus') == 'Cerrado' ? 'selected' : '' }}>{{ __('Cerrado') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-2">{{ __('Desde') }}</label>
                            <input type="date" name="fecha_inicio" class="form-control shadow-sm" value="{{ request('fecha_inicio') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-2">{{ __('Hasta') }}</label>
                            <input type="date" name="fecha_fin" class="form-control shadow-sm" value="{{ request('fecha_fin') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> {{ __('Cancelar') }}
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('reda.admin.general.soporte_tecnico.index') }}" class="btn btn-warning btn-flat">
                            <i class="fa fa-refresh me-1"></i> {{ __('Limpiar filtros') }}
                        </a>
                        <button type="submit" class="btn btn-primary btn-flat fw-bold">
                            <i class="fa fa-search me-1"></i> {{ __('Buscar ahora') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('public/js/reda/admin/general/soporte_tecnico/indexSoporteTecnico.min.js?v=' . time()) }}"></script>
@endpush
