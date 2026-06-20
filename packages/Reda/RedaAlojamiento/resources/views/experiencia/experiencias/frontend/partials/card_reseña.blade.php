@php
    $fotoUsuario = $calificacion->usuario->profile_image;
    $rutaFotoUsuario = $fotoUsuario
        ? asset('public/images/profile/' . $calificacion->usuario->id . '/' . $fotoUsuario)
        : asset('public/images/default-profile.png');

    $primerNombre = explode(' ', trim($calificacion->usuario->first_name))[0];
    $primerApellido = explode(' ', trim($calificacion->usuario->last_name))[0];
@endphp

<div class="producto-card reseña-card">
    <div class="card border-0 shadow-none bg-transparent h-100">
        <div class="card-body p-0 d-flex flex-column">
            <div class="d-flex align-items-center mb-2">
                <img src="{{ $rutaFotoUsuario }}" class="img-profile-list mr-3 img-size-48 rounded-circle shadow-sm object-cover">
                <div class="overflow-hidden">
                    <div class="font-weight-700 text-15 text-truncate">{{ $primerNombre }} {{ $primerApellido }}</div>
                    <div class="text-muted small">{{ $calificacion->created_at->format('M Y') }}</div>
                </div>
            </div>
            <div class="star-rating mb-2 d-flex align-items-center">
                <span class="mr-2 d-flex align-items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="{{ $i <= $calificacion->estrellas ? 'fas' : 'far' }} fa-star text-warning star-rating-12"></i>
                    @endfor
                </span>
                <span class="font-weight-700 text-12">{{ number_format($calificacion->estrellas, 1, '.', '') }}</span>
            </div>
            <div class="reseña-comentario-wrapper">
                <p class="text-14 text-justify mb-0 reseña-comentario">
                    {{ $calificacion->comentario }}
                </p>
                <button class="btn-leer-mas-reseña">{{ __('Más') }}</button>
            </div>
        </div>
    </div>
</div>
