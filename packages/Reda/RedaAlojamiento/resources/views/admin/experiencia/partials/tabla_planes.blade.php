<!-- Vista de Escritorio: Tabla con Selector Dinámico -->
<div class="table-responsive d-none d-md-block">
    <table class="table table-hover table-planes-v2 f-14" style="background: white; border-radius: 8px; border-collapse: separate; border-spacing: 0; border: 1px solid #dee2e6;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="width: 35%; border-bottom: 2px solid #dee2e6; padding: 12px;">{{ __('Nombre del plan') }}</th>
                <th style="width: 45%; border-bottom: 2px solid #dee2e6; padding: 12px;">{{ __('Opciones y Precios') }}</th>
                <th style="width: 20%; border-bottom: 2px solid #dee2e6; padding: 12px;" class="text-center">{{ __('Acciones') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($planes) && $planes->count() > 0)
                @foreach($planes as $plan)
                    @php
                        $planesPago = is_array($plan->planes_pago) ? $plan->planes_pago : (json_decode($plan->planes_pago, true) ?: []);
                        $primeraOpcion = $planesPago[0] ?? null;
                    @endphp
                    <tr class="plan-row" data-id="{{ $plan->id }}">
                        <td style="padding: 12px;">
                            <div class="d-flex align-items-center">
                                <strong class="text-blue f-15">{{ $plan->nombre }}</strong>
                                @if($plan->destacado)
                                    <span class="label label-warning ml-2" style="font-size: 10px; border-radius: 4px;">{{ __('Destacado') }}</span>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-1">Orden: {{ $plan->orden }}</small>
                        </td>
                        <td style="padding: 12px;">
                            <div class="planes-negocio-selector-container">
                                <div class="planes-negocio-lapsos-group">
                                    @foreach($planesPago as $idx => $opcion)
                                        <button type="button" 
                                                class="planes-negocio-btn-lapso {{ $idx === 0 ? 'active' : '' }}" 
                                                data-price="{{ reda_money_format($opcion['moneda'] == 'dólar' ? '$' : 'Bs', $opcion['precio']) }}"
                                                data-lapso="{{ __($opcion['lapso']) }}">
                                            {{ __($opcion['lapso']) }}
                                        </button>
                                    @endforeach
                                </div>
                                <div class="planes-negocio-price-display">
                                    <span class="planes-negocio-price-amount">
                                        {{ $primeraOpcion ? reda_money_format($primeraOpcion['moneda'] == 'dólar' ? '$' : 'Bs', $primeraOpcion['precio']) : 'N/A' }}
                                    </span>
                                    <span class="planes-negocio-price-label">/ {{ $primeraOpcion ? __($primeraOpcion['lapso']) : '' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center" style="padding: 12px;">
                            <div class="btn-group shadow-xs">
                                <button type="button" class="btn btn-sm btn-default btn-flat btn-view-plan" data-id="{{ $plan->id }}" title="{{ __('Ver') }}" style="border-color: #ddd;">
                                    <i class="fa fa-eye text-info"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-default btn-flat btn-edit-plan" data-id="{{ $plan->id }}" title="{{ __('Editar') }}" style="border-color: #ddd;">
                                    <i class="fa fa-edit text-primary"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-default btn-flat btn-delete-plan" data-id="{{ $plan->id }}" title="{{ __('Eliminar') }}" style="border-color: #ddd;">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center text-muted p-5">
                        <i class="fa fa-info-circle fa-3x mb-3 d-block opacity-02"></i>
                        {{ __('No se encontraron planes configurados') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Vista Móvil: Tarjetas con Selector Dinámico -->
<div class="d-md-none">
    @if(!empty($planes) && $planes->count() > 0)
        @foreach($planes as $plan)
            @php
                $planesPago = is_array($plan->planes_pago) ? $plan->planes_pago : (json_decode($plan->planes_pago, true) ?: []);
                $primeraOpcion = $planesPago[0] ?? null;
            @endphp
            <div class="box box-solid mb-4 shadow-sm plan-card" data-id="{{ $plan->id }}" style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; background: white;">
                <div class="box-body p-3">
                    <div class="d-flex justify-content-between align-items-center planes-negocio-card-header">
                        <div>
                            <strong class="text-blue f-16">{{ $plan->nombre }}</strong>
                            @if($plan->destacado)
                                <span class="label label-warning ml-1" style="font-size: 10px; border-radius: 4px;">{{ __('Destacado') }}</span>
                            @endif
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-default btn-flat border btn-view-plan" data-id="{{ $plan->id }}">
                                <i class="fa fa-eye text-info"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-default btn-flat border btn-edit-plan" data-id="{{ $plan->id }}">
                                <i class="fa fa-edit text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-default btn-flat border btn-delete-plan" data-id="{{ $plan->id }}">
                                <i class="fa fa-trash text-danger"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="planes-negocio-selector-container">
                            <div class="planes-negocio-lapsos-group w-100">
                                @foreach($planesPago as $idx => $opcion)
                                    <button type="button" 
                                            class="planes-negocio-btn-lapso {{ $idx === 0 ? 'active' : '' }}" 
                                            style="flex: 1; text-align: center;"
                                            data-price="{{ reda_money_format($opcion['moneda'] == 'dólar' ? '$' : 'Bs', $opcion['precio']) }}"
                                            data-lapso="{{ __($opcion['lapso']) }}">
                                        {{ __($opcion['lapso']) }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="planes-negocio-price-display mt-2 justify-content-center">
                                <span class="planes-negocio-price-amount">
                                    {{ $primeraOpcion ? reda_money_format($primeraOpcion['moneda'] == 'dólar' ? '$' : 'Bs', $primeraOpcion['precio']) : 'N/A' }}
                                </span>
                                <span class="planes-negocio-price-label">/ {{ $primeraOpcion ? __($primeraOpcion['lapso']) : '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center text-muted p-5 bg-white rounded border shadow-xs">
            <i class="fa fa-info-circle fa-3x mb-3 opacity-02"></i>
            <p>{{ __('No se encontraron planes configurados') }}</p>
        </div>
    @endif
</div>

<!-- Paginación -->
<div class="mt-3 d-flex justify-content-center">
    {!! $planes->links('reda-alojamiento::admin.general.paginacion') !!}
</div>
