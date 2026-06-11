@forelse($experiencias as $experiencia)
    <div class="col-item-carrusel">
        <div class="negocio-card">
            {{-- Botón Favorito: Directamente en la tarjeta para estar por encima del overlay link --}}
            <button class="btn-favorito" type="button">
                <i class="far fa-heart"></i>
            </button>

            <!-- Link Overlay: Cubre toda la tarjeta para la navegación -->
            <a href="{{ route('reda.negocios.experiencias.listado_productos_servicios', ['id' => $experiencia->id]) }}" class="negocio-card-overlay-link" title="{{ $experiencia->titulo }}">
                {{ $experiencia->titulo }}
            </a>

            <div class="negocio-img-container">
                <span class="badge-categoria">{{ $experiencia->categoria_negocio }}</span>

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
                    {{ $experiencia->ubicacion['ciudad'] ?? __('Ubicación no especificada') }}
                </p>
                <p class="negocio-rating star-rating">
                    <i class="fas fa-star"></i> 
                    @if($experiencia->calificaciones_count > 0)
                        {{ number_format($experiencia->calificaciones_avg_estrellas, 1) }} ({{ $experiencia->calificaciones_count }})
                    @else
                        <span class="text-muted font-size-13">{{ __('Sin reseñas') }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5">
        <i class="fas fa-store-slash fa-4x mb-3 text-muted"></i>
        <p class="text-16">{{ __('No se encontraron negocios disponibles con estos criterios.') }}</p>
    </div>
@endforelse
