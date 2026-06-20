@php
    $esReseña = ($tipo === 'reseñas');
    $fotosCollage = [];
    
    if (!$esReseña) {
        foreach($items->take(3) as $item) {
            if (isset($item->foto_actividad)) {
                $fotosCollage[] = asset('public/images/actividades_experiencias/' . $item->foto_actividad);
            } elseif (isset($item->usuario)) {
                $fotoUsuario = $item->usuario->profile_image;
                $fotosCollage[] = $fotoUsuario
                    ? asset('public/images/profile/' . $item->usuario->id . '/' . $fotoUsuario)
                    : asset('public/images/default-profile.png');
            } else {
                $fotosCollage[] = asset('public/images/default-image.png');
            }
        }
        // Rellenar si hay menos de 3
        while(count($fotosCollage) < 3) {
            $fotosCollage[] = asset('public/images/default-image.png');
        }
    }
@endphp

<div class="producto-card card-ver-todos" 
     data-tipo="{{ $tipo }}" 
     data-id-negocio="{{ $idNegocio }}" 
     data-titulo-modal="{{ $tituloModal }}">
    <div class="producto-img-container collage-container {{ $esReseña ? 'collage-stars' : '' }}">
        <div class="collage-wrapper">
            @if($esReseña)
                <i class="fas fa-star img-collage star-1"></i>
                <i class="fas fa-star img-collage star-2"></i>
                <i class="fas fa-star img-collage star-3"></i>
            @else
                <img src="{{ $fotosCollage[0] }}" class="img-collage img-1">
                <img src="{{ $fotosCollage[1] }}" class="img-collage img-2">
                <img src="{{ $fotosCollage[2] }}" class="img-collage img-3">
            @endif
        </div>
        <div class="overlay-ver-todos">
            <i class="fas fa-plus"></i>
        </div>
    </div>
    <div class="producto-info">
        <h4 class="producto-nombre">{{ $esReseña ? __('Ver todas') : __('Ver todos') }}</h4>
        <div class="producto-precio">
            @if($esReseña)
                <span>{{ $total }} {{ trans_choice('Reseña|Reseñas', $total) }}</span>
            @else
                <span>{{ $total }} {{ __('elementos') }}</span>
            @endif
        </div>
    </div>
</div>
