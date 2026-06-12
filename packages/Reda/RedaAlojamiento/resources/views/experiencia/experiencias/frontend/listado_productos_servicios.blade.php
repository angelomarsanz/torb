@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="listado_productos_servicios" class="container-fluid p-0">
    <div class="container p-0">

    <!-- SECCIÓN 2: BARRA DE BÚSQUEDA -->
    <section class="seccion-busqueda-actividades px-4 mb-4">
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
                <button class="btn btn-primary rounded-circle p-2 btn-search-round">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Vista Móvil -->
        <div class="d-lg-none">
            <div class="search-bar-actividades d-flex align-items-center justify-content-center" data-toggle="modal" data-target="#modalBusquedaActividades">
                <span class="text-muted"><i class="fas fa-search mr-2"></i> {{ __('¿Qué estás buscando?') }}</span>
            </div>
        </div>

    </section>

    <!-- SECCIÓN 1: INFORMACIÓN DEL NEGOCIO -->
    <section class="seccion-info-negocio p-4">
        <h1 class="negocio-detalle-titulo font-weight-700">{{ $experiencia->titulo }}</h1>
        <p class="negocio-detalle-desc text-muted">{{ $experiencia->descripcion }}</p>
        <div class="negocio-detalle-rating star-rating">
            <i class="fas fa-star"></i>
            @if($experiencia->calificaciones_count > 0)
                <span class="font-weight-700 text-dark">{{ number_format($experiencia->calificaciones_avg_estrellas, 1) }}</span>
                <span class="text-muted ml-1">· {{ $experiencia->calificaciones_count }} {{ trans_choice('reseña|reseñas', $experiencia->calificaciones_count) }}</span>
            @else
                <span class="text-muted small">{{ __('Sin reseñas todavía') }}</span>
            @endif
        </div>
    </section>

    <!-- Modal de Búsqueda Móvil -->
    <div class="modal fade" id="modalBusquedaActividades" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-negocio-rounded">
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
                    <button type="button" class="btn btn-primary btn-block btn-lg btn-aplicar-filtro">{{ __('Aplicar') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: PRODUCTOS Y SERVICIOS EN PROMOCIÓN -->
    @if($promociones->count() > 0)
    <section class="seccion-productos px-4 mb-4">
        <h2 class="text-18 font-weight-700 mb-3">{{ __('Promociones Especiales') }}</h2>
        <div class="container-carrusel-productos">
            @foreach($promociones as $promo)
                @php
                    $complementos = json_decode($promo->precios_monedas_complementarios, true);
                    $precioPromo = $complementos['precio_promocion'] ?? 0;
                    $currencySymbol = $promo->currency->symbol ?? $currentCurrency->symbol;
                @endphp
                <div class="producto-card" data-tipo-actividad="{{ $promo->tipo_producto_servicio }}" data-id="{{ $promo->id }}">
                    <div class="producto-img-container">
                        <span class="badge-promo">{{ __('Oferta') }}</span>
                        @php
                            $rutaFotoAct = asset('public/images/default-image.png');
                            if ($promo->foto_actividad) {
                                $rutaFotoAct = asset('public/images/actividades_experiencias/' . $promo->foto_actividad);
                            }
                        @endphp
                        <img src="{{ $rutaFotoAct }}" alt="{{ $promo->nombre_actividad }}">
                    </div>
                    <div class="producto-info">
                        <h4 class="producto-nombre">{{ $promo->nombre_actividad }}</h4>
                        <div class="producto-precio">
                            <span class="precio-original">{{ $currencySymbol }} {{ number_format($promo->precio, 2) }}</span>
                            <span class="precio-promo">{{ $currencySymbol }} {{ number_format($precioPromo, 2) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- SECCIÓN 4: EXPLORAR TODOS -->
    <section class="seccion-productos px-4 mb-4">
        <h2 class="text-18 font-weight-700 mb-3">{{ __('Explorar Todo') }}</h2>
        <div class="container-carrusel-productos" id="contenedor_todos_productos">
            @foreach($actividades as $actividad)
                @php
                    $currencySymbol = $actividad->currency->symbol ?? $currentCurrency->symbol;
                    $complementos = json_decode($actividad->precios_monedas_complementarios, true);
                    $precioPromo = $complementos['precio_promocion'] ?? 0;
                @endphp
                <div class="producto-card" data-tipo-actividad="{{ $actividad->tipo_producto_servicio }}" data-id="{{ $actividad->id }}">
                    <div class="producto-img-container">
                        @php
                            $rutaFotoAct = asset('public/images/default-image.png');
                            if ($actividad->foto_actividad) {
                                $rutaFotoAct = asset('public/images/actividades_experiencias/' . $actividad->foto_actividad);
                            }
                        @endphp
                        <img src="{{ $rutaFotoAct }}" alt="{{ $actividad->nombre_actividad }}">
                    </div>
                    <div class="producto-info">
                        <h4 class="producto-nombre">{{ $actividad->nombre_actividad }}</h4>
                        <div class="producto-precio">
                            @if($precioPromo > 0)
                                <span class="precio-original">{{ $currencySymbol }} {{ number_format($actividad->precio, 2) }}</span>
                                <span class="precio-promo">{{ $currencySymbol }} {{ number_format($precioPromo, 2) }}</span>
                            @else
                                <span class="font-weight-700">{{ $currencySymbol }} {{ number_format($actividad->precio, 2) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <hr class="mx-4">

    <!-- SECCIÓN 5: UBICACIÓN -->
    <section class="seccion-ubicacion-negocio px-4 mb-5">
        <h2 class="text-22 font-weight-700 mb-3">{{ __('¿Dónde estarás?') }}</h2>
        <div class="mb-3">
            <p class="text-16 mb-1 font-weight-600">{{ $experiencia->ubicacion['linea_uno_direccion'] ?? '' }}</p>
            @if(!empty($experiencia->ubicacion['linea_dos_direccion']))
                <p class="text-16 mb-1">{{ $experiencia->ubicacion['linea_dos_direccion'] }}</p>
            @endif
            <p class="text-16 mb-0">{{ $experiencia->ubicacion['ciudad'] ?? '' }}, {{ $experiencia->ubicacion['estado'] ?? '' }}</p>
        </div>
        <div id="mapa_detalle_negocio"></div>
    </section>

    <hr class="mx-4">

    <!-- SECCIÓN 6: HORARIOS -->
    @if(!empty($experiencia->horarios))
    <section class="seccion-horarios-negocio px-4 mb-5">
        <h2 class="text-22 font-weight-700 mb-3">{{ __('Horarios') }}</h2>
        <div class="row">
            @foreach($experiencia->horarios as $horario)
                <div class="col-md-6 mb-3">
                    <div class="card border-0 shadow-sm p-3 rounded-12">
                        <p class="font-weight-700 mb-2">
                            @foreach($horario['dias'] as $dia)
                                {{ __(ucfirst($dia)) }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </p>
                        @foreach($horario['bloques'] as $bloque)
                            <p class="mb-1 text-muted">
                                {{ $bloque['hora_desde'] }} {{ $bloque['ampm_desde'] }} - {{ $bloque['hora_hasta'] }} {{ $bloque['ampm_hasta'] }}
                            </p>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    <hr class="mx-4">
    @endif

    <!-- SECCIÓN 7: SOBRE NOSOTROS -->
    @if($experiencia->anfitrion)
    <section class="seccion-nosotros-negocio px-4 mb-5">
        <h2 class="text-22 font-weight-700 mb-4">{{ __('Sobre Nosotros') }}</h2>

        <div class="clearfix">
            @php
                $fotoAnfitrion = asset('public/images/default-image.png');
                if (!empty($experiencia->anfitrion->foto_anfitrion)) {
                    $fotoAnfitrion = asset('public/images/anfitriones_experiencias/' . $experiencia->anfitrion->foto_anfitrion);
                }
            @endphp

            {{-- Imagen: Flotada a la izquierda en escritorio --}}
            <div class="float-md-left mr-md-4 mb-3 text-center text-md-left">
                <img src="{{ $fotoAnfitrion }}" alt="{{ __('Sobre Nosotros') }}"
                     class="shadow-sm"
                     id="img_nosotros_detalle">
            </div>

            {{-- Texto descriptivo: Fluye alrededor de la imagen --}}
            <div class="nosotros-texto text-16 text-justify">
                {!! nl2br(e($experiencia->anfitrion->trayectoria_profesional)) !!}
            </div>
        </div>
    </section>
    <hr class="mx-4">
    @endif

    <!-- SECCIÓN 8: GALERÍA (ESTILO INSTAGRAM) -->
    @php
        $fotos = $experiencia->fotos;
        $fotosOrdenadas = $fotos->sortByDesc('cover_photo');
    @endphp
    @if($fotosOrdenadas->count() > 0)
    <section class="seccion-galeria-instagram mb-5">
        <h2 class="text-22 font-weight-700 px-4 mb-4">{{ __('Galería') }}</h2>
        <div id="instagramGallery" class="carousel slide" data-ride="carousel" data-interval="false">
            <div class="carousel-inner">
                @foreach($fotosOrdenadas as $index => $foto)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <div class="instagram-img-wrapper d-flex align-items-center justify-content-center bg-light">
                            <img src="{{ asset('public/images/experiencias/' . $experiencia->id . '/' . $foto->photo) }}"
                                 class="d-block instagram-img"
                                 alt="{{ $experiencia->titulo }}">
                        </div>
                    </div>
                @endforeach
            </div>
            @if($fotosOrdenadas->count() > 1)
                <a class="carousel-control-prev" href="#instagramGallery" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#instagramGallery" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
                <!-- Contador de fotos -->
                <div class="instagram-counter px-3 py-1 bg-dark text-white rounded-pill shadow-sm">
                    <span id="current-photo">1</span> / {{ $fotosOrdenadas->count() }}
                </div>
            @endif
        </div>
    </section>
    <hr class="mx-4">
    @endif

    <!-- SECCIÓN 9: INFORMACIÓN ADICIONAL -->
    @if($experiencia->informaciones->first())
    <section class="seccion-informacion-adicional px-4 mb-5">
        <h2 class="text-22 font-weight-700 mb-3">{{ __('Cosas que debes saber') }}</h2>
        <div class="text-16">
            <h4 class="font-weight-700 text-18 mb-2">{{ __('Requisitos del cliente') }}</h4>
            <p>{!! nl2br(e($experiencia->informaciones->first()->requisitos_viajero)) !!}</p>
        </div>
    </section>
    @endif

    <hr class="mx-4">

    <!-- SECCIÓN 10: RESEÑAS -->
    <section class="seccion-reseñas-negocio px-4 mb-5">
        <div class="d-flex align-items-center mb-4">
            <h2 class="text-22 font-weight-700 m-0">
                <i class="fas fa-star text-warning"></i>
                @if($experiencia->calificaciones_count > 0)
                    {{ number_format($experiencia->calificaciones_avg_estrellas, 1) }} · {{ $experiencia->calificaciones_count }} {{ trans_choice('reseña|reseñas', $experiencia->calificaciones_count) }}
                @else
                    {{ __('Reseñas') }}
                @endif
            </h2>
        </div>

        <div class="row">
            @forelse($experiencia->calificaciones as $calificacion)
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-none bg-transparent">
                        <div class="card-body p-0">
                            <div class="d-flex align-items-center mb-2">
                                @php
                                    $fotoUsuario = $calificacion->usuario->profile_image;
                                    $rutaFotoUsuario = $fotoUsuario 
                                        ? asset('public/images/profile/' . $calificacion->usuario->id . '/' . $fotoUsuario) 
                                        : asset('public/images/default-profile.png');
                                    
                                    // Seleccionar primer nombre y primer apellido de manera inteligente
                                    $primerNombre = explode(' ', trim($calificacion->usuario->first_name))[0];
                                    $primerApellido = explode(' ', trim($calificacion->usuario->last_name))[0];
                                @endphp
                                <img src="{{ $rutaFotoUsuario }}" class="img-profile-list mr-3 img-size-48">
                                <div>
                                    <div class="font-weight-700 text-16">{{ $primerNombre }} {{ $primerApellido }}</div>
                                    <div class="text-muted small">{{ $calificacion->created_at->format('M Y') }}</div>
                                </div>
                            </div>
                            <div class="star-rating mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star {{ $i <= $calificacion->estrellas ? '' : 'text-light' }} star-rating-12"></i>
                                @endfor
                            </div>
                            <p class="text-16 text-justify mb-0">
                                {{ $calificacion->comentario }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="bg-light p-5 rounded-12 text-center text-muted">
                        <i class="far fa-comment-dots fa-3x mb-3"></i>
                        <p class="m-0 text-16">{{ __('Aún no hay reseñas de este comercio. ¡Sé el primero en calificarlo!') }}</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    </div>
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
@stop

@section('validation_script')
    <script>
        window.datosUbicacionNegocio = {
            lat: {{ $experiencia->ubicacion['latitud'] ?? 0 }},
            lng: {{ $experiencia->ubicacion['longitud'] ?? 0 }},
            titulo: "{{ $experiencia->titulo }}"
        };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('vrent.google_map_key') }}&libraries=places"></script>
    <script src="{{ asset('public/js/reda/general/notificaciones.min.js?v=' . time()) }}"></script>
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/listadoProductosServicios.min.js?v=' . time()) }}"></script>
@endsection
