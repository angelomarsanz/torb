@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="calificacion_experiencia"></div>
@stop

@section('validation_script')
    <script src="{{ asset('public/js/reda/general/notificaciones.min.js?v=' . time()) }}"></script>
    <script src="{{ asset('public/js/reda/vistas/experiencia/frontend/calificacionExperiencia.min.js?v=' . time()) }}"></script>
@endsection
