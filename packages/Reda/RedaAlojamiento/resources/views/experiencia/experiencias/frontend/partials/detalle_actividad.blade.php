@php
    $datosComplementarios = json_decode($actividad->precios_monedas_complementarios, true) ?? [];
    $precioPromo = $datosComplementarios['precio_promocion'] ?? 0;
    $monedaPromoId = $datosComplementarios['moneda_precio_promocion'] ?? null;
    
    // Obtenemos el símbolo de la moneda de promoción si existe
    $monedaPromo = null;
    if ($monedaPromoId) {
        $monedaPromo = \App\Models\Currency::find($monedaPromoId);
    }
    
    $currencySymbol = $actividad->currency->symbol ?? '$';
    $promoCurrencySymbol = $monedaPromo->symbol ?? $currencySymbol;

    // Icono según tipo
    $iconoTipo = $actividad->tipo_producto_servicio == 'producto' ? 'fa-box' : 'fa-tools';
    $colorTipo = $actividad->tipo_producto_servicio == 'producto' ? 'text-primary' : 'text-info';
@endphp

<div class="detalle-actividad-modal">
    <div class="row m-0">
        <!-- Columna de Imagen -->
        <div class="col-md-6 p-0">
            <div class="detalle-actividad-img-container">
                @if($precioPromo > 0)
                    <span class="badge badge-danger px-3 py-1 text-uppercase badge-oferta">
                        {{ __('Oferta') }}
                    </span>
                @endif
                @if($actividad->foto_actividad)
                    <img src="{{ asset('public/images/actividades_experiencias/'.$actividad->foto_actividad) }}" 
                         class="img-fluid w-100 h-100" alt="{{ $actividad->nombre_actividad }}">
                @else
                    <img src="{{ asset('public/images/default-image.png') }}" 
                         class="img-fluid w-100 h-100" alt="Default">
                @endif
            </div>
        </div>

        <!-- Columna de Información -->
        <div class="col-md-6 p-4 d-flex flex-column">
            <div class="mb-auto">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas {{ $iconoTipo }} {{ $colorTipo }} mr-2"></i>
                        <span class="text-uppercase font-weight-700 small tracking-wider text-muted">
                            {{ __($actividad->tipo_producto_servicio) }}
                        </span>
                    </div>
                </div>

                <h2 class="font-weight-700 mb-2 detalle-actividad-titulo">{{ $actividad->nombre_actividad }}</h2>
                
                <div class="detalle-actividad-precios mb-4">
                    @if($precioPromo > 0)
                        <div class="d-flex align-items-baseline">
                            <span class="text-success text-24 font-weight-700 mr-2">
                                {{ $promoCurrencySymbol }} {{ number_format($precioPromo, 2) }}
                            </span>
                            <span class="text-muted text-16 text-crossed">
                                {{ $currencySymbol }} {{ number_format($actividad->precio, 2) }}
                            </span>
                        </div>
                    @else
                        <span class="text-dark text-24 font-weight-700">
                            {{ $currencySymbol }} {{ number_format($actividad->precio, 2) }}
                        </span>
                    @endif
                </div>

                <div class="detalle-actividad-desc mb-4">
                    <p class="text-15 text-muted">
                        {!! nl2br(e($actividad->descripcion_actividad)) !!}
                    </p>
                </div>

                <div class="row no-gutters mb-4">
                    <div class="col-6 pr-2">
                        <div class="p-3 bg-light h-100 rounded-12">
                            <p class="m-0 font-weight-700 x-small text-uppercase text-muted mb-1">{{ __('Disponibilidad') }}</p>
                            <p class="m-0 text-14 font-weight-600 {{ $actividad->disponibilidad == '1' ? 'text-success' : 'text-danger' }}">
                                {{ $actividad->disponibilidad == '1' ? __('Disponible') : __('No disponible') }}
                            </p>
                        </div>
                    </div>
                    @if(isset($datosComplementarios['precio_pago_bolivares']) && $datosComplementarios['precio_pago_bolivares'] > 0)
                        <div class="col-6 pl-2">
                            <div class="p-3 bg-light h-100 rounded-12">
                                @php
                                    $monedaBsId = $datosComplementarios['moneda_pago_bolivares'] ?? null;
                                    $monedaBs = $monedaBsId ? \App\Models\Currency::find($monedaBsId) : null;
                                    $simboloBs = $monedaBs->symbol ?? 'Bs.';
                                @endphp
                                <p class="m-0 font-weight-700 x-small text-uppercase text-muted mb-1">{{ __('Moneda Local') }}</p>
                                <p class="m-0 text-14 font-weight-600">
                                    {{ $simboloBs }} {{ number_format($datosComplementarios['precio_pago_bolivares'], 2) }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-3">
                <button type="button" class="btn btn-primary btn-block py-3 font-weight-700 btn-cerrar-modal" data-dismiss="modal">
                    {{ __('Cerrar') }}
                </button>
            </div>
        </div>
    </div>
</div>
