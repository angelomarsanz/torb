@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
    <style>
        .search-results-header {
            background: #f8f9fa;
            padding: 40px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            margin-top: 90px !important;
        }
        .search-term-highlight {
            color: #28a745;
            font-weight: 700;
        }
        #productos_servicios_encontrados {
            padding-bottom: 50px;
        }

        @media (max-width: 767px) {
            .search-results-header {
                margin-top: 80px !important;
            }
        }
    </style>
@endpush

@section('main')

<div class="search-results-header">
    <div class="container-fluid container-fluid-90">
        <h1 class="text-28 font-weight-700 m-0">
            @if($busqueda)
                {{ __('Resultados para') }}: <span class="search-term-highlight">"{{ $busqueda }}"</span>
            @else
                {{ __('Explorando') }} {{ $tipo == 'producto' ? __('Productos') : ($tipo == 'servicio' ? __('Servicios') : __('Productos y Servicios')) }}
            @endif
        </h1>
        <p class="text-muted mt-2">
            {{ $totalActividades }} {{ trans_choice('resultado encontrado|resultados encontrados', $totalActividades) }}
        </p>
    </div>
</div>

<div id="productos_servicios_encontrados" class="container-fluid container-fluid-90">
    
    <!-- SECCIÓN 1: DESTACADOS -->
    @if($totalDestacados > 0)
    <section class="seccion-productos mb-5" id="seccion_destacados">
        <div class="header-seccion-carrusel">
            <h2 class="text-20 font-weight-700 m-0">{{ __('Resultados Destacados') }}</h2>
            <div class="carrusel-controles-desktop d-none d-lg-flex">
                <button class="btn-carrusel-control btn-prev" data-target="#contenedor_destacados" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn-carrusel-control btn-next" data-target="#contenedor_destacados">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <div class="container-carrusel-productos" id="contenedor_destacados">
            @foreach($actividadesDestacadas as $actividad)
                @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_producto_servicio', [
                    'actividad' => $actividad,
                    'mostrar_comercio' => true,
                    'url_redireccion' => route('reda.negocios.experiencias.listado_productos_servicios', [
                        'id' => $actividad->experiencia_id,
                        'actividad_id' => $actividad->id,
                        'q' => $busqueda
                    ])
                ])
            @endforeach
        </div>
    </section>
    @endif

    <!-- SECCIÓN 2: TODOS LOS RESULTADOS -->
    <section class="seccion-productos mb-4" id="seccion_todos">
        <div class="header-seccion-carrusel">
            <h2 class="text-20 font-weight-700 m-0">{{ __('Explorar Todo') }}</h2>
            <div class="carrusel-controles-desktop d-none d-lg-flex">
                <button class="btn-carrusel-control btn-prev" data-target="#contenedor_todos" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn-carrusel-control btn-next" data-target="#contenedor_todos">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="container-carrusel-productos" id="contenedor_todos">
            @foreach($actividades as $actividad)
                @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_producto_servicio', [
                    'actividad' => $actividad,
                    'mostrar_comercio' => true,
                    'url_redireccion' => route('reda.negocios.experiencias.listado_productos_servicios', [
                        'id' => $actividad->experiencia_id,
                        'actividad_id' => $actividad->id,
                        'q' => $busqueda
                    ])
                ])
            @endforeach

            @if($actividades->hasMorePages())
                <div class="card-ver-todos cursor-pointer d-flex flex-column align-items-center justify-content-center"
                     data-tipo="todos"
                     data-busqueda="{{ $busqueda }}"
                     data-tipo-filtro="{{ $tipo }}"
                     data-titulo-modal="{{ __('Todos los resultados') }}">
                    <div class="icon-wrapper mb-2">
                        <i class="fas fa-plus-circle fa-2x text-primary"></i>
                    </div>
                    <span class="font-weight-700">{{ __('Ver todos') }}</span>
                    <small class="text-muted">({{ $totalActividades }})</small>
                </div>
            @endif
        </div>
    </section>

</div>

<!-- Modal Detalle Actividad -->
<div class="modal fade" id="modalDetalleActividad" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content modal-negocio-rounded">
            <div class="modal-header border-0 pb-0 modal-header-abs-right">
                <button type="button" class="close bg-white rounded-circle shadow-sm btn-close-modal-custom" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" id="bodyDetalleActividad">
                <div class="text-center p-5">
                    <i class="fa fa-spinner fa-spin fa-3x text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@include('reda-alojamiento::general.modal_listado_infinito')

@stop

@section('validation_script')
    @include('reda-alojamiento::general.main_footer')
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/productosServiciosEncontrados.min.js?v=' . time()) }}"></script>
@endsection
