@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="listado_productos_servicios" class="container-fluid">
    <div class="row m-0">
            <!-- COLUMNA IZQUIERDA: 30% FIJA (Escritorio) -->
            <div class="col-12 col-lg-30 d-none d-lg-block">
                <div class="sticky-top-detail pr-lg-4">
                    <!-- SECCIÓN 1: INFORMACIÓN DEL NEGOCIO -->
                    <section class="seccion-info-negocio">
                        @if($experiencia->ruta_imagenes)
                            <div class="negocio-detalle-logo-wrapper mb-4">
                                <img src="{{ asset('public/images/logos_negocios/' . $experiencia->ruta_imagenes) }}"
                                     alt="Logo {{ $experiencia->titulo }}"
                                     class="img-fluid">
                            </div>
                        @endif

                        <h1 class="negocio-detalle-titulo font-weight-700 px-2">{{ $experiencia->titulo }}</h1>

                        <div class="negocio-detalle-desc-wrapper px-2">
                            <p class="negocio-detalle-desc text-muted" id="desc_negocio_fija">{{ $experiencia->descripcion }}</p>
                            <button class="btn-leer-mas-desc" id="btn_leer_mas_fija">{{ __('Más') }}</button>
                        </div>

                        <!-- SECCIÓN DE CONTACTO -->
                        @php
                            $emailNegocio = data_get($experiencia->ubicacion, 'email_negocio');
                            $whatsappNegocio = data_get($experiencia->ubicacion, 'whatsapp_negocio');
                        @endphp
                        <div class="negocio-detalle-contacto" id="contacto_negocio_desktop">
                            <p class="titulo-contacto">{{ __('Contacto:') }}</p>
                            @if(!empty($emailNegocio) || !empty($whatsappNegocio))
                                @if(!empty($emailNegocio))
                                    <div class="contacto-item">
                                        <div class="contacto-header">
                                            <i class="fas fa-envelope"></i>
                                            <span class="label-contacto">{{ __('Correo:') }}</span>
                                        </div>
                                        <div class="contacto-valor">
                                            <a href="mailto:{{ $emailNegocio }}">{{ $emailNegocio }}</a>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($whatsappNegocio))
                                    <div class="contacto-item">
                                        <div class="contacto-header">
                                            <i class="fab fa-whatsapp"></i>
                                            <span class="label-contacto">{{ __('WhatsApp:') }}</span>
                                        </div>
                                        <div class="contacto-valor">
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNegocio) }}" target="_blank">{{ $whatsappNegocio }}</a>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <p class="text-muted small italic">{{ __('No se han cargado el correo y el teléfono Whatsapp') }}</p>
                            @endif
                        </div>

                        <div class="negocio-detalle-rating star-rating mt-3">
                            @if($experiencia->calificaciones_count > 0)
                                @php $puntuacion = (float) $experiencia->calificaciones_avg_estrellas; @endphp
                                <div class="mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($puntuacion >= $i)
                                            <i class="fas fa-star text-warning"></i>
                                        @elseif($puntuacion > ($i - 1) && $puntuacion < $i)
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        @else
                                            <i class="far fa-star text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="font-weight-700 text-dark">{{ number_format($puntuacion, 1, '.', '') }}</span>
                                <span class="text-muted ml-1">({{ $experiencia->calificaciones_count }} {{ trans_choice('Reseña|Reseñas', $experiencia->calificaciones_count) }})</span>
                            @else
                                <i class="fas fa-star text-muted"></i>
                                <span class="text-muted small">{{ __('Sin reseñas todavía') }}</span>
                            @endif
                        </div>
                    </section>
                </div>
            </div>

            <!-- COLUMNA DERECHA: 70% DESPLAZABLE (Escritorio) / 100% (Móvil) -->
            <div class="col-12 col-lg-70">

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

                <!-- SECCIÓN 1: INFORMACIÓN DEL NEGOCIO (Solo Móvil) -->
                <div class="d-lg-none">
                    <section class="seccion-info-negocio p-4">
                        @if($experiencia->ruta_imagenes)
                            <div class="negocio-detalle-logo-wrapper mb-4">
                                <img src="{{ asset('public/images/logos_negocios/' . $experiencia->ruta_imagenes) }}"
                                     alt="Logo {{ $experiencia->titulo }}"
                                     class="img-fluid">
                            </div>
                        @endif

                        <h1 class="negocio-detalle-titulo font-weight-700">{{ $experiencia->titulo }}</h1>
                        <div class="negocio-detalle-desc-wrapper">
                            <p class="negocio-detalle-desc text-muted">{{ $experiencia->descripcion }}</p>
                            <button class="btn-leer-mas-desc">{{ __('Más') }}</button>
                        </div>

                        <!-- SECCIÓN DE CONTACTO (Solo Móvil) -->
                        <div class="negocio-detalle-contacto" id="contacto_negocio_mobile">
                            <p class="titulo-contacto">{{ __('Contacto:') }}</p>
                            @if(!empty($emailNegocio) || !empty($whatsappNegocio))
                                @if(!empty($emailNegocio))
                                    <div class="contacto-item">
                                        <div class="contacto-header">
                                            <i class="fas fa-envelope"></i>
                                            <span class="label-contacto">{{ __('Correo:') }}</span>
                                        </div>
                                        <div class="contacto-valor">
                                            <a href="mailto:{{ $emailNegocio }}">{{ $emailNegocio }}</a>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($whatsappNegocio))
                                    <div class="contacto-item">
                                        <div class="contacto-header">
                                            <i class="fab fa-whatsapp"></i>
                                            <span class="label-contacto">{{ __('WhatsApp:') }}</span>
                                        </div>
                                        <div class="contacto-valor">
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNegocio) }}" target="_blank">{{ $whatsappNegocio }}</a>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <p class="text-muted small italic">{{ __('No se han cargado el correo y el teléfono Whatsapp') }}</p>
                            @endif
                        </div>

                        <div class="negocio-detalle-rating star-rating mt-3">
                            @if($experiencia->calificaciones_count > 0)
                                @php $puntuacion = (float) $experiencia->calificaciones_avg_estrellas; @endphp
                                <div class="mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($puntuacion >= $i)
                                            <i class="fas fa-star text-warning"></i>
                                        @elseif($puntuacion > ($i - 1) && $puntuacion < $i)
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        @else
                                            <i class="far fa-star text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="font-weight-700 text-dark">{{ number_format($puntuacion, 1, '.', '') }}</span>
                                <span class="text-muted ml-1">({{ $experiencia->calificaciones_count }} {{ trans_choice('Reseña|Reseñas', $experiencia->calificaciones_count) }})</span>
                            @else
                                <i class="fas fa-star text-muted"></i>
                                <span class="text-muted small">{{ __('Sin reseñas todavía') }}</span>
                            @endif
                        </div>
                    </section>
                </div>

                <!-- SECCIÓN 3: PRODUCTOS Y SERVICIOS EN PROMOCIÓN -->
                @if($promociones->count() > 0)
                <section class="seccion-productos mb-4" id="seccion_promociones">
                    <div class="header-seccion-carrusel">
                        <h2 class="text-18 font-weight-700">{{ __('Promociones Especiales') }}</h2>
                        <div class="carrusel-controles-desktop d-none d-lg-flex">
                            <button class="btn-carrusel-control btn-prev" data-target="#carrusel_promociones" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn-carrusel-control btn-next" data-target="#carrusel_promociones">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="container-carrusel-productos"
                         id="carrusel_promociones"
                         data-tipo="promociones"
                         data-id-negocio="{{ $experiencia->id }}">
                        @foreach($promociones as $promo)
                            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_producto_servicio', ['actividad' => $promo, 'es_promo' => true])
                        @endforeach
                        
                        @if($totalPromociones > 10)
                            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_ver_todos', [
                                'items' => $promociones, 
                                'tipo' => 'promociones', 
                                'idNegocio' => $experiencia->id, 
                                'tituloModal' => __('Promociones Especiales'),
                                'total' => $totalPromociones
                            ])
                        @endif
                    </div>
                </section>
                @endif

                <!-- SECCIÓN 4: EXPLORAR TODOS -->
                <section class="seccion-productos mb-4" id="seccion_todos">
                    <div class="header-seccion-carrusel">
                        <h2 class="text-18 font-weight-700">{{ __('Explorar Todo') }}</h2>
                        <div class="carrusel-controles-desktop d-none d-lg-flex">
                            <button class="btn-carrusel-control btn-prev" data-target="#contenedor_todos_productos" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn-carrusel-control btn-next" data-target="#contenedor_todos_productos">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="container-carrusel-productos"
                         id="contenedor_todos_productos"
                         data-tipo="todas"
                         data-id-negocio="{{ $experiencia->id }}">
                        @foreach($actividades as $actividad)
                            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_producto_servicio', ['actividad' => $actividad])
                        @endforeach

                        @if($totalActividades > 10)
                            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_ver_todos', [
                                'items' => $actividades, 
                                'tipo' => 'todas', 
                                'idNegocio' => $experiencia->id, 
                                'tituloModal' => __('Listado Completo'),
                                'total' => $totalActividades
                            ])
                        @endif
                    </div>
                </section>

                <hr class="mx-4">

                <!-- SECCIÓN 5: UBICACIÓN -->
                <section class="seccion-ubicacion-negocio px-4 mb-5">
                    <h2 class="text-22 font-weight-700 mb-3">{{ __('¿Dónde estamos ubicados?') }}</h2>
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

                        <div class="float-md-left mr-md-4 mb-3 text-center text-md-left">
                            <img src="{{ $fotoAnfitrion }}" alt="{{ __('Sobre Nosotros') }}"
                                class="shadow-sm"
                                id="img_nosotros_detalle">
                        </div>

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
                    <h2 class="text-22 font-weight-700 mb-3">{{ __('Información Adicional') }}</h2>
                    <div class="text-16">
                        <p>{!! nl2br(e($experiencia->informaciones->first()->requisitos_viajero)) !!}</p>
                    </div>
                </section>
                @endif

                <hr class="mx-4">

                <!-- SECCIÓN 10: RESEÑAS (CARRUSEL) -->
                <section class="seccion-productos mb-5" id="seccion_reseñas">
                    <div class="header-seccion-carrusel">
                        <div class="d-flex align-items-center">
                            <h2 class="text-22 font-weight-700 m-0 d-flex align-items-center flex-wrap">
                                @if($experiencia->calificaciones_count > 0)
                                    @php $puntuacionFinal = (float) $experiencia->calificaciones_avg_estrellas; @endphp
                                    <span class="mr-2 d-flex align-items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($puntuacionFinal >= $i)
                                                <i class="fas fa-star text-warning"></i>
                                            @elseif($puntuacionFinal > ($i - 1) && $puntuacionFinal < $i)
                                                <i class="fas fa-star-half-alt text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                    </span>
                                    <span class="mr-2">{{ number_format($puntuacionFinal, 1, '.', '') }}</span>
                                    <span class="text-muted text-16 font-weight-normal">({{ $experiencia->calificaciones_count }} {{ trans_choice('Reseña|Reseñas', $experiencia->calificaciones_count) }})</span>
                                @else
                                    <i class="fas fa-star text-muted mr-2"></i>
                                    {{ __('Reseñas') }}
                                @endif
                            </h2>
                        </div>
                        <div class="carrusel-controles-desktop d-none d-lg-flex">
                            <button class="btn-carrusel-control btn-prev" data-target="#carrusel_reseñas" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn-carrusel-control btn-next" data-target="#carrusel_reseñas">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="container-carrusel-productos"
                         id="carrusel_reseñas"
                         data-tipo="reseñas"
                         data-id-negocio="{{ $experiencia->id }}">
                        
                        @forelse($calificaciones as $calificacion)
                            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_reseña', ['calificacion' => $calificacion])
                        @empty
                            <div class="w-100 bg-light p-5 rounded-12 text-center text-muted">
                                <i class="far fa-comment-dots fa-3x mb-3"></i>
                                <p class="m-0 text-16">{{ __('Aún no hay reseñas de este comercio.') }}</p>
                            </div>
                        @endforelse

                        @if($totalCalificaciones > 0)
                            @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_ver_todos', [
                                'items' => $calificaciones, 
                                'tipo' => 'reseñas', 
                                'idNegocio' => $experiencia->id, 
                                'tituloModal' => number_format($puntuacionFinal, 1, '.', '') . ' (' . $totalCalificaciones . ' ' . trans_choice('Reseña|Reseñas', $totalCalificaciones) . ')',
                                'total' => $totalCalificaciones
                            ])
                        @endif
                    </section>

            </div>
        </div>

    </div>
</div>

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

<!-- Modal Detalle Reseña (Comentario Completo) -->
<div class="modal fade" id="modalDetalleReseña" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-negocio-rounded shadow-lg border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-700 text-20">{{ __('Reseña Completa') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center mb-3" id="headerDetalleReseña">
                    <!-- Se poblará vía JS -->
                </div>
                <div class="text-16 text-justify overflow-auto texto-detalle-reseña" id="textoDetalleReseña">
                    <!-- Se poblará vía JS -->
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-dark btn-block rounded-pill font-weight-700" data-dismiss="modal">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>
</div>

@include('reda-alojamiento::general.modal_listado_infinito')

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
    @include('reda-alojamiento::general.main_footer')
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/listadoProductosServicios.min.js?v=' . time()) }}"></script>
@endsection
