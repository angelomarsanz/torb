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
                                <span class="text-18 pt-4 pb-4 font-weight-700">Mis Negocios</span>

                                {{-- Botón para crear nuevo, similar al flujo de Airbnb --}}
                                <a href="{{ url('reda/crear-experiencia') }}" class="btn vbg-default border">
                                    <i class="fa fa-plus"></i> Nuevo Negocio
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
                                                        <a href="{{ url('reda/formulario-de-pasos-experiencias/'.$experiencia->id.'/fotos') }}">
                                                            <img src="{{ asset('public/images/experiencias/' . $experiencia->id . '/' . $fotoPortada->photo) }}"
                                                                class="img-fluid w-100 h-100 object-fit-cover rounded-start"
                                                                alt="{{ $experiencia->titulo }}"
                                                                style="min-height: 150px; object-fit: cover;">
                                                        </a>
                                                    @else
                                                        <a href="{{ url('reda/formulario-de-pasos-experiencias/'.$experiencia->id.'/fotos') }}">
                                                            <img src="{{ asset('public/img/unnamed.png') }}"
                                                                class="img-fluid w-100 h-100 object-fit-cover rounded-start"
                                                                alt="Sin foto"
                                                                style="min-height: 150px; object-fit: cover;">
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Columna de Información --}}
                                            <div class="col-md-6 col-xl-6 p-4">
                                                <div class="d-flex justify-content-between">
                                                    <span class="badge bg-orange text-white mb-2">{{ ucfirst($experiencia->categoria_negocio ?? 'General') }}</span>
                                                </div>
                                                <a href="{{ url('reda/formulario-de-pasos-experiencias/'.$experiencia->id.'/descripcion') }}">
                                                    <div class=\"text-muted small mb-1\">ID: {{ $experiencia->id }}</div>
                                                    <p class="text-18 font-weight-700 text-color">{{ $experiencia->titulo }}</p>
                                                </a>
                                            </div>

                                            {{-- Columna de Acciones --}}
                                            <div class="col-md-3 col-xl-3 border-start-lg p-4 d-flex flex-column justify-content-center">
                                                {{-- Botón Editar --}}
                                                <a href="{{ url('reda/formulario-de-pasos-experiencias/' . $experiencia->id . '/descripcion') }}"
                                                class="d-flex flex-column align-items-center text-center text-decoration-none mx-3"
                                                style="color: #222 !important;">
                                                    <i class="fa fa-edit" style="font-size: 20px !important; display: block !important; margin-bottom: 2px;"></i>
                                                    <span class="font-weight-700" style="font-size: 13px; display: block;">Editar</span>
                                                </a>
                                                {{-- Botón Eliminar --}}
                                                <a href="javascript:void(0)"
                                                class="d-flex flex-column align-items-center text-center text-decoration-none mx-3 btn-eliminar-experiencia"
                                                data-id="{{ $experiencia->id }}"
                                                style="color: #dc3545 !important; cursor: pointer;">
                                                    <i class="fa fa-trash" style="font-size: 20px !important; display: block !important; margin-bottom: 2px;"></i>
                                                    <span class="font-weight-700" style="font-size: 13px; display: block;">Eliminar</span>
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
                                    <p class="text-center mt-3">Aún no tienes negocios registrados.</p>
                                    <a href="{{ url('reda/create-experiencias') }}" class="btn btn-success">Crear mi primer negocio</a>
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
