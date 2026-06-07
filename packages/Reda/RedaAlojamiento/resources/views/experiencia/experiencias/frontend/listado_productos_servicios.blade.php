@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="listado_productos_servicios" class="container-fluid p-0">

    <!-- SECCIÓN 1: CARRUSEL DE FOTOS DEL NEGOCIO -->
    <section class="seccion-carrusel-negocio">
        @php
            $fotos = $experiencia->fotos;
            // Ordenar para que la de portada vaya primero
            $fotosOrdenadas = $fotos->sortByDesc('cover_photo');
        @endphp
        <div id="carouselNegocio" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                @forelse($fotosOrdenadas as $index => $foto)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ asset('public/images/experiencias/' . $experiencia->id . '/' . $foto->photo) }}" alt="{{ $experiencia->titulo }}">
                    </div>
                @empty
                    <div class="carousel-item active">
                        <img src="{{ asset('public/images/default-image.png') }}" alt="{{ $experiencia->titulo }}">
                    </div>
                @endforelse
            </div>
            @if($fotosOrdenadas->count() > 1)
                <a class="carousel-control-prev" href="#carouselNegocio" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselNegocio" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            @endif
        </div>
    </section>

    <!-- SECCIÓN 2: INFORMACIÓN DEL NEGOCIO -->
    <section class="seccion-info-negocio">
        <h1 class="negocio-detalle-titulo">{{ $experiencia->titulo }}</h1>
        <p class="negocio-detalle-desc">{{ $experiencia->descripcion }}</p>
        <div class="negocio-detalle-rating">
            <i class="fas fa-star"></i> 4.92
        </div>
    </section>

    <!-- SECCIÓN 3: BARRA DE BÚSQUEDA -->
    <section class="seccion-busqueda-actividades">
        <!-- Vista Desktop -->
        <div class="d-none d-lg-block">
            <div class="search-bar-actividades d-flex align-items-center">
                <div class="flex-grow-1 pr-3">
                    <select id="filtro_tipo_actividad" class="form-control border-0 shadow-none">
                        <option value="">{{ __('¿Qué estás buscando?') }}</option>
                        <option value="producto">{{ __('Productos') }}</option>
                        <option value="servicio">{{ __('Servicios') }}</option>
                    </select>
                </div>
                <button class="btn btn-primary rounded-circle p-2" style="width: 40px; height: 40px;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Vista Móvil -->
        <div class="d-lg-none">
            <div class="search-bar-actividades d-flex align-items-center justify-content-between" data-toggle="modal" data-target="#modalBusquedaActividades">
                <span class="text-muted"><i class="fas fa-search mr-2"></i> {{ __('Buscar') }}</span>
                <i class="fas fa-sliders-h text-muted"></i>
            </div>
        </div>
    </section>

    <!-- Modal de Búsqueda Móvil -->
    <div class="modal fade" id="modalBusquedaActividades" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 24px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title font-weight-700">{{ __('Filtrar por') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-600">{{ __('Tipo') }}</label>
                        <select class="form-control custom-select" id="filtro_tipo_actividad_movil">
                            <option value="">{{ __('Todos') }}</option>
                            <option value="producto">{{ __('Productos') }}</option>
                            <option value="servicio">{{ __('Servicios') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary btn-block btn-lg" style="border-radius: 12px;">{{ __('Aplicar') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 4: PRODUCTOS Y SERVICIOS EN PROMOCIÓN -->
    @if($promociones->count() > 0)
    <section class="seccion-productos">
        <h2 class="text-18 font-weight-700 mb-3">{{ __('Promociones Especiales') }}</h2>
        <div class="container-carrusel-productos">
            @foreach($promociones as $promo)
                @php
                    $complementos = json_decode($promo->precios_monedas_complementarios, true);
                    $precioPromo = $complementos['precio_promocion'] ?? 0;
                    $currencySymbol = $promo->currency->symbol ?? $currentCurrency->symbol;
                @endphp
                <div class="producto-card" data-tipo-actividad="{{ $promo->tipo_producto_servicio }}">
                    <div class="producto-img-container">
                        <span class="badge-promo">{{ __('Oferta') }}</span>
                        @php
                            $rutaFotoAct = asset('public/images/default-image.png');
                            if ($promo->foto_actividad) {
                                $rutaFotoAct = asset('public/images/actividades_experiencias/' . $promo->id . '/' . $promo->foto_actividad);
                            }
                        @endphp
                        <img src="{{ $rutaFotoAct }}" alt="{{ $promo->nombre_actividad }}">
                    </div>
                    <div class="producto-info">
                        <h4 class="producto-nombre">{{ $promo->nombre_actividad }}</h4>
                        <div class="producto-precio">
                            <span class="precio-original">{{ $currencySymbol }}{{ number_format($promo->precio, 2) }}</span>
                            <span class="precio-promo">{{ $currencySymbol }}{{ number_format($precioPromo, 2) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- SECCIÓN 5: EXPLORAR TODOS -->
    <section class="seccion-productos">
        <h2 class="text-18 font-weight-700 mb-3">{{ __('Explorar Todo') }}</h2>
        <div class="container-carrusel-productos" id="contenedor_todos_productos">
            @foreach($actividades as $actividad)
                @php
                    $currencySymbol = $actividad->currency->symbol ?? $currentCurrency->symbol;
                    $complementos = json_decode($actividad->precios_monedas_complementarios, true);
                    $precioPromo = $complementos['precio_promocion'] ?? 0;
                @endphp
                <div class="producto-card" data-tipo-actividad="{{ $actividad->tipo_producto_servicio }}">
                    <div class="producto-img-container">
                        @php
                            $rutaFotoAct = asset('public/images/default-image.png');
                            if ($actividad->foto_actividad) {
                                $rutaFotoAct = asset('public/images/actividades_experiencias/' . $actividad->id . '/' . $actividad->foto_actividad);
                            }
                        @endphp
                        <img src="{{ $rutaFotoAct }}" alt="{{ $actividad->nombre_actividad }}">
                    </div>
                    <div class="producto-info">
                        <h4 class="producto-nombre">{{ $actividad->nombre_actividad }}</h4>
                        <div class="producto-precio">
                            @if($precioPromo > 0)
                                <span class="precio-original">{{ $currencySymbol }}{{ number_format($actividad->precio, 2) }}</span>
                                <span class="precio-promo">{{ $currencySymbol }}{{ number_format($precioPromo, 2) }}</span>
                            @else
                                <span class="font-weight-700">{{ $currencySymbol }}{{ number_format($actividad->precio, 2) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

</div>
@stop

@section('validation_script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('vrent.google_map_key') }}&libraries=places"></script>
    <script src="{{ asset('public/js/reda/general/notificaciones.min.js?v=' . time()) }}"></script>
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/listadoProductosServicios.min.js?v=' . time()) }}"></script>
@endsection
