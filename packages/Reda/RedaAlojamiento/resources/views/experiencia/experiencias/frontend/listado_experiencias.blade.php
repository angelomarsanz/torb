@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
    <style>
        .cursor-pointer { cursor: pointer; }
        .font-weight-600 { font-weight: 600; }
        .search-trigger-desktop:hover { transform: scale(1.02); transition: all 0.2s ease-in-out; }
        .ui-autocomplete { z-index: 2147483647 !important; } /* Para que aparezca sobre el modal */
    </style>
@endpush

@section('main')
<div id="listado_experiencias" class="container-fluid container-fluid-90 mt-5 pt-4">

    <!-- SECCIÓN 1: FILTROS (BARRA DE BÚSQUEDA) -->

    <!-- Trigger de Búsqueda Móvil (Solo visible en < 992px) -->
    <div class="d-lg-none seccion-filtros-movil mb-4">
        <div class="search-trigger-movil" data-toggle="modal" data-target="#modalBusquedaComercios">
            <i class="fas fa-search"></i>
            <span>{{ __('¿Qué estás buscando?') }}</span>
        </div>
    </div>

    <!-- Trigger de Búsqueda Desktop (Solo visible en >= 992px) -->
    <section class="seccion-filtros-desktop d-none d-lg-block mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <div class="search-trigger-desktop cursor-pointer" data-toggle="modal" data-target="#modalBusquedaComercios">
                    <div class="d-flex align-items-center justify-content-between px-4 py-3 bg-white border rounded-pill shadow-sm">
                        <span class="text-muted font-weight-600 ml-2">{{ __('¿Qué estás buscando?') }}</span>
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-search text-white text-14"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de Búsqueda (Unificado para Móvil y Desktop) -->
    <div class="modal fade modal-busqueda-movil" id="modalBusquedaComercios" role="dialog" aria-hidden="true">
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
                        <!-- NUEVO: Buscar por Comercio -->
                        <div class="filtro-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Buscar comercio') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-store text-muted"></i></span>
                                </div>
                                <input type="text" name="nombre_comercio" id="input_nombre_comercio" class="form-control border-left-0" placeholder="{{ __('Nombre del negocio...') }}" autocomplete="off">
                            </div>
                        </div>

                        <!-- NUEVO: Buscar por Producto o Servicio (Por ahora solo visual) -->
                        <div class="filtro-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Buscar producto o servicio') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-shopping-bag text-muted"></i></span>
                                </div>
                                <input type="text" name="producto_servicio" class="form-control border-left-0" placeholder="{{ __('¿Qué producto o servicio buscas?') }}" autocomplete="off">
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
                            <button type="submit" class="btn btn-primary btn-block btn-lg rounded-pill btn-ejecutar-busqueda-movil shadow-sm">
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

<!-- Modal Notificación (Si no está en el layout) -->
<div class="modal fade z-1080" id="modal-notificacion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content rounded-15 border-0 shadow-modal">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center pt-0">
                <div id="notificacion-icono" class="mb-3"></div>
                <h4 id="notificacion-titulo" class="modal-title mb-2 font-weight-bold"></h4>
                <p id="notificacion-mensaje" class="text-muted f-14 mb-0"></p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-primary rounded-20 py-8 px-30" data-dismiss="modal">
                    {{ __('Aceptar') }}
                </button>
            </div>
        </div>
    </div>
</div>

@include('reda-alojamiento::general.modal_listado_infinito')

@stop

@section('validation_script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('vrent.google_map_key') }}&libraries=places"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <script>
        window.nombresComercios = @json($nombresComercios);
    </script>

    @include('reda-alojamiento::general.main_footer')
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/listadoExperiencias.min.js?v=' . time()) }}"></script>
@endsection
