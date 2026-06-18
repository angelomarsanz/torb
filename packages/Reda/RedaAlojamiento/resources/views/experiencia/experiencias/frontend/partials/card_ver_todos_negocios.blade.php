@php
    $fotosCollage = [];
    foreach($items->take(3) as $item) {
        $foto = $item->foto_portada;
        $nombreFoto = $foto ? $foto->photo : null;
        
        if ($nombreFoto) {
            if (strpos($nombreFoto, '/') !== false) {
                $fotosCollage[] = asset('public/images/experiencias/' . $nombreFoto);
            } else {
                $fotosCollage[] = asset('public/images/experiencias/' . $item->id . '/' . $nombreFoto);
            }
        } else {
            $fotosCollage[] = asset('public/images/default-image.png');
        }
    }
    // Rellenar si hay menos de 3
    while(count($fotosCollage) < 3) {
        $fotosCollage[] = asset('public/images/default-image.png');
    }
@endphp

<div class="col-item-carrusel">
    <div class="negocio-card card-ver-todos" 
         data-tipo="{{ $tipo }}" 
         data-titulo-modal="{{ $tituloModal }}">
        <div class="negocio-img-container collage-container">
            <div class="collage-wrapper">
                <img src="{{ $fotosCollage[0] }}" class="img-collage img-1">
                <img src="{{ $fotosCollage[1] }}" class="img-collage img-2">
                <img src="{{ $fotosCollage[2] }}" class="img-collage img-3">
            </div>
            <div class="overlay-ver-todos">
                <i class="fas fa-plus"></i>
            </div>
        </div>
        <div class="negocio-info">
            <h3 class="negocio-titulo font-weight-700">{{ __('Ver todos') }}</h3>
            <p class="negocio-ubicacion">
                <span class="text-muted small m-0">{{ $total }} {{ __('comercios') }}</span>
            </p>
        </div>
    </div>
</div>
