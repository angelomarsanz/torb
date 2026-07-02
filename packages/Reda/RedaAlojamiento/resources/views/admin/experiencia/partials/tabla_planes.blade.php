<!-- Vista de Escritorio: Tabla tradicional -->
<div class="table-responsive d-none d-md-block">
    <table class="table table-bordered table-striped table-hover f-14">
        <thead>
            <tr class="planes-negocio-bg-header-table">
                <th class="planes-negocio-w-40">{{ __('Nombre del plan') }}</th>
                <th class="planes-negocio-w-40">{{ __('Precio') }}</th>
                <th class="planes-negocio-w-20-center">{{ __('Acciones') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($planes) && $planes->count() > 0)
                @foreach($planes as $plan)
                    @php
                        $planesPago = is_array($plan->planes_pago) ? $plan->planes_pago : (json_decode($plan->planes_pago, true) ?: []);
                    @endphp
                    @foreach($planesPago as $index => $opcion)
                        <tr>
                            <td>
                                <strong class="text-blue">{{ $plan->nombre }}</strong>
                                @if($plan->destacado)
                                    <span class="label label-warning ml-2">Destacado</span>
                                @endif
                                @if(count($planesPago) > 1)
                                    <small class="text-muted d-block">Opción {{ $index + 1 }}</small>
                                @endif
                            </td>
                            <td>
                                {{ reda_money_format($opcion['moneda'] == 'dólar' ? '$' : 'Bs', $opcion['precio']) }} / {{ __($opcion['lapso']) }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-default btn-flat btn-view-plan" data-id="{{ $plan->id }}" data-toggle="tooltip" title="{{ __('Ver') }}">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-primary btn-flat btn-edit-plan" data-id="{{ $plan->id }}" data-toggle="tooltip" title="{{ __('Editar') }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-danger btn-flat btn-delete-plan" data-id="{{ $plan->id }}" data-index="{{ $index }}" data-toggle="tooltip" title="{{ __('Eliminar') }}">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center text-muted planes-negocio-padding-20">
                        <i class="fa fa-info-circle"></i> {{ __('No se encontraron planes configurados') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Vista Móvil: Lista de Tarjetas (Cards) -->
<div class="d-md-none">
    @if(!empty($planes) && $planes->count() > 0)
        @foreach($planes as $plan)
            @php
                $planesPago = is_array($plan->planes_pago) ? $plan->planes_pago : (json_decode($plan->planes_pago, true) ?: []);
            @endphp
            @foreach($planesPago as $index => $opcion)
                <div class="card mb-3 shadow-sm border-light planes-negocio-card-movil">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="planes-negocio-flex-1">
                                <small class="text-muted d-block planes-negocio-label-movil">{{ __('Nombre del plan') }}</small>
                                <strong class="text-blue f-15">{{ $plan->nombre }}</strong>
                                @if($plan->destacado)
                                    <span class="label label-warning ml-2 planes-negocio-badge-destacado-movil">Destacado</span>
                                @endif
                                @if(count($planesPago) > 1)
                                    <small class="text-muted d-block f-9">Opción {{ $index + 1 }}</small>
                                @endif
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-default btn-flat border btn-view-plan" data-id="{{ $plan->id }}" data-toggle="tooltip" title="{{ __('Ver') }}">
                                    <i class="fa fa-eye text-info"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-default btn-flat border btn-edit-plan" data-id="{{ $plan->id }}" data-toggle="tooltip" title="{{ __('Editar') }}">
                                    <i class="fa fa-edit text-primary"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-default btn-flat border btn-delete-plan" data-id="{{ $plan->id }}" data-index="{{ $index }}" data-toggle="tooltip" title="{{ __('Eliminar') }}">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted d-block planes-negocio-label-movil">{{ __('Precio') }}</small>
                            <p class="mb-0 text-dark f-14">{{ reda_money_format($opcion['moneda'] == 'dólar' ? '$' : 'Bs', $opcion['precio']) }} / {{ __($opcion['lapso']) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    @else
        <div class="text-center text-muted p-5 bg-light rounded">
            <i class="fa fa-info-circle fa-3x mb-3 planes-negocio-opacity-03"></i>
            <p>{{ __('No se encontraron planes configurados') }}</p>
        </div>
    @endif
</div>

<!-- Paginación -->
<div class="mt-3">
    {!! $planes->links('reda-alojamiento::admin.general.paginacion') !!}
</div>
