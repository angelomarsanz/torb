@forelse($favoritos as $experiencia)
    <div class="favorito-item d-flex align-items-center p-3 border-bottom">
        <div class="favorito-img-wrapper mr-3">
            @php
                $foto = $experiencia->foto_portada;
                $rutaFoto = asset('public/images/default-image.png');
                if ($foto) {
                    if (strpos($foto->photo, '/') !== false) {
                        $rutaFoto = asset('public/images/experiencias/' . $foto->photo);
                    } else {
                        $rutaFoto = asset('public/images/experiencias/' . $experiencia->id . '/' . $foto->photo);
                    }
                }
            @endphp
            <img src="{{ $rutaFoto }}" alt="{{ $experiencia->titulo }}" class="img-fluid rounded shadow-sm">
        </div>
        <div class="favorito-info flex-grow-1">
            <h6 class="m-0 font-weight-700">
                <a href="{{ route('reda.negocios.experiencias.listado_productos_servicios', ['id' => $experiencia->id]) }}" class="text-dark">
                    {{ $experiencia->titulo }}
                </a>
            </h6>
            <div class="text-muted small">
                <i class="fas fa-star text-warning"></i> 
                {{ number_format($experiencia->calificaciones_avg_estrellas ?? 0, 1, '.', '') }}
            </div>
        </div>
        <div class="favorito-acciones">
            <button class="btn btn-sm btn-outline-danger btn-toggle-favorito-comercio" 
                    data-id="{{ $experiencia->id }}" 
                    data-owner-id="{{ $experiencia->user_id }}"
                    title="{{ __('Eliminar de favoritos') }}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
@empty
    <div class="text-center p-5">
        <i class="far fa-heart fa-3x text-muted mb-3 opacity-05"></i>
        <p class="text-muted m-0">{{ __('Aún no tienes comercios favoritos.') }}</p>
    </div>
@endforelse
