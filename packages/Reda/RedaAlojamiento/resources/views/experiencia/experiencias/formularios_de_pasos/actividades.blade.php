@extends('template')
@section('main')
<div class="formulario-de-pasos" data-step="{{ $paso }}"></div>
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
                            <div class="col-md-12 border mt-4 pb-5 rounded-3 pl-sm-0 pr-sm-0 ">
                                @php
                                    echo "Actividades";
                                @endphp
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
@endsection
