@php
    $currencySymbol = $actividad->currency->symbol ?? $currentCurrency->symbol;
    $complementos = json_decode($actividad->precios_monedas_complementarios, true);
    $precioPromo = $complementos['precio_promocion'] ?? 0;
    $esPromoActiva = (isset($es_promo) && $es_promo) || ($precioPromo > 0);

    $rutaFotoAct = asset('public/images/default-image.png');
    if ($actividad->foto_actividad) {
        $rutaFotoAct = asset('public/images/actividades_experiencias/' . $actividad->foto_actividad);
    }
@endphp

<div class="producto-card" data-tipo-actividad="{{ $actividad->tipo_producto_servicio }}" data-id="{{ $actividad->id }}">
    <div class="producto-img-container">
        @if($esPromoActiva && $precioPromo > 0)
            <span class="badge-promo">{{ __('Oferta') }}</span>
        @endif
        <img src="{{ $rutaFotoAct }}" alt="{{ $actividad->nombre_actividad }}">
    </div>
    <div class="producto-info">
        <h4 class="producto-nombre">{{ $actividad->nombre_actividad }}</h4>
        <div class="producto-precio">
            @if($precioPromo > 0)
                <span class="precio-original">{{ $currencySymbol }} {{ number_format($actividad->precio, 2) }}</span>
                <span class="precio-promo">{{ $currencySymbol }} {{ number_format($precioPromo, 2) }}</span>
            @else
                <span class="font-weight-700 text-dark">{{ $currencySymbol }} {{ number_format($actividad->precio, 2) }}</span>
            @endif
        </div>
    </div>
</div>
