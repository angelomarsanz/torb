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
                                <h1 class="text-24 font-weight-700 m-0">{{ __('Calificaciones por Negocio') }}</h1>
                                <p class="text-muted m-0">{{ __('Resumen de la reputación de cada uno de tus comercios.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 p-0">
                            {{-- Vista Escritorio --}}
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover border rounded-3 overflow-hidden shadow-sm align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="100"></th>
                                            <th>{{ __('Negocio') }}</th>
                                            <th width="250">{{ __('Calificación Promedio') }}</th>
                                            <th width="150" class="text-center">{{ __('Reseñas') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($negocios as $negocio)
                                            @php
                                                $logo = null;
                                                if ($negocio->ruta_imagenes) {
                                                    $logo = asset('public/images/logos_negocios/' . $negocio->ruta_imagenes);
                                                } else {
                                                    $fotoPortada = $negocio->fotos->where('cover_photo', 1)->first();
                                                    if (!$fotoPortada) {
                                                        $fotoPortada = $negocio->fotos->first();
                                                    }

                                                    if ($fotoPortada) {
                                                        $logo = asset('public/images/experiencias/' . $negocio->id . '/' . $fotoPortada->photo);
                                                    }
                                                }
                                                $promedio = round($negocio->calificaciones_avg_estrellas ?? 0, 1);
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <div class="negocio-media-resumen-container mx-auto">
                                                        @if($logo)
                                                            <img src="{{ $logo }}" class="img-negocio-resumen" alt="{{ $negocio->titulo }}">
                                                        @else
                                                            <i class="fas fa-store fa-2x text-muted opacity-05"></i>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-18 font-weight-700">{{ $negocio->titulo }}</div>
                                                    <small class="text-muted">{{ $negocio->categoria_negocio }}</small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="star-rating mr-2">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fa fa-star {{ $i <= $promedio ? '' : 'text-light' }} text-14"></i>
                                                            @endfor
                                                        </div>
                                                        <span class="font-weight-700 text-16">{{ number_format($promedio, 1) }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('reda.negocios.experiencias.detalle_calificaciones', $negocio->id) }}" class="text-success font-weight-700 text-16">
                                                        ({{ $negocio->calificaciones_count }}) {{ trans_choice(__('Reseña|Reseñas'), $negocio->calificaciones_count) }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="fas fa-store-slash fa-3x mb-3 opacity-05"></i>
                                                    <p>{{ __('Aún no tienes negocios registrados.') }}</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Vista Móvil --}}
                            <div class="d-md-none">
                                @forelse($negocios as $negocio)
                                    @php
                                        $logo = null;
                                        if ($negocio->ruta_imagenes) {
                                            $logo = asset('public/images/logos_negocios/' . $negocio->ruta_imagenes);
                                        } else {
                                            $fotoPortada = $negocio->fotos->where('cover_photo', 1)->first();
                                            if (!$fotoPortada) {
                                                $fotoPortada = $negocio->fotos->first();
                                            }

                                            if ($fotoPortada) {
                                                $logo = asset('public/images/experiencias/' . $negocio->id . '/' . $fotoPortada->photo);
                                            }
                                        }
                                        $promedio = round($negocio->calificaciones_avg_estrellas ?? 0, 1);
                                    @endphp
                                    <div class="card card-negocio mb-3 border rounded-4 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <div class="negocio-media-mobile-container">
                                                        @if($logo)
                                                            <img src="{{ $logo }}" class="img-negocio-mobile" alt="{{ $negocio->titulo }}">
                                                        @else
                                                            <i class="fas fa-store fa-lg text-muted opacity-05"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <h3 class="text-16 font-weight-700 m-0 text-truncate">{{ $negocio->titulo }}</h3>
                                                    <div class="d-flex align-items-center mt-1">
                                                        <div class="star-rating mr-2">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="fa fa-star {{ $i <= $promedio ? '' : 'text-light' }} text-12"></i>
                                                            @endfor
                                                        </div>
                                                        <span class="font-weight-700 text-14">{{ number_format($promedio, 1) }}</span>
                                                    </div>
                                                    <div class="mt-1">
                                                        <a href="{{ route('reda.negocios.experiencias.detalle_calificaciones', $negocio->id) }}" class="text-success font-weight-700 text-14">
                                                            ({{ $negocio->calificaciones_count }}) {{ trans_choice(__('Reseña|Reseñas'), $negocio->calificaciones_count) }}
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="ml-2">
                                                    <a href="{{ route('reda.negocios.experiencias.detalle_calificaciones', $negocio->id) }}" class="btn btn-sm btn-light rounded-circle">
                                                        <i class="fa fa-chevron-right text-muted"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-store-slash fa-3x mb-3 opacity-05"></i>
                                        <p>{{ __('No hay negocios disponibles.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Paginación --}}
                    <div class="row justify-content-between pb-3 mt-4 mb-5">
                        {{ $negocios->appends(request()->except('page'))->links('paginate') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
