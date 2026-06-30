@php
    $datosComplementarios = json_decode($actividad->precios_monedas_complementarios, true) ?? [];
    $precioPromo = $datosComplementarios['precio_promocion'] ?? 0;
    $monedaPromoId = $datosComplementarios['moneda_precio_promocion'] ?? null;
    
    $monedaPromo = null;
    if ($monedaPromoId) {
        $monedaPromo = \App\Models\Currency::find($monedaPromoId);
    }
    
    $currencySymbol = $actividad->currency->symbol ?? '$';
    $promoCurrencySymbol = $monedaPromo->symbol ?? $currencySymbol;

    $iconoTipo = $actividad->tipo_producto_servicio == 'producto' ? 'fa-box' : 'fa-tools';
    $colorTipo = $actividad->tipo_producto_servicio == 'producto' ? 'text-primary' : 'text-info';

    $rutaFotoAct = asset('public/images/default-image.png');
    if ($actividad->foto_actividad) {
        $rutaFotoAct = asset('public/images/actividades_experiencias/' . $actividad->foto_actividad);
    }
@endphp

<div class="detalle-actividad-inline mb-5">
    <!-- IMAGEN CON MARCO -->
    <div class="actividad-inline-img-wrapper mb-4">
        @if($precioPromo > 0)
            <span class="badge badge-danger px-3 py-1 text-uppercase badge-oferta-inline">
                {{ __('Oferta') }}
            </span>
        @endif
        <img src="{{ $rutaFotoAct }}" class="img-fluid rounded-12 shadow-sm" alt="{{ $actividad->nombre_actividad }}">
    </div>

    <!-- DATOS DE DETALLE -->
    <div class="actividad-inline-info px-2">
        <div class="d-flex align-items-center mb-2">
            <i class="fas {{ $iconoTipo }} {{ $colorTipo }} mr-2"></i>
            <span class="text-uppercase font-weight-700 small tracking-wider text-muted">
                {{ __($actividad->tipo_producto_servicio) }}
            </span>
        </div>

        <h2 class="font-weight-700 mb-3 text-28">{{ $actividad->nombre_actividad }}</h2>
        
        <div class="actividad-inline-precios mb-4">
            @if($precioPromo > 0)
                <div class="d-flex align-items-baseline">
                    <span class="text-success text-32 font-weight-700 mr-3">
                        {{ $promoCurrencySymbol }} {{ reda_number_format($precioPromo, 2) }}
                    </span>
                    <span class="text-muted text-18 text-crossed">
                        {{ $currencySymbol }} {{ reda_number_format($actividad->precio, 2) }}
                    </span>
                </div>
            @else
                <span class="text-dark text-32 font-weight-700">
                    {{ $currencySymbol }} {{ reda_number_format($actividad->precio, 2) }}
                </span>
            @endif
        </div>

        <div class="actividad-inline-desc mb-4">
            <p class="text-16 text-muted text-justify">
                {!! nl2br(e($actividad->descripcion_actividad)) !!}
            </p>
        </div>

        <div class="row no-gutters mb-4">
            <div class="col-sm-6 pr-md-2 mb-3 mb-sm-0">
                <div class="p-3 bg-light h-100 rounded-12 border">
                    <p class="m-0 font-weight-700 x-small text-uppercase text-muted mb-1">{{ __('Disponibilidad') }}</p>
                    <p class="m-0 text-16 font-weight-600 {{ $actividad->disponibilidad == '1' ? 'text-success' : 'text-danger' }}">
                        {{ $actividad->disponibilidad == '1' ? __('Disponible') : __('No disponible') }}
                    </p>
                </div>
            </div>
            @if(isset($datosComplementarios['precio_pago_bolivares']) && $datosComplementarios['precio_pago_bolivares'] > 0)
                <div class="col-sm-6 pl-md-2">
                    <div class="p-3 bg-light h-100 rounded-12 border">
                        @php
                            $monedaBsId = $datosComplementarios['moneda_pago_bolivares'] ?? null;
                            $monedaBs = $monedaBsId ? \App\Models\Currency::find($monedaBsId) : null;
                            $simboloBs = $monedaBs->symbol ?? 'Bs.';
                        @endphp
                        <p class="m-0 font-weight-700 x-small text-uppercase text-muted mb-1">{{ __('Moneda Local') }}</p>
                        <p class="m-0 text-16 font-weight-600">
                            {{ $simboloBs }} {{ reda_number_format($datosComplementarios['precio_pago_bolivares'], 2) }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
