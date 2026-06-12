@extends('template')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/css/user-front.min.css') }}" />
@endpush

@section('main')
<div id="listado_calificaciones_duenio"></div>

<div class="margin-top-85">
    <div class="row m-0">
        {{-- Incluimos el sidebar original --}}
        @include('users.sidebar')

        <div class="col-lg-10">
            <div class="main-panel">
                <div class="container-fluid min-height">
                    <div class="row">
                        <div class="col-md-12 p-0 mb-3">
                            <div class="list-bacground mt-4 rounded-3 p-4 border shadow-sm">
                                <h1 class="text-24 font-weight-700 m-0">{{ __('Calificaciones Recibidas') }}</h1>
                                <p class="text-muted m-0">{{ __('Aquí puedes ver lo que opinan los clientes sobre tus comercios.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 p-0">
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover border rounded-3 overflow-hidden shadow-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="200">{{ __('Comercio') }}</th>
                                            <th width="150">{{ __('Usuario') }}</th>
                                            <th width="150">{{ __('Puntuación') }}</th>
                                            <th>{{ __('Comentario') }}</th>
                                            <th width="150">{{ __('Fecha') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($calificaciones as $calificacion)
                                            <tr>
                                                <td class="align-middle">
                                                    <div class="font-weight-700">{{ $calificacion->experiencia->titulo }}</div>
                                                    <small class="text-muted">{{ $calificacion->experiencia->categoria_negocio }}</small>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        @php
                                                            $fotoUsuario = $calificacion->usuario->profile_image;
                                                            $rutaFotoUsuario = $fotoUsuario
                                                                ? asset('public/images/profile/' . $calificacion->usuario->id . '/' . $fotoUsuario)
                                                                : asset('public/images/default-profile.png');

                                                            // Seleccionar primer nombre y primer apellido de manera inteligente
                                                            $primerNombre = explode(' ', trim($calificacion->usuario->first_name))[0];
                                                            $primerApellido = explode(' ', trim($calificacion->usuario->last_name))[0];
                                                        @endphp
                                                        <img src="{{ $rutaFotoUsuario }}" class="img-profile-list img-size-30 mr-2">
                                                        <span class="text-14">{{ $primerNombre }} {{ $primerApellido }}</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="star-rating">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa fa-star {{ $i <= $calificacion->estrellas ? '' : 'text-light' }} star-rating-12"></i>
                                                        @endfor
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="text-muted italic">"{{ $calificacion->comentario ?: __('Sin comentario') }}"</div>
                                                </td>
                                                <td class="align-middle text-muted small">
                                                    {{ $calificacion->created_at->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fas fa-star-half-alt fa-3x mb-3 opacity-05"></i>
                                                    <p>{{ __('Aún no has recibido calificaciones en tus negocios.') }}</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Vista Móvil --}}
                            <div class="d-md-none">
                                @forelse($calificaciones as $calificacion)
                                    <div class="card mb-3 border rounded-4 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h3 class="text-16 font-weight-700 m-0">{{ $calificacion->experiencia->titulo }}</h3>
                                                    <div class="star-rating star-rating-large mt-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa fa-star {{ $i <= $calificacion->estrellas ? '' : 'text-light' }}"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $calificacion->created_at->format('d/m/Y') }}</small>
                                            </div>

                                            <div class="comment-box p-3 mb-3">
                                                <p class="m-0 text-14 italic">"{{ $calificacion->comentario ?: __('Sin comentario') }}"</p>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                @php
                                                    $fotoUsuario = $calificacion->usuario->profile_image;
                                                    $rutaFotoUsuario = $fotoUsuario
                                                        ? asset('public/images/profile/' . $calificacion->usuario->id . '/' . $fotoUsuario)
                                                        : asset('public/images/default-profile.png');

                                                    // Seleccionar primer nombre y primer apellido de manera inteligente
                                                    $primerNombre = explode(' ', trim($calificacion->usuario->first_name))[0];
                                                    $primerApellido = explode(' ', trim($calificacion->usuario->last_name))[0];
                                                @endphp
                                                <img src="{{ $rutaFotoUsuario }}" class="img-profile-list img-size-25 mr-2">
                                                <span class="text-13 font-weight-600">{{ $primerNombre }} {{ $primerApellido }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-star-half-alt fa-3x mb-3 opacity-05"></i>
                                        <p>{{ __('No hay calificaciones disponibles.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    <div class="row justify-content-between pb-3 mt-4 mb-5">
                        {{ $calificaciones->appends(request()->except('page'))->links('paginate') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
