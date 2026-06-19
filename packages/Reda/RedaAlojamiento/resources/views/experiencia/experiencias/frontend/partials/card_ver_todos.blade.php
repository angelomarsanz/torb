@php
    $fotosCollage = [];
    foreach($items->take(3) as $item) {
        if ($item->foto_actividad) {
            $fotosCollage[] = asset('public/images/actividades_experiencias/' . $item->foto_actividad);
        } else {
            $fotosCollage[] = asset('public/images/default-image.png');
        }
    }
    // Rellenar si hay menos de 3
    while(count($fotosCollage) < 3) {
        $fotosCollage[] = asset('public/images/default-image.png');
    }
@endphp

<div class="producto-card card-ver-todos" 
     data-tipo="{{ $tipo }}" 
     data-id-negocio="{{ $idNegocio }}" 
     data-titulo-modal="{{ $tituloModal }}">
    <div class="producto-img-container collage-container">
        <div class="collage-wrapper">
            <img src="{{ $fotosCollage[0] }}" class="img-collage img-1">
            <img src="{{ $fotosCollage[1] }}" class="img-collage img-2">
            <img src="{{ $fotosCollage[2] }}" class="img-collage img-3">
        </div>
        <div class="overlay-ver-todos">
            <i class="fas fa-plus"></i>
        </div>
    </div>
    <div class="producto-info">
        <h4 class="producto-nombre">{{ __('Ver todos') }}</h4>
        <div class="producto-precio">
            <span>{{ $total }} {{ __('elementos') }}</span>
        </div>
    </div>
</div>
