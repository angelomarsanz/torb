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
                        <form method="post" id="list_des" action="{{ route('reda.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8'>
                            {{ csrf_field() }}
                            <div class="col-md-12 border mt-4 pb-5 rounded-3 pl-4 pl-sm-0 pr-4 pr-sm-0 ">
								<div class="form-group col-md-12 main-panelbg pb-3 pt-3 mt-4 mt-sm-0 ">
									<h4 class="text-18 font-weight-700 pl-3">{{ __('Ubicación') }}</h4>
								</div>

								<input type="hidden" name='latitude' id='latitude' value="{{ $result->ubicacion['latitud'] ?? '' }}">
								<input type="hidden" name='longitude' id='longitude' value="{{ $result->ubicacion['longitud'] ?? '' }}">

                                <div class="row mt-4">
									<div class="col-md-12 pl-5 pr-5">
										<label>{{ __('País') }} <span class="text-danger">*</span></label>
										<select id="country" name="country" class="form-control text-16 mt-2">
											@foreach ($country as $key => $value)
												<option value="{{ $key }}" {{ (isset($result->ubicacion['pais']) && $key == $result->ubicacion['pais']) ? 'selected' : '' }}>{{ $value }}</option>
											@endforeach
										</select>
										<span class="text-danger">{{ $errors->first('country') }}</span>
									</div>
								</div>

								<div class="row mt-4">
									<div class="col-md-12 pl-5 pr-5">
										<label>{{ __('Línea de dirección 1') }} <span class="text-danger">*</span></label>
										<input type="text" name="address_line_1" id="address_line_1" value="{{ $result->ubicacion['linea_uno_direccion'] ?? '' }}" class="form-control text-16 mt-2" placeholder="{{ __('Nombre de la casa/número + calle/carretera') }}">
										<span class="text-danger">{{ $errors->first('address_line_1') }}</span>
									</div>
								</div>

								<div class="row mt-4">
									<div class="col-md-12 pl-5 pr-5">
										<div id="map_view" class="map-view-location" style="height: 400px; width: 100%;"></div>
									</div>
									<div class="col-md-12 mt-4 pl-5 pr-5">
										<p>{{ __('Puedes mover el puntero para establecer la posición correcta en el mapa') }}</p>
										<span class="text-danger">{{ $errors->first('latitude') }}</span>
									</div>
								</div>

								<div class="row mt-4">
									<div class="col-md-6 mt-4 pl-5 pr-5">
										<label>{{ __('Línea de dirección 2') }}</label>
										<input type="text" name="address_line_2" id="address_line_2" value="{{ $result->ubicacion['lineaDosDireccion'] ?? '' }}" class="form-control text-16 mt-2" placeholder="{{ __('Apto., suite, código de acceso al edificio') }}">
									</div>
									<div class="col-md-6 mt-4 pl-5 pr-5">
										<label>{{ __('Ciudad / Pueblo / Distrito') }}  <span class="text-danger">*</span></label>
										<input type="text" name="city" id="city" value="{{ $result->ubicacion['ciudad'] ?? '' }}" class="form-control text-16 mt-2">
										<span class="text-danger">{{ $errors->first('city') }}</span>
									</div>

									<div class="col-md-6 mt-4 pl-5 pr-5">
										<label>{{ __('Estado / Provincia / Condado / Región') }} <span class="text-danger">*</span></label>
										<input type="text" name="state" id="state" value="{{ $result->ubicacion['estado'] ?? '' }}" class="form-control text-16 mt-2">
										<span class="text-danger">{{ $errors->first('state') }}</span>
									</div>

									<div class="col-md-6 mt-4 pl-5 pr-5">
										<label>{{ __('Código postal') }}</label>
										<input type="text" name="postal_code" id="postal_code" value="{{ $result->ubicacion['codigo_postal'] ?? '' }}" class="form-control text-16 mt-2">
										<span class="text-danger">{{ $errors->first('postal_code') }}</span>
									</div>
								</div>
                            </div>

                            <div class="col-md-12 p-0 mt-4 mb-5">
                                <div class="row m-0 justify-content-between">
                                    <div class="mt-4">
										<a href="{{ route('reda.experiencias.pasos', [$result->id, 'actividades']) }}" class="btn btn-outline-danger secondary-text-color-hover text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3">
											{{ __('Atrás') }}
										</a>
									</div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn vbtn-outline-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3" id="btn_next">
                                            <i class="spinner fa fa-spinner fa-spin d-none"></i>
                                            <span id="btn_next-text">{{ __('Siguiente') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('validation_script')
    <script type="text/javascript" src='https://maps.google.com/maps/api/js?key={{ config("vrent.google_map_key") }}&libraries=places'></script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/locationpicker.jquery.min.js') }}"></script>

    <script type="text/javascript">
        'use strict'
        let nextText = "{{ __('Siguiente') }}..";
        let fieldRequiredText = "{{ __('Este campo es obligatorio.') }}";
        let maxlengthText = "{{ __('Por favor, no introduzcas más de 255 caracteres.') }}";
        // Coordenadas por defecto: Centro de Caracas (Plaza Bolívar)
        let latitude = "{{ (isset($result->ubicacion['latitud']) && $result->ubicacion['latitud'] != '') ? $result->ubicacion['latitud'] : '10.5061' }}";
        let longitude = "{{ (isset($result->ubicacion['longitud']) && $result->ubicacion['longitud'] != '') ? $result->ubicacion['longitud'] : '-66.9145' }}";
    </script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasoExperiencias.min.js?v=' . time()) }}"></script>
@endsection
