@extends('template')

@section('main')
<div id="index_experiencias"></div> {{-- Gancho para tu JS --}}

<div class="margin-top-85">
    <div class="row m-0">
        {{-- Incluimos el sidebar original --}}
        @include('users.sidebar')

        <div class="col-lg-10">
            <div class="main-panel">
                <div class="container-fluid min-height">
                    <div class="row">
                        <div class="col-md-12 p-0 mb-3">
                            <div class="list-bacground mt-4 rounded-3 p-4 border d-flex justify-content-between align-items-center">
                                <span class="text-18 pt-4 pb-4 font-weight-700">{{ __('Mis Negocios') }}</span>

                                {{-- Botón para crear nuevo, similar al flujo de Airbnb --}}
                                <a href="{{ url('reda/negocios/crear-experiencia') }}" class="btn vbg-default border">
                                    <i class="fa fa-plus"></i> {{ __('Nuevo Negocio') }}
                                </a>
                            </div>

                    <div class="row mt-4">
                        @forelse($experiencias as $experiencia)
                            <div class="col-md-12 p-0 mb-4">
                                <div class="card h-100 border rounded-3">
                                    <div class="card-body p-0">
                                        <div class="row">
                                            {{-- Columna de Imagen --}}
                                            <div class="col-md-3 p-0">
                                                <div class="img-container h-100">
                                                    @php
                                                        $fotoPortada = $experiencia->foto_portada;
                                                    @endphp

                                                    @if($fotoPortada)
                                                        <a href="{{ url('reda/negocios/formulario-de-pasos-experiencias/'.$experiencia->id.'/fotos') }}">
                                                            <img src="{{ asset('public/images/experiencias/' . $experiencia->id . '/' . $fotoPortada->photo) }}"
                                                                class="img-fluid w-100 h-100 object-fit-cover rounded-start img-min-150"
                                                                alt="{{ $experiencia->titulo }}">
                                                        </a>
                                                    @else
                                                        <a href="{{ url('reda/negocios/formulario-de-pasos-experiencias/'.$experiencia->id.'/fotos') }}">
                                                            <img src="{{ asset('public/img/unnamed.png') }}"
                                                                class="img-fluid w-100 h-100 object-fit-cover rounded-start img-min-150"
                                                                alt="Sin foto">
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Columna de Información --}}
                                            <div class="col-md-6 col-xl-6 p-4">
                                                <div class="mb-2">
                                                    <span class="badge bg-orange text-white">{{ ucfirst($experiencia->categoria_negocio ?? 'General') }}</span>
                                                </div>

                                                <a href="{{ url('reda/negocios/formulario-de-pasos-experiencias/'.$experiencia->id.'/descripcion') }}">
                                                    <div class="text-muted small mb-1">{{ __('ID:') }} {{ $experiencia->id }}</div>
                                                    <p class="text-18 font-weight-700 text-color mb-1">{{ $experiencia->titulo }}</p>
                                                </a>

                                                {{-- Promedio de Calificaciones --}}
                                                <div class="star-rating d-flex align-items-center justify-content-start">
                                                    <i class="fas fa-star text-warning mr-1"></i>
                                                    <span class="font-weight-700 text-dark mr-1">{{ number_format($experiencia->calificaciones_avg_estrellas ?? 0, 1, '.', '') }}</span>
                                                    <span class="text-muted small">
                                                        - 
                                                        @if($experiencia->calificaciones_count > 0)
                                                            {{ $experiencia->calificaciones_count }} {{ trans_choice('Reseña|Reseñas', $experiencia->calificaciones_count) }}
                                                        @else
                                                            {{ __('Sin reseñas todavía') }}
                                                        @endif
                                                    </span>
                                                </div>

                                                {{-- Contador de Visitas --}}
                                                <div class="mt-2 text-muted small">
                                                    <i class="fas fa-eye mr-1"></i> 
                                                    <span class="font-weight-700 text-dark">{{ number_format($experiencia->visitas ?? 0, 0, '.', '.') }}</span> 
                                                    {{ __('visitas al sitio') }}
                                                </div>
                                            </div>

                                            {{-- Columna de Acciones --}}
                                            <div class="col-md-3 col-xl-3 border-start-lg p-4 d-flex flex-row flex-md-column justify-content-center align-items-center">
                                                {{-- Botón Editar --}}
                                                <a href="{{ url('reda/negocios/formulario-de-pasos-experiencias/' . $experiencia->id . '/descripcion') }}"
                                                class="btn-list-action btn-edit mx-3 my-2 m-md-2">
                                                    <i class="fa fa-edit btn-list-icon"></i>
                                                    <span class="btn-list-text">{{ __('Editar') }}</span>
                                                </a>
                                                {{-- Botón Eliminar --}}
                                                <a href="javascript:void(0)"
                                                class="btn-list-action btn-delete mx-3 my-2 m-md-2 btn-eliminar-experiencia"
                                                data-id="{{ $experiencia->id }}">
                                                    <i class="fa fa-trash btn-list-icon"></i>
                                                    <span class="btn-list-text">{{ __('Eliminar') }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="row justify-content-center w-100 p-4 mt-4">
                                <div class="text-center w-100">
                                    <img src="{{ asset('public/img/unnamed.png') }}" class="img-fluid" alt="No encontrado">
                                    <p class="text-center mt-3">{{ __('Aún no tienes negocios registrados.') }}</p>
                                    <a href="{{ url('reda/negocios/crear-experiencia') }}" class="btn btn-success">{{ __('Crear mi primer negocio') }}</a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Paginación --}}
                    <div class="row justify-content-between pb-3 mt-4 mb-5">
                        {{ $experiencias->appends(request()->except('page'))->links('paginate') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('validation_script')
    <script>window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));</script>
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/indexExperiencias.min.js?v=' . time()) }}"></script>
@endsection
