@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="listado_experiencias" class="container-fluid container-fluid-90 mt-5 pt-4">

    <!-- SECCIÓN 1: FILTROS (PLACEHOLDER) -->
    <section class="seccion-filtros">
        <div class="row">
            <div class="col-12">
                <div class="placeholder-box" style="height: 60px;">
                    <i class="fas fa-filter mr-2"></i> {{ __('Espacio para Filtros de Negocios') }}
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN 2: DESTACADOS (CAROUSEL PLACEHOLDER) -->
    <section class="seccion-destacados">
        <div class="section-intro mb-4">
            <h2 class="text-24 font-weight-700">{{ __('Negocios Destacados') }}</h2>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="placeholder-box" style="height: 250px;">
                    <i class="fas fa-star mr-2"></i> {{ __('Espacio para Carrusel de Destacados') }}
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN 3: LISTADO PRINCIPAL -->
    <section class="seccion-listado">
        <div class="section-intro mb-4">
            <h2 class="text-24 font-weight-700">{{ __('Explora todos los Negocios') }}</h2>
        </div>

        <div class="row">
            @forelse($experiencias as $experiencia)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="negocio-card" onclick="window.location.href='#'">
                        <div class="negocio-img-container">
                            <span class="badge-categoria">{{ $experiencia->categoria_negocio }}</span>
                            <button class="btn-favorito"><i class="far fa-heart"></i></button>

                            @php
                                $foto = $experiencia->foto_portada;
                                $nombreFoto = $foto ? $foto->photo : null;
                                $rutaFoto = asset('public/images/default-image.png');
                                
                                if ($nombreFoto) {
                                    // Si el nombre ya contiene el ID (formato antiguo o inconsistente), lo usamos directo
                                    if (strpos($nombreFoto, '/') !== false) {
                                        $rutaFoto = asset('public/images/experiencias/' . $nombreFoto);
                                    } else {
                                        // Formato estándar según MediaController: images/experiencias/{id}/{filename}
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
                            <p class="negocio-precio">
                                @if($experiencia->precio_persona > 0)
                                    {!! moneyFormat($currentCurrency->symbol, $experiencia->precio_persona) !!}
                                    <span class="negocio-precio-tipo">/ {{ __('persona') }}</span>
                                @elseif($experiencia->precio_grupo > 0)
                                    {!! moneyFormat($currentCurrency->symbol, $experiencia->precio_grupo) !!}
                                    <span class="negocio-precio-tipo">/ {{ __('grupo') }}</span>
                                @else
                                    {{ __('Consultar precio') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-store-slash fa-4x mb-3 text-muted"></i>
                    <p class="text-18">{{ __('No se encontraron negocios disponibles en este momento.') }}</p>
                </div>
            @endforelse
        </div>

        <!-- PAGINACIÓN -->
        <div class="row mt-4 mb-5">
            <div class="col-12 d-flex justify-content-center">
                {{ $experiencias->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </section>
</div>
@stop

@section('validation_script')
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/listado-experiencias.min.js') }}"></script>
@endsection
