@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
    <style>
        .cursor-pointer { cursor: pointer; }
        .font-weight-600 { font-weight: 600; }
        .search-trigger-desktop:hover { transform: scale(1.01); transition: all 0.2s ease-in-out; }
        .ui-autocomplete { z-index: 2147483647 !important; } /* Máximo nivel para sobrepasar el modal */
        
        /* Ajuste para la barra sticky */
        .search-sticky-wrapper {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: white;
            transition: box-shadow 0.3s ease;
        }
        .search-sticky-wrapper.is-sticky {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        /* Ajuste de márgenes para que el contenido no quede oculto bajo la barra sticky */
        #listado_experiencias {
            padding-top: 20px !important;
        }

        @media (max-width: 991px) {
            .search-trigger-movil {
                padding: 12px 20px;
                border: 1px solid #ddd;
                border-radius: 30px;
                background: #fff;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
        }
    </style>
@endpush

@section('main')

<!-- BARRA DE BÚSQUEDA FLOTANTE (STICKY) -->
<div class="search-sticky-wrapper py-3 border-bottom" id="search_sticky_bar">
    <div class="container-fluid container-fluid-90">
        <!-- Trigger de Búsqueda Móvil (Solo visible en < 992px) -->
        <div class="d-lg-none seccion-filtros-movil">
            <div class="search-trigger-movil cursor-pointer shadow-sm" data-toggle="modal" data-target="#modalBusquedaComercios">
                <div class="d-flex align-items-center">
                    <i class="fas fa-search text-primary mr-3"></i>
                    <span class="text-muted font-weight-600">{{ __('¿Qué estás buscando?') }}</span>
                </div>
                <i class="fas fa-sliders-h text-muted"></i>
            </div>
        </div>

        <!-- Trigger de Búsqueda Desktop (Solo visible en >= 992px) -->
        <div class="d-none d-lg-block seccion-filtros-desktop">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-xl-5">
                    <div class="search-trigger-desktop cursor-pointer" data-toggle="modal" data-target="#modalBusquedaComercios">
                        <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-white border rounded-pill shadow-sm">
                            <span class="text-muted font-weight-600 ml-2">{{ __('¿Qué estás buscando?') }}</span>
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-search text-white text-14"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="listado_experiencias" class="container-fluid container-fluid-90">

    <!-- Modal de Búsqueda Unificado -->
    <div class="modal fade modal-busqueda-movil" id="modalBusquedaComercios" role="dialog" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-20 shadow-lg border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-700">{{ __('Búsqueda de Comercios') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form id="form_busqueda_negocios_modal" class="form-busqueda-comercios">
                        <!-- Buscar por Comercio (CON AUTOCOMPLETADO) -->
                        <div class="filtro-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Buscar comercio') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-store text-muted"></i></span>
                                </div>
                                <input type="text" name="nombre_comercio" id="input_nombre_comercio" class="form-control border-left-0" placeholder="{{ __('Nombre del negocio...') }}" autocomplete="off">
                            </div>
                        </div>

                        <!-- Buscar por Producto (CON AUTOCOMPLETADO) -->
                        <div class="filtro-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Buscar producto') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-shopping-bag text-muted"></i></span>
                                </div>
                                <input type="text" name="nombre_producto" id="input_nombre_producto" class="form-control border-left-0" placeholder="{{ __('¿Qué producto buscas?') }}" autocomplete="off">
                            </div>
                        </div>

                        <!-- Buscar por Servicio (CON AUTOCOMPLETADO) -->
                        <div class="filtro-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Buscar servicio') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-concierge-bell text-muted"></i></span>
                                </div>
                                <input type="text" name="nombre_servicio" id="input_nombre_servicio" class="form-control border-left-0" placeholder="{{ __('¿Qué servicio buscas?') }}" autocomplete="off">
                            </div>
                        </div>

                        <div class="filtro-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Categoría') }}</label>
                            <select name="categoria" class="filtro-categoria form-control rounded-10">
                                <option value="">{{ __('Todas las categorías') }}</option>
                                @foreach($categoriasNegocios as $clave => $nombre)
                                    <option value="{{ $clave }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filtro-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Distancia') }} <span class="radio-km-display badge badge-primary ml-2">25 km</span></label>
                            <input type="range" name="radio" class="filtro-radio custom-range" min="1" max="50" value="25">
                            <div class="d-flex justify-content-between mt-1 text-muted f-12">
                                <span>1 km</span>
                                <span>50 km</span>
                            </div>
                        </div>

                        <div class="filtro-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Ubicación') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                </div>
                                <input type="text" name="ubicacion_texto" class="filtro-ubicacion form-control border-left-0" placeholder="{{ __('Sector, ciudad, estado...') }}" autocomplete="off">
                            </div>
                            <input type="hidden" name="latitud" class="filtro-lat">
                            <input type="hidden" name="longitud" class="filtro-lng">
                        </div>

                        <div class="modal-footer-search mt-5">
                            <button type="submit" class="btn btn-primary btn-block btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-search mr-2"></i> {{ __('Buscar') }}
                            </button>
                            <button type="button" class="btn btn-link btn-block text-muted" data-dismiss="modal">
                                {{ __('Cerrar') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    

    <!-- SECCIÓN 2: DESTACADOS -->
    <section class="seccion-productos mb-4" id="seccion_destacados" @if($totalDestacados == 0) style="display:none;" @endif>
        <div class="header-seccion-carrusel">
            <h2 class="text-18 font-weight-700 m-0">{{ __('Comercios Destacados') }}</h2>
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
            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.lista_cards', ['experiencias' => $destacados])
            
            @if($totalDestacados > 10)
                @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_ver_todos_negocios', [
                    'items' => $destacados,
                    'tipo' => 'destacados',
                    'tituloModal' => __('Comercios Destacados'),
                    'total' => $totalDestacados
                ])
            @endif
        </div>
    </section>

    <!-- SECCIÓN 3: LISTADO PRINCIPAL -->
    <section class="seccion-productos mb-4">
        <div class="header-seccion-carrusel">
            <h2 class="text-18 font-weight-700 m-0">{{ __('Explora todos los Comercios') }}</h2>
            <div class="carrusel-controles-desktop d-none d-lg-flex">
                <button class="btn-carrusel-control btn-prev" data-target="#contenedor_listado_general" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn-carrusel-control btn-next" data-target="#contenedor_listado_general">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="container-carrusel-productos" id="contenedor_listado_general">
            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.lista_cards', ['experiencias' => $experiencias])

            @if($totalExperiencias > 10)
                @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_ver_todos_negocios', [
                    'items' => $experiencias,
                    'tipo' => 'todos',
                    'tituloModal' => __('Explora todos los Comercios'),
                    'total' => $totalExperiencias
                ])
            @endif
        </div>
    </section>
</div>

    @include('reda-alojamiento::general.modal_listado_infinito')

@stop

@section('validation_script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('vrent.google_map_key') }}&libraries=places"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <script>
        window.nombresComercios = @json($nombresComercios);
        window.nombresProductos = @json($nombresProductos);
        window.nombresServicios = @json($nombresServicios);
        console.log('Comercios cargados para autocomplete:', window.nombresComercios);
        console.log('Productos cargados para autocomplete:', window.nombresProductos);
        console.log('Servicios cargados para autocomplete:', window.nombresServicios);
    </script>

    @include('reda-alojamiento::general.main_footer')
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/listadoExperiencias.min.js?v=' . time()) }}"></script>
@endsection
