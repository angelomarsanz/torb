@extends('template')
@section('main')
<div id="create_experiencia"></div>
<div class="mb-4 margin-top-85">
    <div class="row m-0">
        @include('users.sidebar')
        <div class="col-md-10 min-height">
            <div class="main-panel m-4 list-background border rounded-3">
                <h3 class="text-center mt-5 text-24 font-weight-700">{{ __('Publica tu Experiencia') }}</h3>
                <p class="text-center text-16 pl-4 pr-4">{{ __('Comparte una actividad única con viajeros de todo el mundo.') }}</p>
                
                <form id="list_experience" method="post" action="{{ route('reda.experiencias.create') }}" class="mt-4" accept-charset='UTF-8'>
                    {{ csrf_field() }}
                    
                    {{-- Campos ocultos para ubicación --}}
                    <input type="hidden" name='latitud_encuentro' id='latitude'>
                    <input type="hidden" name='longitud_encuentro' id='longitude'>

                    <div class="row p-4">
                        {{-- Título de la Experiencia --}}
                        <div class="col-md-12">
                            <div class="form-group mt-4">
                                <label>{{ __('Título de la Experiencia') }} <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control text-16" placeholder="{{ __('Ej: Tour de café en la montaña') }}" value="{{ old('titulo') }}">
                                @if ($errors->has('titulo')) <p class="error-tag">{{ $errors->first('titulo') }}</p> @endif
                            </div>
                        </div>

                        {{-- Tipo de Moneda --}}
                        <div class="col-md-6">
                            <div class="form-group mt-4">
                                <label>{{ __('Moneda') }}</label>
                                <select name="tipo_moneda" class="form-control text-16">
                                    <option value="USD">USD - Dólares</option>
                                    <option value="EUR">EUR - Euros</option>
                                    {{-- Aquí podrías iterar sobre una variable $currencies si la pasas desde el controller --}}
                                </select>
                            </div>
                        </div>

                        {{-- Precio por Persona --}}
                        <div class="col-md-6">
                            <div class="form-group mt-4">
                                <label>{{ __('Precio por Persona') }} <span class="text-danger">*</span></label>
                                <input type="number" name="precio_persona" class="form-control text-16" step="0.01" placeholder="0.00">
                                @if ($errors->has('precio_persona')) <p class="error-tag">{{ $errors->first('precio_persona') }}</p> @endif
                            </div>
                        </div>

                        {{-- Ubicación (Mapa) --}}
                        <div class="col-md-12">
                            <div class="form-group mt-4">
                                <label>{{ __('Punto de Encuentro') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-16" id="map_address" name="direccion_mapa" placeholder="{{ __('¿Dónde comienza la experiencia?') }}">
                                <div id="us3" style="width: 100%; height: 300px;" class="mt-2"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="float-right">
                                <button type="submit" class="btn vbtn-outline-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3 mt-4 mb-4" id="btn_next"> 
                                    <i class="spinner fa fa-spinner fa-spin d-none"></i>
                                    <span id="btn_next-text">{{ __('Continuar') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('validation_script')
    <script type="text/javascript" src='https://maps.googleapis.com/maps/api/js?key={{ config("vrent.google_map_key") }}&libraries=places'></script>
    <script type="text/javascript" src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/locationpicker.jquery.min.js') }}"></script>

    <script type="text/javascript">
        'use strict'
        let continueText = "{{ __('Continuando') }}..";
        let fieldRequiredText = "{{ __('Este campo es obligatorio.') }}";
    </script>
@endsection