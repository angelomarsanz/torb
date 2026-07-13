@forelse($favoritos as $experiencia)
    <div class="favorito-item d-flex align-items-center p-3 border-bottom">
        {{-- Contenido: Imagen y Título alineados a la izquierda --}}
        <div class="favorito-content-block">
            <a href="{{ route('reda.negocios.experiencias.listado_productos_servicios', ['id' => $experiencia->id]) }}" 
               class="text-decoration-none d-flex flex-column align-items-start">
                <div class="favorito-img-wrapper mb-2" style="width: 100px;">
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
                    <img src="{{ $rutaFoto }}" alt="{{ $experiencia->titulo }}" class="img-fluid rounded shadow-sm" style="width: 100px; height: 70px; object-fit: cover;">
                </div>
                <div class="favorito-info">
                    <h6 class="m-0 font-weight-700 text-dark" style="line-height: 1.3;">
                        {{ $experiencia->titulo }}
                    </h6>
                </div>
            </a>
        </div>

        {{-- Botón Eliminar: Alineado a la derecha pero con margen controlado --}}
        <div class="favorito-acciones ml-auto pl-3">
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
