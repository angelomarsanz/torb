@extends('template')
@section('main')
<div class="formulario-de-pasos-experiencias" data-step="{{ $paso }}"></div>

<style>
    /* Estilos para el listado de planes interactivo - Versión Refinada */
    .pricing-card {
        border: 2px solid #e2e8f0;
        border-radius: 24px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        background: #fff;
        overflow: visible; /* Para que la etiqueta sobresalga sin cortarse */
        margin-top: 15px; /* Espacio para que la etiqueta no pegue arriba */
    }
    .pricing-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }
    
    /* Estilo para Plan Estándar */
    .pricing-card.is-active {
        border-color: #64748b;
        background-color: #f8fafc;
    }
    .pricing-card.is-active::before {
        content: '\f058';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 20px;
        right: 20px;
        color: #64748b;
        font-size: 24px;
        z-index: 2;
    }

    /* Estilo para Plan Destacado (Indigo Premium) */
    .pricing-card.is-featured {
        border-color: #e2e8f0;
    }
    .pricing-card.is-featured.is-active {
        border-color: #6366f1;
        background-color: #f5f3ff;
    }
    .pricing-card.is-featured.is-active::before {
        color: #6366f1;
    }
    
    .badge-featured {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #fff;
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: absolute;
        top: -18px; /* Ajustado para que no se corte */
        left: 50%;
        transform: translateX(-50%);
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        z-index: 10;
        white-space: nowrap;
    }
    
    /* Selector de Lapsos (Grid 2x2 para mejor ajuste) */
    .pricing-selector-group {
        display: grid;
        grid-template-columns: repeat(2, 1fr); /* 2 arriba, 2 abajo */
        gap: 6px;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 14px;
        margin: 20px 0;
    }
    .pricing-btn-lapso {
        border: none;
        background: transparent;
        padding: 10px 4px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        transition: all 0.2s;
        cursor: pointer;
        text-align: center;
        outline: none !important;
    }
    .pricing-btn-lapso.active {
        background: #fff;
        color: #4f46e5;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .pricing-card:not(.is-featured) .pricing-btn-lapso.active {
        color: #334155;
    }
    
    /* Área de Precio */
    .pricing-price-box {
        margin: 15px 0 25px 0;
        min-height: 90px;
    }
    .pricing-amount {
        font-size: 36px;
        font-weight: 900;
        color: #1e293b;
        display: block;
    }
    .pricing-period {
        color: #94a3b8;
        font-size: 14px;
        font-weight: 600;
        text-transform: capitalize;
    }
    
    /* Características */
    .pricing-features {
        list-style: none;
        padding: 0;
        margin: 20px 0;
    }
    .pricing-feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
        font-size: 14px;
        color: #475569;
        font-weight: 500;
    }
    .pricing-feature-item i {
        color: #10b981;
        font-size: 16px;
    }
    
    .pricing-btn-select {
        width: 100%;
        padding: 16px;
        border-radius: 16px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
        border: 2px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        outline: none !important;
    }
    .pricing-card.is-active .pricing-btn-select {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
        box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
    }
    .pricing-card:not(.is-featured).is-active .pricing-btn-select {
        background: #334155;
        border-color: #334155;
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
                            
                            @php
                                $planGuardado = $result->plan_negocios['plan_id'] ?? '';
                                $opcionIndiceGuardado = $result->plan_negocios['plan_opcion_index'] ?? 0;
                            @endphp

                            <input type="hidden" name="plan_id" id="selected_plan_id" value="{{ $planGuardado }}">
                            <input type="hidden" name="plan_opcion_index" id="selected_plan_opcion_index" value="{{ $opcionIndiceGuardado }}">

                            <div class="col-md-12 p-0 text-center text-md-left">
                                <h2 class="font-weight-800 text-dark mb-2">{{ __('Plan de Suscripción') }}</h2>
                                <p class="text-muted text-16 mb-5">{{ __('Seleccione el plan que mejor se adapte a su negocio para activar todas las funcionalidades.') }}</p>
                            </div>

                            <div class="row m-0">
                                @forelse($planes as $plan)
                                    @php
                                        $opciones = is_array($plan->planes_pago) ? $plan->planes_pago : (json_decode($plan->planes_pago, true) ?: []);
                                        $beneficios = is_array($plan->beneficios) ? $plan->beneficios : (json_decode($plan->beneficios, true) ?: []);
                                        
                                        $isActive = $planGuardado == $plan->id;
                                        $indiceMostrar = $isActive ? $opcionIndiceGuardado : 0;
                                        $opcionMostrar = $opciones[$indiceMostrar] ?? ($opciones[0] ?? null);
                                    @endphp

                                    <div class="col-12 col-lg-6 col-xl-4 mb-5 px-3">
                                        <div class="pricing-card h-100 {{ $isActive ? 'is-active' : '' }} {{ $plan->destacado ? 'is-featured' : '' }}" 
                                             data-id="{{ $plan->id }}">
                                            
                                            @if($plan->destacado)
                                                <span class="badge-featured">{{ __('Más Popular') }}</span>
                                            @endif

                                            <div class="p-4 pt-5 d-flex flex-column h-100">
                                                <h3 class="text-center font-weight-900 text-dark mb-4">{{ $plan->nombre }}</h3>

                                                {{-- Selector de Lapsos --}}
                                                <div class="pricing-selector-group">
                                                    @foreach($opciones as $index => $opcion)
                                                        <button type="button" 
                                                                class="pricing-btn-lapso {{ $index == $indiceMostrar ? 'active' : '' }}" 
                                                                data-index="{{ $index }}"
                                                                data-price="{{ reda_money_format($opcion['moneda'] == 'dólar' ? '$' : 'Bs', $opcion['precio']) }}"
                                                                data-lapso="{{ __($opcion['lapso']) }}">
                                                            {{ __($opcion['lapso']) }}
                                                        </button>
                                                    @endforeach
                                                </div>

                                                {{-- Precio Dinámico --}}
                                                <div class="pricing-price-box text-center">
                                                    @if($opcionMostrar)
                                                        <span class="pricing-amount">
                                                            {{ reda_money_format($opcionMostrar['moneda'] == 'dólar' ? '$' : 'Bs', $opcionMostrar['precio']) }}
                                                        </span>
                                                        <span class="pricing-period">/ {{ __($opcionMostrar['lapso']) }}</span>
                                                    @else
                                                        <span class="pricing-amount">N/A</span>
                                                    @endif
                                                </div>

                                                {{-- Beneficios --}}
                                                <div class="flex-grow-1 border-top pt-4 mt-2">
                                                    <ul class="pricing-features">
                                                        @foreach($beneficios as $beneficio)
                                                            <li class="pricing-feature-item">
                                                                <i class="fa fa-check-circle"></i>
                                                                <span>{{ $beneficio }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                <div class="mt-4">
                                                    <button type="button" class="pricing-btn-select">
                                                        {{ $isActive ? __('Plan Seleccionado') : __('Elegir Plan') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center p-5 bg-white rounded shadow-sm">
                                        <i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                        <p class="text-muted">{{ __('No hay planes configurados en este momento.') }}</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="col-md-12 p-0 mt-4 mb-5 pt-4 text-center text-md-left">
                                <button type="submit" class="btn vbtn-success text-18 font-weight-800 pl-5 pr-5 pt-3 pb-3 shadow-lg rounded-pill" id="btn_next">
                                    <i class="spinner fa fa-spinner fa-spin d-none"></i>
                                    <span id="btn_next-text">{{ __('Confirmar y Finalizar') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Notificaciones --}}
@include('reda-alojamiento::general.modal_notificaciones')

@endsection

@section('validation_script')
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('public/js/reda/vistas/experiencia/formularioDePasosExperiencias.min.js?v=' . time()) }}"></script>
    
    @if(session('error_destacado'))
        <script>
            $(function() {
                $('#notificacion-icono').html('<i class="fa fa-exclamation-circle fa-4x text-danger"></i>');
                $('#notificacion-titulo').text("{{ __('¡Requisitos insuficientes!') }}");
                $('#notificacion-mensaje').text("{{ session('error_destacado') }}");
                $('#modal-notificacion').modal('show');
            });
        </script>
    @endif
@endsection
