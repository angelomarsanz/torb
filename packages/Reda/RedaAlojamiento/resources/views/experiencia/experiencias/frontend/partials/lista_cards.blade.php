@forelse($experiencias as $experiencia)
    <div class="col-item-carrusel">
        <div class="negocio-card">
            {{-- Botón Favorito: Directamente en la tarjeta para estar por encima del overlay link --}}
            @php
                $esFavorito = false;
                if (Auth::check()) {
                    $esFavorito = \Reda\RedaAlojamiento\Models\Experiencia\FavoritoComercio::where('user_id', Auth::id())
                        ->where('experiencia_id', $experiencia->id)
                        ->exists();
                }
            @endphp
            <button class="btn-favorito" 
                    type="button" 
                    data-id="{{ $experiencia->id }}" 
                    data-owner-id="{{ $experiencia->user_id }}">
                <i class="{{ $esFavorito ? 'fas text-success' : 'far' }} fa-heart"></i>
            </button>

            <!-- Link Overlay: Cubre toda la tarjeta para la navegación -->
            <a href="{{ route('reda.negocios.experiencias.listado_productos_servicios', ['id' => $experiencia->id]) }}" class="negocio-card-overlay-link" title="{{ $experiencia->titulo }}">
                {{ $experiencia->titulo }}
            </a>

            <div class="negocio-img-container">
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
                    @if($experiencia->calificaciones_count > 0)
                        <i class="fas fa-star text-warning"></i>
                        <span class="font-weight-700 text-dark">{{ number_format($experiencia->calificaciones_avg_estrellas, 1, '.', '') }}</span>
                    @else
                        <i class="fas fa-star text-muted"></i>
                        <span class="text-muted small">{{ __('Sin reseñas') }}</span>
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
