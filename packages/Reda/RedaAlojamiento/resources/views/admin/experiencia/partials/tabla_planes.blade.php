<!-- Vista de Escritorio: Tabla tradicional -->
<div class="table-responsive d-none d-md-block">
    <table class="table table-bordered table-striped table-hover f-14">
        <thead>
            <tr style="background-color: #f4f4f4;">
                <th style="width: 40%">{{ __('Nombre del plan') }}</th>
                <th style="width: 40%">{{ __('Precio') }}</th>
                <th style="width: 20%; text-align: center;">{{ __('Acciones') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($planes) && $planes->count() > 0)
                @foreach($planes as $plan)
                    <tr>
                        <td>
                            <strong class="text-blue">{{ $plan->nombre }}</strong>
                            @if($plan->destacado)
                                <span class="label label-warning ml-2">Destacado</span>
                            @endif
                        </td>
                        <td>
                            {{ reda_money_format($plan->moneda == 'dólar' ? '$' : 'Bs', $plan->precio) }} / {{ $plan->lapso_pago }}
                        </td>
                        <td style="text-align: center;">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-primary btn-flat btn-edit-plan" data-id="{{ $plan->id }}" data-toggle="tooltip" title="{{ __('Editar') }}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-danger btn-flat btn-delete-plan" data-id="{{ $plan->id }}" data-toggle="tooltip" title="{{ __('Eliminar') }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center text-muted" style="padding: 20px;">
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
            <div class="card mb-3 shadow-sm border-light" style="border-radius: 10px; border: 1px solid #eee;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div style="flex: 1;">
                            <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">{{ __('Nombre del plan') }}</small>
                            <strong class="text-blue" style="font-size: 15px;">{{ $plan->nombre }}</strong>
                            @if($plan->destacado)
                                <span class="label label-warning ml-2" style="font-size: 9px;">Destacado</span>
                            @endif
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-default btn-flat border btn-edit-plan" data-id="{{ $plan->id }}" data-toggle="tooltip" title="{{ __('Editar') }}">
                                <i class="fa fa-edit text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-default btn-flat border btn-delete-plan" data-id="{{ $plan->id }}" data-toggle="tooltip" title="{{ __('Eliminar') }}">
                                <i class="fa fa-trash text-danger"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">{{ __('Precio') }}</small>
                        <p class="mb-0 text-dark f-14">{{ reda_money_format($plan->moneda == 'dólar' ? '$' : 'Bs', $plan->precio) }} / {{ $plan->lapso_pago }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center text-muted p-5 bg-light rounded">
            <i class="fa fa-info-circle fa-3x mb-3" style="opacity: 0.3;"></i>
            <p>{{ __('No se encontraron planes configurados') }}</p>
        </div>
    @endif
</div>

<!-- Paginación -->
<div class="mt-3">
    {!! $planes->links('reda-alojamiento::admin.general.paginacion') !!}
</div>
