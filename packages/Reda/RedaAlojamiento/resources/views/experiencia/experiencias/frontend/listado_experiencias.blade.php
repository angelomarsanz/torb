@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="listado_experiencias" class="container-fluid container-fluid-90 mt-5 pt-4">

    <!-- SECCIÓN 1: FILTROS (BARRA DE BÚSQUEDA) -->
    <section class="seccion-filtros">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="search-bar-negocios">
                    <form id="form_busqueda_negocios" class="row align-items-center m-0">
                        <!-- Categoría -->
                        <div class="col-md-3 search-item" id="item_categoria">
                            <label>{{ __('Categoría') }}</label>
                            <select name="categoria" id="filtro_categoria">
                                <option value="">{{ __('Todas las categorías') }}</option>
                                @foreach($categoriasNegocios as $clave => $nombre)
                                    <option value="{{ $clave }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Radio / Distancia -->
                        <div class="col-md-3 search-item" id="item_radio">
                            <label>{{ __('Distancia') }}<span id="radio_km_display">25 km</span></label>
                            <input type="range" name="radio" id="filtro_radio" min="1" max="50" value="25" class="custom-range">
                        </div>
                        
                        <!-- Ubicación -->
                        <div class="col-md-5 search-item" id="item_ubicacion">
                            <label>{{ __('Ubicación del negocio') }}</label>
                            <input type="text" name="ubicacion_texto" id="filtro_ubicacion" placeholder="{{ __('Sector, ciudad, estado...') }}" autocomplete="off">
                            <input type="hidden" name="latitud" id="filtro_lat">
                            <input type="hidden" name="longitud" id="filtro_lng">
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

    <!-- SECCIÓN 2: DESTACADOS -->
    <section class="seccion-destacados">
        <div class="section-intro mb-4 text-center">
            <h2 class="text-24 font-weight-700">{{ __('Negocios Destacados') }}</h2>
        </div>
        <div class="row" id="contenedor_destacados">
            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.lista_cards', ['experiencias' => $destacados])
        </div>
    </section>

    <!-- SECCIÓN 3: LISTADO PRINCIPAL -->
    <section class="seccion-listado">
        <div class="section-intro mb-4 text-center">
            <h2 class="text-24 font-weight-700">{{ __('Explora todos los Negocios') }}</h2>
        </div>

        <div class="row" id="contenedor_listado_general">
            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.lista_cards', ['experiencias' => $experiencias])
        </div>

        <!-- PAGINACIÓN -->
        <div class="row mt-2 mb-5">
            <div class="col-12 d-flex justify-content-center" id="contenedor_paginacion">
                {{ $experiencias->links('vendor.pagination.bootstrap-4') }}
            </div>
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
@stop

@section('validation_script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('vrent.google_map_key') }}&libraries=places"></script>
    <script src="{{ asset('public/js/reda/general/notificaciones.min.js?v=' . time()) }}"></script>
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/listado-experiencias.min.js?v=' . time()) }}"></script>
@endsection
