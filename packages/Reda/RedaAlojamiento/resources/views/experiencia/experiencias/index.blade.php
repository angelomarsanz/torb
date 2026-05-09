@extends('template')
@section('main')
<div id="index_experiencias"></div>
<div class="mb-4 margin-top-85">
    <div class="row m-0">
        @include('users.sidebar')
        <div>
        </div>
    </div>
</div>
@endsection

@section('validation_script')
    <script>window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));</script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/indexExperiencias.min.js?v=' . time()) }}"></script>
@endsection