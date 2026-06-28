@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="listado_calificaciones_duenio"></div>

<div class="margin-top-85">
    <div class="row m-0">
        {{-- Incluimos el sidebar original --}}
        @include('users.sidebar')

        <div class="col-lg-10">
            <div class="main-panel">
                <div class="container-fluid min-height">
                    <div class="row">
                        <div class="col-md-12 p-0 mb-3">
                            <div class="list-bacground mt-4 rounded-3 p-4 border shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                                <div>
                                    <h1 class="text-24 font-weight-700 m-0">{{ __('Calificaciones por Negocio') }}</h1>
                                    <p class="text-muted m-0">{{ __('Resumen de la reputación de cada uno de tus comercios.') }}</p>
                                </div>
                                <div class="mt-3 mt-md-0 container-busqueda-superior">
                                    <div class="input-group cursor-pointer shadow-sm rounded-pill border overflow-hidden bg-white" id="trigger-busqueda-inteligente">
                                        @php
                                            $textoBusqueda = '';
                                            if ($busqueda) {
                                                $textoBusqueda = $busqueda;
                                            } elseif ($categoria) {
                                                $textoBusqueda = $categoria;
                                            }
                                        @endphp
                                        <input type="text" class="form-control border-0 bg-white cursor-pointer" placeholder="{{ __('Búsqueda de comercios...') }}" readonly value="{{ $textoBusqueda }}">
                                        <div class="input-group-append">
                                            <div class="btn btn-success border-0 px-3 d-flex align-items-center cursor-pointer" id="btn-icon-busqueda-inteligente">
                                                <i class="fa fa-search"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 p-0">
                            {{-- Vista Escritorio --}}
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover border rounded-3 overflow-hidden shadow-sm align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="100"></th>
                                            <th>{{ __('Negocio') }}</th>
                                            <th width="250">{{ __('Calificación Promedio') }}</th>
                                            <th width="150" class="text-center">{{ __('Reseñas') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($negocios as $negocio)
                                            @php
                                                $logo = null;
                                                if ($negocio->ruta_imagenes) {
                                                    $logo = asset('public/images/logos_negocios/' . $negocio->ruta_imagenes);
                                                } else {
                                                    $fotoPortada = $negocio->fotos->where('cover_photo', 1)->first();
                                                    if (!$fotoPortada) {
                                                        $fotoPortada = $negocio->fotos->first();
                                                    }

                                                    if ($fotoPortada) {
                                                        $logo = asset('public/images/experiencias/' . $negocio->id . '/' . $fotoPortada->photo);
                                                    }
                                                }
                                                $promedio = round($negocio->calificaciones_avg_estrellas ?? 0, 1);
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <div class="negocio-media-resumen-container mx-auto">
                                                        @if($logo)
                                                            <img src="{{ $logo }}" class="img-negocio-resumen" alt="{{ $negocio->titulo }}">
                                                        @else
                                                            <i class="fas fa-store fa-2x text-muted opacity-05"></i>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-18 font-weight-700">{{ $negocio->titulo }}</div>
                                                    <small class="text-muted">{{ $negocio->categoria_negocio }}</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="star-rating mr-2">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fa fa-star {{ $i <= $promedio ? '' : 'text-light' }} text-14"></i>
                                                            @endfor
                                                        </div>
                                                        <span class="font-weight-700 text-16">{{ number_format($promedio, 1) }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('reda.negocios.experiencias.detalle_calificaciones', $negocio->id) }}" class="text-success font-weight-700 text-16">
                                                        ({{ $negocio->calificaciones_count }}) {{ trans_choice(__('Reseña|Reseñas'), $negocio->calificaciones_count) }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="fas fa-store-slash fa-3x mb-3 opacity-05"></i>
                                                    <p>{{ __('No se encontraron negocios que coincidan con tu búsqueda.') }}</p>
                                                    <a href="{{ route('reda.negocios.experiencias.calificaciones_listado') }}" class="btn btn-success mt-3 px-4 rounded-pill">
                                                        {{ __('Ver todos los negocios') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Vista Móvil --}}
                            <div class="d-md-none">
                                @forelse($negocios as $negocio)
                                    @php
                                        $logo = null;
                                        if ($negocio->ruta_imagenes) {
                                            $logo = asset('public/images/logos_negocios/' . $negocio->ruta_imagenes);
                                        } else {
                                            $fotoPortada = $negocio->fotos->where('cover_photo', 1)->first();
                                            if (!$fotoPortada) {
                                                $fotoPortada = $negocio->fotos->first();
                                            }

                                            if ($fotoPortada) {
                                                $logo = asset('public/images/experiencias/' . $negocio->id . '/' . $fotoPortada->photo);
                                            }
                                        }
                                        $promedio = round($negocio->calificaciones_avg_estrellas ?? 0, 1);
                                    @endphp
                                    <div class="card card-negocio mb-3 border rounded-4 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <div class="negocio-media-mobile-container">
                                                        @if($logo)
                                                            <img src="{{ $logo }}" class="img-negocio-mobile" alt="{{ $negocio->titulo }}">
                                                        @else
                                                            <i class="fas fa-store fa-lg text-muted opacity-05"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h3 class="text-16 font-weight-700 m-0 text-truncate">{{ $negocio->titulo }}</h3>
                                                    <div class="d-flex align-items-center mt-1">
                                                        <div class="star-rating mr-2">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fa fa-star {{ $i <= $promedio ? '' : 'text-light' }} text-12"></i>
                                                            @endfor
                                                        </div>
                                                        <span class="font-weight-700 text-14">{{ number_format($promedio, 1) }}</span>
                                                    </div>
                                                    <div class="mt-1">
                                                        <a href="{{ route('reda.negocios.experiencias.detalle_calificaciones', $negocio->id) }}" class="text-success font-weight-700 text-14">
                                                            ({{ $negocio->calificaciones_count }}) {{ trans_choice(__('Reseña|Reseñas'), $negocio->calificaciones_count) }}
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="ml-2">
                                                    <a href="{{ route('reda.negocios.experiencias.detalle_calificaciones', $negocio->id) }}" class="btn btn-sm btn-light rounded-circle">
                                                        <i class="fa fa-chevron-right text-muted"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted bg-white border rounded-4 shadow-sm">
                                        <i class="fas fa-store-slash fa-3x mb-3 opacity-05"></i>
                                        <p>{{ __('No hay negocios disponibles.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    <div class="row justify-content-between pb-3 mt-4 mb-5">
                        {{ $negocios->appends(request()->except('page'))->links('reda-alojamiento::general.paginacion') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Búsqueda Inteligente de Comercios --}}
<div class="modal fade" id="modalBusquedaInteligente" tabindex="-1" role="dialog" aria-labelledby="modalBusquedaInteligenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white rounded-0">
                <h5 class="modal-title font-weight-700" id="modalBusquedaInteligenteLabel">
                    <i class="fas fa-search-plus mr-2"></i> {{ __('Búsqueda de Comercios') }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('reda.negocios.experiencias.calificaciones_listado') }}" method="GET" id="form-busqueda-comercios">
                <div class="modal-body p-4">
                    {{-- Búsqueda por Nombre --}}
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label class="form-label font-weight-700 text-14">{{ __('Nombre del Comercio') }}</label>
                            <div class="input-group">
                                <input type="text" name="search" id="input-busqueda-comercios" class="form-control rounded-pill-left" placeholder="{{ __('Nombre del negocio...') }}" value="{{ $busqueda ?? '' }}" list="lista-nombres-comercios" autocomplete="off">
                                <datalist id="lista-nombres-comercios"></datalist>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success px-4 rounded-pill-right">
                                        <i class="fas fa-search mr-2"></i> {{ __('Buscar') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Filtros por Categoría --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label font-weight-700 text-14">{{ __('Filtrar por Categoría') }}</label>
                            <div class="d-flex flex-wrap">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 mr-2 mb-2 btn-filtro-categoria {{ !$categoria ? 'active' : '' }}" data-category="">
                                    {{ __('Todas') }}
                                </button>
                                @foreach($categorias as $cat)
                                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 mr-2 mb-2 btn-filtro-categoria {{ $categoria == $cat ? 'active' : '' }}" data-category="{{ $cat }}">
                                        {{ $cat }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="category" id="hidden_category" value="{{ $categoria }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <a href="{{ route('reda.negocios.experiencias.calificaciones_listado') }}" class="btn btn-outline-secondary px-4 rounded-pill font-weight-700">
                        {{ __('Limpiar todo') }}
                    </a>
                    <button type="submit" class="btn btn-success px-5 rounded-pill font-weight-700 shadow-sm">
                        {{ __('Aplicar Búsqueda') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('scripts')
    <script src="{{ asset('public/js/reda/vistas/experiencia/listadoCalificaciones.min.js?v=' . time()) }}"></script>
@endpush
