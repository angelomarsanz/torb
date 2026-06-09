@extends('template')

@section('main')
<div id="calificacion_experiencia_frontend" data-id="{{ $experiencia->id }}"></div>

<div class="margin-top-85 mb-5">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                {{-- Encabezado Estilo Airbnb --}}
                <div class="d-flex align-items-center mb-4">
                    <a href="{{ url('reda/negocios/listado-productos-servicios/'.$experiencia->id) }}" class="text-color mr-3">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                    <h2 class="text-24 font-weight-700 m-0">{{ __('Califica tu experiencia') }}</h2>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-4">
                        {{-- Resumen del Comercio --}}
                        <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                            <div class="mr-3">
                                @php
                                    $foto = $experiencia->foto_portada;
                                    $rutaFoto = $foto ? asset('public/images/experiencias/'.$experiencia->id.'/'.$foto->photo) : asset('public/img/unnamed.png');
                                @endphp
                                <img src="{{ $rutaFoto }}" class="rounded-3 object-fit-cover" style="width: 70px; height: 70px;" alt="{{ $experiencia->titulo }}">
                            </div>
                            <div>
                                <span class="text-muted small text-uppercase font-weight-700">{{ $experiencia->categoria_negocio }}</span>
                                <h3 class="text-18 font-weight-700 m-0">{{ $experiencia->titulo }}</h3>
                            </div>
                        </div>

                        {{-- Formulario de Calificación --}}
                        <form id="form-calificacion" method="POST" action="{{ route('reda.negocios.experiencias.guardar_calificacion') }}">
                            @csrf
                            <input type="hidden" name="experiencia_id" value="{{ $experiencia->id }}">
                            <input type="hidden" name="estrellas" id="input-estrellas" value="0">

                            {{-- Selector de Estrellas Interactivo --}}
                            <div class="text-center mb-5">
                                <p class="font-weight-700 text-18 mb-3">{{ __('¿Qué puntuación le darías?') }}</p>
                                <div class="rating-stars-container d-flex justify-content-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="far fa-star star-item cursor-pointer mx-1" data-value="{{ $i }}" style="font-size: 36px; color: #FFB400;"></i>
                                    @endfor
                                </div>
                                <span id="error-estrellas" class="text-danger small d-none font-weight-700 mt-2 d-block">
                                    {{ __('Por favor, selecciona una puntuación') }}
                                </span>
                            </div>

                            {{-- Comentario --}}
                            <div class="form-group mb-4">
                                <label for="comentario" class="font-weight-700 text-16 mb-2">{{ __('Cuéntanos más detalles (opcional)') }}</label>
                                <textarea name="comentario" id="comentario" class="form-control rounded-3 p-3" rows="5" 
                                    placeholder="{{ __('¿Qué fue lo que más te gustó? ¿Qué podría mejorar?') }}"></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">{{ __('Máximo 1000 caracteres') }}</small>
                                    <small id="char-count" class="text-muted">0 / 1000</small>
                                </div>
                            </div>

                            {{-- Botón Enviar --}}
                            <div class="mt-5">
                                <button type="submit" id="btn-enviar-calificacion" class="btn vbtn-success w-100 py-3 font-weight-700 text-18 rounded-3">
                                    <i class="fa fa-spinner fa-spin d-none mr-2"></i>
                                    <span class="btn-text">{{ __('Enviar calificación') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Pie de página informativo --}}
                <p class="text-center text-muted small px-4">
                    {{ __('Tu calificación será pública y ayudará a otros miembros de la comunidad a tomar mejores decisiones.') }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Éxito --}}
<div class="modal fade" id="modalExitoCalificacion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-4 text-center p-5">
            <div class="mb-4">
                <i class="fa fa-check-circle fa-5x text-success"></i>
            </div>
            <h3 class="font-weight-700 mb-3">{{ __('¡Muchas gracias!') }}</h3>
            <p class="text-muted text-16 mb-4">{{ __('Tu calificación ha sido enviada correctamente. Valoramos mucho tu opinión.') }}</p>
            <button type="button" class="btn vbtn-outline-success w-100 py-3 font-weight-700 rounded-3" onclick="window.location.href='{{ url('reda/negocios/listado-productos-servicios/'.$experiencia->id) }}'">
                {{ __('Volver al comercio') }}
            </button>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .star-item:hover, .star-item.active {
        font-weight: 900 !important; /* fas equivalent */
    }
    .rounded-4 {
        border-radius: 1.5rem !important;
    }
    .object-fit-cover {
        object-fit: cover;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush

@section('validation_script')
    <script>
        window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));
    </script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/calificacionExperienciaFrontend.min.js?v=' . time()) }}"></script>
@endsection
