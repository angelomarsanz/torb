@extends('template')
@section('main')
<div class="formulario-de-pasos-experiencias" data-step="{{ $paso }}"></div>

<style>
    .plan-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .plan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .plan-card.is-featured {
        border-color: #007bff;
    }
    .plan-card .card-body {
        min-height: 450px;
    }
    .price-value {
        font-size: 2.5rem;
    }
    .price-interval {
        font-size: 1rem;
        color: #6c757d;
    }
    .benefit-item i {
        width: 20px;
    }
    .badge-destacado {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>

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
                        <form method="post" id="list_des" action="{{ route('reda.negocios.experiencias.pasos', [$result->id, $paso]) }}" accept-charset='UTF-8'>
                            {{ csrf_field() }}
                            
                            <div class="col-md-12 p-0">
                                <h3 class="font-weight-700 mb-2">{{ __('Seleccione un plan para su negocio') }}</h3>
                                <p class="text-muted mb-5">{{ __('Elija el plan que mejor se adapte a sus necesidades y comience a disfrutar de los beneficios.') }}</p>
                            </div>

                            <div class="row m-0">
                                @forelse($planes as $plan)
                                    @php
                                        $opciones = is_array($plan->planes_pago) ? $plan->planes_pago : (json_decode($plan->planes_pago, true) ?: []);
                                        $beneficios = is_array($plan->beneficios) ? $plan->beneficios : (json_decode($plan->beneficios, true) ?: []);
                                    @endphp

                                    @foreach($opciones as $index => $opcion)
                                        <div class="col-12 col-lg-4 mb-4">
                                            <div class="card h-100 shadow-sm rounded-4 plan-card position-relative {{ $plan->destacado ? 'is-featured' : '' }}">
                                                
                                                @if($plan->destacado)
                                                    <span class="badge badge-primary badge-destacado">{{ __('Recomendado') }}</span>
                                                @endif

                                                <div class="card-body d-flex flex-column p-4">
                                                    <h4 class="font-weight-700 text-center mb-4">{{ $plan->nombre }}</h4>
                                                    
                                                    <div class="text-center mb-4">
                                                        <span class="price-value font-weight-700">{{ reda_money_format($opcion['moneda'], $opcion['precio']) }}</span>
                                                        <br />
                                                        <span class="price-interval">{{ __($opcion['lapso']) }}</span>
                                                    </div>

                                                    <div class="flex-grow-1">
                                                        <p class="font-weight-600 mb-3">{{ __('¿Qué incluye?') }}</p>
                                                        <ul class="list-unstyled">
                                                            @foreach($beneficios as $beneficio)
                                                                <li class="benefit-item mb-2 d-flex align-items-start">
                                                                    <i class="fa fa-check text-success mt-1 mr-2"></i>
                                                                    <span class="f-14">{{ $beneficio }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>

                                                    <div class="mt-4">
                                                        <button type="button" class="btn {{ $plan->destacado ? 'vbtn-success' : 'btn-outline-success' }} btn-block font-weight-700 py-3 rounded-pill">
                                                            {{ __('Elegir este plan') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @empty
                                    <div class="col-12 text-center p-5">
                                        <i class="fa fa-info-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">{{ __('No hay planes disponibles en este momento.') }}</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="col-md-12 p-0 mt-5 mb-5">
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

@section('validation_script')
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasosExperiencias.min.js?v=' . time()) }}"></script>
@endsection
