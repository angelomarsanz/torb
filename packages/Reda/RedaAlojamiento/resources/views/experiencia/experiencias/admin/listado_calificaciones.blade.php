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
                            <div class="list-bacground mt-4 rounded-3 p-4 border">
                                <h1 class="text-24 font-weight-700 m-0">{{ __('Calificaciones Recibidas') }}</h1>
                                <p class="text-muted m-0">{{ __('Aquí puedes ver lo que opinan los clientes sobre tus comercios.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 p-0">
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover border rounded-3 overflow-hidden">
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
                                                        <img src="{{ $calificacion->usuario->profile_src }}" class="img-profile-list img-size-30 mr-2">
                                                        <span>{{ $calificacion->usuario->first_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="star-rating">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa fa-star {{ $i <= $calificacion->estrellas ? '' : 'text-muted' }}"></i>
                                                        @endfor
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="text-muted italic">"{{ $calificacion->comentario ?: __('Sin comentario') }}"</div>
                                                </td>
                                                <td class="align-middle">
                                                    {{ $calificacion->created_at->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fa fa-star-o fa-3x mb-3"></i>
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
                                                            <i class="fa fa-star {{ $i <= $calificacion->estrellas ? '' : 'text-muted' }}"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $calificacion->created_at->format('d/m/Y') }}</small>
                                            </div>
                                            
                                            <div class="comment-box p-3 mb-3">
                                                <p class="m-0 text-14 italic">"{{ $calificacion->comentario ?: __('Sin comentario') }}"</p>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <img src="{{ $calificacion->usuario->profile_src }}" class="img-profile-list img-size-25 mr-2">
                                                <span class="text-13 font-weight-600">{{ $calificacion->usuario->full_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted">
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
