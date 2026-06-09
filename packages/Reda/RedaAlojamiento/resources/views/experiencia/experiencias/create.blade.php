@extends('template')
@section('main')
<div id="create_experiencia"></div>
<div class="mb-4 margin-top-85">
    <div class="row m-0">
        @include('users.sidebar')
        <div class="col-md-10 min-height">
            <div class="main-panel m-4 list-background border rounded-3">
                <h3 class="text-center mt-5 text-24 font-weight-700">{{ __('reda-alojamiento::messages.general.publica_tu_negocio') }}</h3>
                <p class="text-center text-16 pl-4 pr-4">{{ __('reda-alojamiento::messages.general.comparte_la_informacion_de_tu_negocio_con_viajeros_de_todo_el_mundo') }}</p>               
                <form id="list_experience" method="post" action="{{ route('reda.negocios.experiencias.create') }}" class="mt-4" accept-charset='UTF-8'>
                    {{ csrf_field() }}
                    
                    <div class="row p-4">
                        <div class="col-md-12">
                            <div class="form-group mt-4">
                                <label>{{ __('reda-alojamiento::messages.general.nombre_del_negocio') }} <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control text-16" placeholder="" value="{{ old('titulo') }}">
                                @if ($errors->has('titulo')) <p class="error-tag">{{ $errors->first('titulo') }}</p> @endif
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="float-right">
                                <button type="submit" class="btn vbtn-outline-success text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3 mt-4 mb-4" id="btn_next"> 
                                    <i class="spinner fa fa-spinner fa-spin d-none"></i>
                                    <span id="btn_next-text">{{ __('reda-alojamiento::messages.general.continuar') }}</span>
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
    <script>window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));</script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/createExperiencias.min.js?v=' . time()) }}"></script>
@endsection