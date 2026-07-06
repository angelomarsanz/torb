@extends('template')

@section('main')
<div id="detalle_calificaciones_duenio"></div>

<div class="margin-top-85">
    <div class="row m-0">
        {{-- Incluimos el sidebar original --}}
        @include('users.sidebar')

        <div class="col-lg-10">
            <div class="main-panel">
                <div class="container-fluid min-height">
                    <div class="row">
                        <div class="col-md-12 p-0 mb-3">
                            <div class="mt-4 d-flex align-items-center justify-content-between flex-wrap bg-white p-4 rounded-4 shadow-sm border">
                                <div class="d-flex align-items-center mb-2">
                                    <a href="{{ route('reda.negocios.experiencias.calificaciones_listado') }}" class="btn btn-outline-secondary rounded-circle mr-3">
                                        <i class="fa fa-arrow-left"></i>
                                    </a>
                                    <div>
                                        <h1 class="text-24 font-weight-700 m-0">{{ __('Reseñas de :negocio', ['negocio' => $experiencia->titulo]) }}</h1>
                                        <div class="d-flex align-items-center mt-1">
                                            <div class="star-rating mr-2">
                                                @php $promedio = round($experiencia->calificaciones_avg_estrellas ?? 0, 1); @endphp
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa fa-star {{ $i <= $promedio ? '' : 'text-light' }} text-14"></i>
                                                @endfor
                                            </div>
                                            <span class="font-weight-700 text-16">{{ number_format($promedio, 1) }} ({{ $experiencia->calificaciones_count }} {{ trans_choice(__('Reseña|Reseñas'), $experiencia->calificaciones_count) }})</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2 container-busqueda-superior">
                                    <div class="input-group cursor-pointer shadow-sm rounded-pill border overflow-hidden" id="trigger-busqueda-inteligente">
                                        <input type="text" class="form-control border-0 bg-white cursor-pointer" placeholder="{{ __('Búsqueda de reseñas...') }}" readonly value="">
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
                            {{-- Contenedor de Reseñas --}}
                            <div class="list-container">
                                @forelse($calificaciones as $calificacion)
                                    <div class="card mb-4 border rounded-4 shadow-sm overflow-hidden">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $logo = null;
                                                        if ($experiencia->ruta_imagenes) {
                                                            $logo = asset('public/images/logos_negocios/' . $experiencia->ruta_imagenes);
                                                        } else {
                                                            $fotoPortada = $experiencia->fotos->where('cover_photo', 1)->first();
                                                            if (!$fotoPortada) {
                                                                $fotoPortada = $experiencia->fotos->first();
                                                            }

                                                            if ($fotoPortada) {
                                                                $logo = asset('public/images/experiencias/' . $experiencia->id . '/' . $fotoPortada->photo);
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="negocio-media-detalle-container mr-3">
                                                        @if($logo)
                                                            <img src="{{ $logo }}" class="img-negocio-detalle" alt="{{ $experiencia->titulo }}">
                                                        @else
                                                            <i class="fas fa-store text-muted opacity-05"></i>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <h3 class="text-18 font-weight-700 m-0">{{ $experiencia->titulo }}</h3>
                                                        <div class="star-rating mt-1">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fa fa-star {{ $i <= $calificacion->estrellas ? '' : 'text-light' }} text-14"></i>
                                                            @endfor
                                                            <span class="ml-1 font-weight-700 text-14">{{ number_format($calificacion->estrellas, 1) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="badge badge-dark rounded-pill px-3 py-2 mb-2 shadow-sm text-14 font-weight-700">
                                                        ID #{{ $calificacion->id }}
                                                    </div>
                                                    <div class="text-muted small font-weight-600">
                                                        {{ $calificacion->created_at->format('d/m/Y') }}
                                                    </div>
                                                    <div class="text-muted text-12">
                                                        {{ $calificacion->created_at->format('H:i') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="comment-box-review p-4 mb-3">
                                                <p class="m-0 text-16 italic text-dark font-weight-500">
                                                    "{{ $calificacion->comentario ?: __('Sin comentario') }}"
                                                </p>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 btn-reportar-reseña"
                                                        data-id="{{ $calificacion->id }}"
                                                        data-id-experiencia="{{ $experiencia->id }}"
                                                        data-negocio="{{ $experiencia->titulo }}"
                                                        data-usuario="{{ $calificacion->usuario->first_name }} {{ $calificacion->usuario->last_name }}"
                                                        data-calificacion="{{ $calificacion->estrellas }}"
                                                        data-comentario="{{ $calificacion->comentario }}">
                                                        <i class="fas fa-flag mr-1"></i> {{ __('Reportar') }}
                                                    </button>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="text-right mr-3">
                                                        <div class="text-14 font-weight-700">{{ $calificacion->usuario->first_name }} {{ $calificacion->usuario->last_name }}</div>
                                                        <small class="text-muted">{{ __('Cliente') }}</small>
                                                    </div>
                                                    @php
                                                        $fotoUsuario = $calificacion->usuario->profile_image;
                                                        $rutaFotoUsuario = $fotoUsuario
                                                            ? asset('public/images/profile/' . $calificacion->usuario->id . '/' . $fotoUsuario)
                                                            : asset('public/images/default-profile.png');
                                                    @endphp
                                                    <img src="{{ $rutaFotoUsuario }}" class="img-profile-list shadow-sm">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted border rounded-4 bg-white shadow-sm">
                                        <i class="fas fa-star-half-alt fa-4x mb-3 opacity-05 text-success"></i>
                                        <h3 class="text-20 font-weight-700">{{ __('Sin reseñas todavía') }}</h3>
                                        <p>{{ __('No se encontraron reseñas que coincidan con tu búsqueda.') }}</p>
                                        <a href="{{ route('reda.negocios.experiencias.detalle_calificaciones', $experiencia->id) }}" class="btn btn-success mt-3 pl-4 pr-4">
                                            {{ __('Ver todas las reseñas') }}
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    <div class="row justify-content-between pb-3 mt-4 mb-5">
                        {{ $calificaciones->appends(request()->except('page'))->links('reda-alojamiento::general.paginacion') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Búsqueda Inteligente --}}
<div class="modal fade" id="modalBusquedaInteligente" tabindex="-1" role="dialog" aria-labelledby="modalBusquedaInteligenteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white rounded-0">
                <h5 class="modal-title font-weight-700" id="modalBusquedaInteligenteLabel">
                    <i class="fas fa-search-plus mr-2"></i> {{ __('Búsqueda de Reseñas') }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('reda.negocios.experiencias.detalle_calificaciones', $experiencia->id) }}" method="GET" id="form-busqueda-inteligente">
                <div class="modal-body p-4">
                    {{-- Búsqueda por ID o Nombre --}}
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="input_review_id" class="form-label font-weight-700 text-14">{{ __('Buscar por ID') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0 rounded-pill-left"><i class="fas fa-hashtag text-muted"></i></span>
                                </div>
                                <input type="text" name="review_id" id="input_review_id" class="form-control border-left-0 rounded-pill-right" placeholder="{{ __('Ej: 123') }}" value="{{ $reviewId }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="input_customer_name" class="form-label font-weight-700 text-14">{{ __('Buscar por Cliente') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0 rounded-pill-left"><i class="fas fa-user text-muted"></i></span>
                                </div>
                                <input type="text" name="customer_name" id="input_customer_name" class="form-control border-left-0 rounded-pill-right" placeholder="{{ __('Nombre del cliente...') }}" list="listaClientes" autocomplete="off" value="{{ $customerName }}">
                                <datalist id="listaClientes">
                                    @foreach($nombresClientes as $nombre)
                                        <option value="{{ $nombre }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>

                    {{-- Campos ocultos para enviar al controlador (Filtros de estado) --}}
                    <input type="hidden" name="rating_filter" id="hidden_rating_filter" value="{{ $ratingFilter }}">
                    <input type="hidden" name="is_reported" id="hidden_is_reported" value="{{ $isReported }}">

                    <hr>

                    {{-- Filtros Rápidos --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label font-weight-700 text-14">{{ __('Filtros rápidos') }}</label>
                            <div class="d-flex flex-wrap">
                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 mr-2 mb-2 btn-filtro-rapido {{ $ratingFilter == 'best' ? 'active' : '' }}" data-filter="best">
                                    <i class="fas fa-star mr-1"></i> {{ __('Mejores calificaciones') }}
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 mr-2 mb-2 btn-filtro-rapido {{ $ratingFilter == 'worst' ? 'active' : '' }}" data-filter="worst">
                                    <i class="fas fa-star-half-alt mr-1"></i> {{ __('Peores calificaciones') }}
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 mr-2 mb-2 btn-filtro-rapido {{ $ratingFilter == 'recent' || (!$ratingFilter && !$reviewId && !$customerName && !$isReported && !$dateFrom) ? 'active' : '' }}" data-filter="recent">
                                    <i class="fas fa-clock mr-1"></i> {{ __('Más recientes') }}
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 mr-2 mb-2 btn-filtro-rapido {{ $isReported ? 'active' : '' }}" data-filter="reported">
                                    <i class="fas fa-flag mr-1"></i> {{ __('Reseñas reportadas') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Búsqueda por Fecha --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_from" class="form-label font-weight-700 text-14">{{ __('Desde') }}</label>
                            <input type="date" name="date_from" id="date_from" class="form-control rounded-3" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date_to" class="form-label font-weight-700 text-14">{{ __('Hasta') }}</label>
                            <input type="date" name="date_to" id="date_to" class="form-control rounded-3" value="{{ $dateTo }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <a href="{{ route('reda.negocios.experiencias.detalle_calificaciones', $experiencia->id) }}" class="btn btn-outline-secondary px-4 rounded-pill font-weight-700">
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

{{-- Modal para Reportar Reseña --}}
<div class="modal fade" id="modalReportarReseña" tabindex="-1" role="dialog" aria-labelledby="modalReportarReseñaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white rounded-0">
                <h5 class="modal-title font-weight-700" id="modalReportarReseñaLabel">
                    <i class="fas fa-flag mr-2"></i> {{ __('Reportar Reseña') }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formReportarReseña">
                @csrf
                <input type="hidden" name="calificacion_id" id="reporte_calificacion_id">
                <input type="hidden" name="tema" id="reporte_tema">
                <input type="hidden" name="link_error" id="reporte_link_error">
                <input type="hidden" name="vista_origen" value="Reportar calificación">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="prioridad" class="form-label font-weight-700 text-14">{{ __('Prioridad') }}</label>
                        <select name="prioridad" id="prioridad" class="form-control rounded-3" required>
                            <option value="Baja">{{ __('Baja') }}</option>
                            <option value="Media" selected>{{ __('Media') }}</option>
                            <option value="Alta">{{ __('Alta') }}</option>
                            <option value="Urgente">{{ __('Urgente') }}</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="mensaje" class="form-label font-weight-700 text-14">{{ __('Mensaje') }}</label>
                        <textarea name="mensaje" id="mensaje" class="form-control rounded-3" rows="4" placeholder="{{ __('Escribe los detalles de tu reporte aquí...') }}"></textarea>
                        <small id="mensaje_error" class="text-muted text-12">{{ __('Mínimo 10 caracteres') }}</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill font-weight-700" data-dismiss="modal">{{ __('Cancelar') }}</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-pill font-weight-700 shadow-sm" id="btnEnviarReporte">
                        {{ __('Enviar Reporte') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('scripts')
    <script type="text/javascript" src="{{ asset('public/js/reda/general/notificaciones.min.js?v=' . time()) }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/detalleCalificaciones.min.js?v=' . time()) }}"></script>
@endpush
