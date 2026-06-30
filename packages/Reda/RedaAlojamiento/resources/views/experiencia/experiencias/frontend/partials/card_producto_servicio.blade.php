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

@if(isset($url_redireccion) && $url_redireccion)
    <a href="{{ $url_redireccion }}" class="producto-card-wrapper-link text-decoration-none">
@endif

<div class="producto-card" 
     data-tipo-actividad="{{ $actividad->tipo_producto_servicio }}" 
     data-id="{{ $actividad->id }}"
     data-id-experiencia="{{ $actividad->experiencia_id }}">
    <div class="producto-img-container">
        @if($esPromoActiva && $precioPromo > 0)
            <span class="badge-promo">{{ __('Oferta') }}</span>
        @endif
        <img src="{{ $rutaFotoAct }}" alt="{{ $actividad->nombre_actividad }}">
    </div>
    <div class="producto-info">
        <h4 class="producto-nombre">{{ $actividad->nombre_actividad }}</h4>
        
        @if(isset($mostrar_comercio) && $mostrar_comercio && $actividad->experiencia)
            <div class="producto-comercio mb-1">
                <i class="fas fa-store text-muted mr-1 small"></i>
                <span class="text-muted small font-weight-600">{{ $actividad->experiencia->titulo }}</span>
            </div>
        @endif

        <div class="producto-precio">
            @if($precioPromo > 0)
                <span class="precio-original">{{ $currencySymbol }} {{ reda_number_format($actividad->precio, 2) }}</span>
                <span class="precio-promo">{{ $currencySymbol }} {{ reda_number_format($precioPromo, 2) }}</span>
            @else
                <span class="font-weight-700 text-dark">{{ $currencySymbol }} {{ reda_number_format($actividad->precio, 2) }}</span>
            @endif
        </div>
    </div>
</div>

@if(isset($url_redireccion) && $url_redireccion)
    </a>
@endif
