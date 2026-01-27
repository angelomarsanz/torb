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
                    
                    <div class="row p-4">
                        {{-- Título de la Experiencia --}}
                        <div class="col-md-12">
                            <div class="form-group mt-4">
                                <label>{{ __('Título de la Experiencia') }} <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control text-16" placeholder="{{ __('Ej: Tour de café en la montaña') }}" value="{{ old('titulo') }}">
                                @if ($errors->has('titulo')) <p class="error-tag">{{ $errors->first('titulo') }}</p> @endif
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
    <script type="text/javascript" src="{{ asset('js/jquery.validate.min.js') }}"></script>

    <script type="text/javascript">
        'use strict'
        let continueText = "{{ __('Continuando') }}..";
        let fieldRequiredText = "{{ __('Este campo es obligatorio.') }}";
    </script>
@endsection