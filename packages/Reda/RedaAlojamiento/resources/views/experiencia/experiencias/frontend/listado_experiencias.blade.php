@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="listado_experiencias" class="container-fluid container-fluid-90 mt-5 pt-4">

    <!-- SECCIÓN 1: FILTROS (BARRA DE BÚSQUEDA) -->

    <!-- Trigger de Búsqueda Móvil (Solo visible en < 992px) -->
    <div class="d-lg-none seccion-filtros-movil mb-4">
        <div class="search-trigger-movil" data-toggle="modal" data-target="#modalBusquedaComercios">
            <i class="fas fa-search"></i>
            <span>{{ __('Buscar') }}</span>
        </div>
    </div>

    <!-- Barra de Búsqueda Desktop (Solo visible en >= 992px) -->
    <section class="seccion-filtros d-none d-lg-block">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="search-bar-negocios">
                    <form id="form_busqueda_negocios" class="form-busqueda-comercios row align-items-center m-0">
                        <!-- Categoría -->
                        <div class="col-md-3 search-item" id="item_categoria">
                            <label>{{ __('Categoría') }}</label>
                            <select name="categoria" class="filtro-categoria">
                                <option value="">{{ __('Todas las categorías') }}</option>
                                @foreach($categoriasNegocios as $clave => $nombre)
                                    <option value="{{ $clave }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Radio / Distancia -->
                        <div class="col-md-3 search-item" id="item_radio">
                            <label>{{ __('Distancia') }}<span class="radio-km-display">25 km</span></label>
                            <input type="range" name="radio" class="filtro-radio custom-range" min="1" max="50" value="25">
                        </div>

                        <!-- Ubicación -->
                        <div class="col-md-5 search-item" id="item_ubicacion">
                            <label>{{ __('Ubicación del negocio') }}</label>
                            <input type="text" name="ubicacion_texto" class="filtro-ubicacion" placeholder="{{ __('Sector, ciudad, estado...') }}" autocomplete="off">
                            <input type="hidden" name="latitud" class="filtro-lat">
                            <input type="hidden" name="longitud" class="filtro-lng">
                        </div>

                        <!-- Botón Buscar -->
                        <div class="col-md-1 d-flex justify-content-end p-0">
                            <button type="submit" class="btn-search-negocio">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal de Búsqueda Móvil -->
    <div class="modal fade modal-busqueda-movil" id="modalBusquedaComercios" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Búsqueda de Comercios') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form_busqueda_negocios_movil" class="form-busqueda-comercios">
                        <div class="filtro-movil-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Categoría') }}</label>
                            <select name="categoria" class="filtro-categoria form-control">
                                <option value="">{{ __('Todas las categorías') }}</option>
                                @foreach($categoriasNegocios as $clave => $nombre)
                                    <option value="{{ $clave }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filtro-movil-item mb-4">
                            <label class="font-weight-700 mb-2">{{ __('Distancia') }} <span class="radio-km-display badge badge-success ml-2">25 km</span></label>
                            <input type="range" name="radio" class="filtro-radio custom-range" min="1" max="50" value="25">
                            <div class="d-flex justify-content-between mt-1 text-muted f-12">
                                <span>1 km</span>
                                <span>50 km</span>
                            </div>
                        </div>

                        <div class="filtro-movil-item mb-4">
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
                            <button type="submit" class="btn btn-primary btn-block btn-lg btn-ejecutar-busqueda-movil">
                                <i class="fas fa-search mr-2"></i> {{ __('Buscar') }}
                            </button>
                            <button type="button" class="btn btn-link btn-block text-muted" data-dismiss="modal">
                                {{ __('Cancelar') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    

    <!-- SECCIÓN 2: DESTACADOS -->
    <section class="seccion-destacados mb-5">
        <div class="header-seccion-carrusel mb-4">
            <h2 class="text-20 font-weight-700 m-0">{{ __('Comercios Destacados') }}</h2>
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
    <section class="seccion-listado mb-5">
        <div class="header-seccion-carrusel mb-4">
            <h2 class="text-20 font-weight-700 m-0">{{ __('Explora todos los Comercios') }}</h2>
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
<div class="modal fade" id="modal-notificacion" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
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
                <button type="button" class="btn btn-primary" data-dismiss="modal" style="border-radius: 20px; padding: 8px 30px;">
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
    @include('reda-alojamiento::general.main_footer')
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/listadoExperiencias.min.js?v=' . time()) }}"></script>
@endsection
