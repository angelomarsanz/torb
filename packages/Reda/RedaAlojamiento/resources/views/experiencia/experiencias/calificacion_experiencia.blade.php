@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="calificacion_experiencia"></div>

<div class="margin-top-85">
    <div class="row m-0">
        {{-- Incluimos el sidebar original --}}
        @include('users.sidebar')

        <div class="col-lg-10">
            <div class="main-panel">
                <div class="container-fluid min-height">
                    <div class="row">
                        <div class="col-md-12 p-0 mb-3">
                            <div class="list-bacground mt-4 rounded-3 p-4 border d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <h1 class="text-24 font-weight-700 m-0">{{ __('Material para Calificaciones') }}</h1>
                                    <p class="text-muted m-0">{{ __('Elige cómo quieres descargar el acceso para que tus clientes califiquen tu comercio.') }}</p>
                                </div>
                                <div class="alert alert-info border-0 shadow-sm m-0 p-3 rounded-3 img-max-400">
                                    <i class="fa fa-info-circle mr-2"></i>
                                    <small>{{ __('Puedes descargar el código QR individual para tus diseños propios, o generar un cartel diseñado listo para imprimir y colgar en tu negocio.') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        @forelse($experiencias as $experiencia)
                            <div class="col-md-6 col-xl-4 mb-4">
                                <div class="card h-100 border rounded-4 shadow-sm qr-card-container">
                                    <div class="card-body p-4 d-flex flex-column">
                                        {{-- Info del Negocio --}}
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="mr-3">
                                                @php
                                                    $foto = $experiencia->fotos->first();
                                                    $rutaFoto = $foto ? asset('public/images/experiencias/'.$experiencia->id.'/'.$foto->photo) : asset('public/img/unnamed.png');
                                                @endphp
                                                <img src="{{ $rutaFoto }}" class="rounded-3 img-size-50 object-fit-cover">
                                            </div>
                                            <div class="overflow-hidden">
                                                <h3 class="text-16 font-weight-700 m-0 text-truncate">{{ $experiencia->titulo }}</h3>
                                                <span class="badge bg-light text-dark border small">{{ $experiencia->categoria_negocio }}</span>
                                            </div>
                                        </div>

                                        {{-- Vista Previa Real del QR --}}
                                        <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center bg-white rounded-3 p-4 mb-4 border shadow-sm">
                                            <div class="qrcode-preview" 
                                                 data-url="{{ route('reda.negocios.experiencias.calificar', $experiencia->id) }}">
                                                {{-- Aquí se generará el QR mediante JS --}}
                                            </div>
                                            <p class="text-muted small text-center mt-3 m-0 font-weight-600">{{ __('Código QR dinámico') }}</p>
                                        </div>

                                        {{-- Acciones --}}
                                        <div class="mt-auto">
                                            <div class="row m-0">
                                                <div class="col-12 p-0 mb-2">
                                                    <a href="{{ route('reda.negocios.experiencias.descargar_cartel', $experiencia->id) }}" class="btn vbtn-success w-100 font-weight-700 py-2">
                                                        <i class="fas fa-file-pdf mr-2"></i> {{ __('Generar Cartel Impresión') }}
                                                    </a>
                                                </div>
                                                <div class="col-12 p-0">
                                                    <button class="btn btn-outline-dark w-100 font-weight-700 btn-generar-qr py-2" 
                                                            data-id="{{ $experiencia->id }}"
                                                            data-url="{{ route('reda.negocios.experiencias.calificar', $experiencia->id) }}"
                                                            data-nombre="{{ $experiencia->titulo }}">
                                                        <i class="fas fa-image mr-2"></i> {{ __('Descargar Solo QR') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
@empty
                            <div class="col-12 text-center py-5">
                                <img src="{{ asset('public/img/unnamed.png') }}" class="img-fluid mb-3 img-max-150">
                                <p class="text-muted">{{ __('No tienes negocios registrados para generar material de calificación.') }}</p>
                                <a href="{{ route('reda.negocios.experiencias.create') }}" class="btn btn-success">{{ __('Crear mi primer negocio') }}</a>
                            </div>
                        @endforelse
                    </div>

                    {{-- Paginación --}}
                    <div class="row justify-content-between pb-3 mt-4 mb-5">
                        {{ $experiencias->appends(request()->except('page'))->links('paginate') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Generando (Cargando) --}}
<div class="modal fade" id="modalGenerandoQR" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-4 p-5 text-center">
            <i class="fa fa-spinner fa-spin fa-4x text-success mb-4"></i>
            <h3 class="font-weight-700 mb-2">{{ __('Preparando archivo') }}</h3>
            <p class="text-muted">{{ __('Estamos procesando la solicitud. Por favor espera un momento...') }}</p>
        </div>
    </div>
</div>
@stop

@section('validation_script')
    <script src="{{ asset('public/js/reda/general/notificaciones.min.js?v=' . time()) }}"></script>
    {{-- CDN para generación de QR en cliente (para la opción de Solo QR) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="{{ asset('public/js/reda/vistas/experiencia/calificacionExperiencia.min.js?v=' . time()) }}"></script>
@endsection
