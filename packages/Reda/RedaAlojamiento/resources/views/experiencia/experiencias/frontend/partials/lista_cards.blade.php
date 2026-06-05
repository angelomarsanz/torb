@forelse($experiencias as $experiencia)
    <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="negocio-card" onclick="window.location.href='#'">
            <div class="negocio-img-container">
                <span class="badge-categoria">{{ $experiencia->categoria_negocio }}</span>
                <button class="btn-favorito"><i class="far fa-heart"></i></button>

                @php
                    $foto = $experiencia->foto_portada;
                    $nombreFoto = $foto ? $foto->photo : null;
                    $rutaFoto = asset('public/images/default-image.png');
                    
                    if ($nombreFoto) {
                        if (strpos($nombreFoto, '/') !== false) {
                            $rutaFoto = asset('public/images/experiencias/' . $nombreFoto);
                        } else {
                            $rutaFoto = asset('public/images/experiencias/' . $experiencia->id . '/' . $nombreFoto);
                        }
                    }
                @endphp

                <img src="{{ $rutaFoto }}" alt="{{ $experiencia->titulo }}">
            </div>

            <div class="negocio-info">
                <h3 class="negocio-titulo">{{ $experiencia->titulo }}</h3>
                <p class="negocio-ubicacion">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    {{ $experiencia->ubicacion['ciudad'] ?? __('Ubicación no especificada') }}
                </p>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5">
        <i class="fas fa-store-slash fa-4x mb-3 text-muted"></i>
        <p class="text-18">{{ __('No se encontraron negocios disponibles con estos criterios.') }}</p>
    </div>
@endforelse
