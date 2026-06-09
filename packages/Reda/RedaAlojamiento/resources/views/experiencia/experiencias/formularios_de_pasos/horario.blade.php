@extends('template')
@section('main')
<div class="formulario-de-pasos-experiencias" data-step="{{ $paso }}"></div>
<div class="margin-top-85">
    <div class="row m-0">
        @include('users.sidebar')
        <div class="col-md-10">
            <div class="main-panel min-height mt-4">
                <div class="row justify-content-center">
                    <div class="col-md-3 pl-4 pr-4">
                        @include('pasos::menu_lateral')
                    </div>

                    <div class="col-md-9 mt-4 mt-sm-0 pl-4 pr-4">
                        <form method="post" id="list_des" action="{{ route('reda.negocios.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8'>
                            {{ csrf_field() }}
                            <div class="col-md-12 border mt-4 pb-5 rounded-3 pl-sm-3 pr-sm-3">
                                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                                    <h4 class="font-weight-700 m-0">{{ __('Horarios') }}</h4>
                                    <button type="button" class="btn vbtn-outline-success" id="btn-agregar-horario">
                                        <i class="fa fa-plus"></i> {{ __('Agregar nuevo horario') }}
                                    </button>
                                </div>

                                <div id="listado-horarios-container">
                                    @include('reda-alojamiento::experiencia.experiencias.formularios_de_pasos.partials.listado_horarios', ['horarios' => $result->horarios])
                                </div>
                            </div>

                            <div class="col-md-12 p-0 mt-4 mb-5">
                                <div class="row m-0 justify-content-between">
                                    <button type="submit" class="btn vbtn-outline-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3" id="btn_next">
                                        <i class="spinner fa fa-spinner fa-spin d-none"></i>
                                        <span id="btn_next-text">{{ __('Siguiente') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Horario -->
<div class="modal fade" id="modalHorario" tabindex="-1" role="dialog" aria-labelledby="modalHorarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-700" id="modalHorarioLabel">{{ __('Configurar Horario') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-modal-horario">
                    {{ csrf_field() }}
                    <input type="hidden" id="horario-index" value="">

                    <div class="form-group">
                        <label class="font-weight-700">{{ __('Días de la semana') }}</label>
                        <div class="d-flex flex-wrap justify-content-between mt-2">
                            @php
                                $diasSemana = [
                                    'lun' => 'Lun',
                                    'mar' => 'Mar',
                                    'mie' => 'Mie',
                                    'jue' => 'Jue',
                                    'vie' => 'Vie',
                                    'sab' => 'Sáb',
                                    'dom' => 'Dom'
                                ];
                            @endphp
                            @foreach($diasSemana as $key => $label)
                                <div class="custom-control custom-checkbox mr-3 mb-2">
                                    <input type="checkbox" class="custom-control-input check-dia" id="dia-{{ $key }}" value="{{ $key }}" name="dias[]">
                                    <label class="custom-control-label" for="dia-{{ $key }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="font-weight-700 m-0">{{ __('Bloques de horas') }}</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-bloque">
                                <i class="fa fa-plus"></i> {{ __('Añadir bloque de horas') }}
                            </button>
                        </div>
                        <div id="bloques-container">
                            <!-- Los bloques se añadirán aquí dinámicamente -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn vbtn-outline-success" id="btn-guardar-horario-modal">
                    <i class="fa fa-spinner fa-spin d-none spinner-save"></i>
                    <span class="btn-text">{{ __('Aceptar') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Notificación -->
<div class="modal fade" id="modal-notificacion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center p-4">
            <div id="notificacion-icono" class="mb-3"></div>
            <h4 id="notificacion-titulo" class="font-weight-700"></h4>
            <p id="notificacion-mensaje" class="text-muted"></p>
            <button type="button" class="btn vbtn-outline-success w-100" data-dismiss="modal">{{ __('Entendido') }}</button>
        </div>
    </div>
</div>

<!-- Modal Confirmación -->
<div class="modal fade" id="modal-confirmacion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center p-4">
            <i class="fa fa-exclamation-triangle fa-4x text-warning mb-3"></i>
            <h4 class="font-weight-700">{{ __('¿Estás seguro?') }}</h4>
            <p id="confirmacion-mensaje" class="text-muted"></p>
            <div class="d-flex justify-content-center mt-3">
                <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">{{ __('No, cancelar') }}</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-si">
                    <i class="fa fa-spinner fa-spin d-none"></i>
                    <span class="btn-text">{{ __('Sí, eliminar') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('validation_script')
    <script>
        const EXPERIENCIA_ID = '{{ $result->id }}';
    </script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasosExperiencias.min.js?v=' . time()) }}"></script>
@endsection
