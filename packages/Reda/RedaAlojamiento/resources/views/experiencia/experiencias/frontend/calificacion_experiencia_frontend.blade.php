@extends('template')

@section('main')
<div id="calificacion_experiencia_frontend" data-id="{{ $experiencia->id }}" data-es-duenio="{{ $esDuenio ? 'true' : 'false' }}"></div>

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
                            @php
                                $logo = $experiencia->ruta_imagenes;
                                $fotoPortada = $experiencia->foto_portada; // Accesor que ya prioriza Destacada > Primera
                                
                                $rutaImagen = null;
                                $esLogo = false;

                                // Prioridad 1: Logo del comercio
                                if ($logo) {
                                    $rutaImagen = asset('public/images/logos_negocios/'.$logo);
                                    $esLogo = true;
                                } 
                                // Prioridad 2 y 3: Foto Destacada o en su defecto la Primera foto
                                elseif ($fotoPortada) {
                                    $rutaImagen = asset('public/images/experiencias/'.$experiencia->id.'/'.$fotoPortada->photo);
                                    $esLogo = false;
                                }
                            @endphp

                            <div class="negocio-media-container mr-3">
                                @if($rutaImagen)
                                    <img src="{{ $rutaImagen }}" 
                                         class="{{ $esLogo ? 'img-negocio-logo' : 'img-negocio-portada' }}"
                                         alt="{{ $experiencia->titulo }}">
                                @else
                                    {{-- Prioridad 4: Ícono por defecto si no hay ninguna imagen --}}
                                    <i class="fas fa-store text-muted icon-negocio-default"></i>
                                @endif
                            </div>

                            <div>
                                <span class="text-muted small text-uppercase font-weight-700 tracking-wider">{{ $experiencia->categoria_negocio }}</span>
                                <h3 class="text-18 font-weight-700 m-0">{{ $experiencia->titulo }}</h3>
                            </div>
                        </div>

                        @if($esDuenio)
                            {{-- Mensaje para el dueño --}}
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-exclamation-triangle fa-4x text-warning"></i>
                                </div>
                                <h4 class="font-weight-700 mb-3">{{ __('Acción no permitida') }}</h4>
                                <p class="text-muted text-16 mb-4">
                                    {{ __('Usted no puede calificar su propio negocio.') }}
                                </p>
                                <a href="{{ url('reda/negocios/listado-productos-servicios/'.$experiencia->id) }}" class="btn vbtn-outline-success px-5 rounded-3">
                                    {{ __('Volver al comercio') }}
                                </a>
                            </div>
                        @else
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
                                            <i class="far fa-star star-item mx-1" data-value="{{ $i }}"></i>
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
                        @endif
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

@section('validation_script')
    <script>
        window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));
        window.RedaAlojamientoJson = @json(__('reda-alojamiento::es'));
    </script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/frontend/calificacionExperienciaFrontend.min.js?v=' . time()) }}"></script>
@endsection
