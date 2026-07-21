@extends('template')

@section('main')
<div class="margin-top-85">
    <div class="row m-0">
        {{-- Sidebar original del proyecto --}}
        @include('users.sidebar')

        <div class="col-lg-10 p-0 mb-5 min-height">
            <div class="main-panel">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 p-0 mb-3">
                            <div class="list-bacground mt-4 rounded-3 p-4 border">
                                <span class="text-18 pt-4 pb-4 font-weight-700">{{ __('Mediaciones') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Contenedor principal de la vista de Disputas (Dashboard style) --}}
                    <div id="indexDisputas" class="mt-2">
                        <div class="row">
                            {{-- Columna Central: Pestañas y Listado --}}
                            <div class="col-md-9 p-0">
                                <div id="disputas-tabs-header" class="mb-4">
                                    {{-- Se inyecta vía JS --}}
                                </div>
                                <div id="disputas-list-container">
                                    {{-- Se inyecta vía JS --}}
                                </div>

                                {{-- Contenedor para la paginación --}}
                                <div id="disputas-pagination-container" class="mt-4 mb-5 d-flex justify-content-center">
                                    {{-- Se inyecta vía JS --}}
                                </div>
                            </div>

                            {{-- Columna Lateral: Información Extra --}}
                            <div class="col-md-3" id="disputas-info-lateral">
                                <div class="card border rounded-3 p-3 shadow-sm bg-light mb-4">
                                    <h6 class="font-weight-700 mb-3 text-16">{{ __('Estado del Trámite') }}</h6>
                                    
                                    <div class="position-relative px-4">
                                        <div id="timeline-prev" class="timeline-nav-btn timeline-nav-left">
                                            <i class="fas fa-chevron-left"></i>
                                        </div>
                                        
                                        <div id="reda-timeline-container" class="reda-timeline-carousel">
                                            {{-- Se inyecta vía JS --}}
                                            <p class="text-center text-muted small w-100">{{ __('Selecciona una mediación para ver su progreso.') }}</p>
                                        </div>

                                        <div id="timeline-next" class="timeline-nav-btn timeline-nav-right">
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border rounded-3 p-3 shadow-sm bg-white">
                                    <h6 class="font-weight-700 mb-3 text-16">{{ __('Detalle') }}</h6>
                                    <div id="disputas-info-extra-content">
                                        <p class="text-14 text-muted">{{ __('Aquí aparecerá información relevante sobre el estado general de tus mediaciones.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Custom Timeline Styles */
    .reda-timeline-carousel {
        display: flex;
        overflow-x: auto;
        padding: 20px 0;
        scroll-behavior: smooth;
        -ms-overflow-style: none;
        scrollbar-width: none;
        min-height: 120px;
    }
    .reda-timeline-carousel::-webkit-scrollbar {
        display: none;
    }
    .timeline-item {
        min-width: 120px;
        text-align: center;
        position: relative;
        padding: 0 5px;
        flex-shrink: 0;
    }
    .timeline-item::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #e0e0e0;
        z-index: 1;
    }
    .timeline-item:last-child::after {
        display: none;
    }
    .timeline-icon {
        width: 30px;
        height: 30px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        position: relative;
        z-index: 2;
        transition: all 0.3s ease;
        font-size: 12px;
        color: #adb5bd;
    }
    .timeline-item.active .timeline-icon {
        border-color: #28a745;
        background: #28a745;
        color: #fff;
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.4);
    }
    .timeline-item.completed .timeline-icon {
        border-color: #28a745;
        background: #e8f5e9;
        color: #28a745;
    }
    .timeline-item.completed::after {
        background: #28a745;
    }
    .timeline-text {
        font-size: 11px;
        line-height: 1.2;
        font-weight: 600;
        color: #6c757d;
        display: block;
        word-wrap: break-word;
    }
    .timeline-item.active .timeline-text {
        color: #28a745;
        font-weight: 700;
    }
    .timeline-nav-btn {
        position: absolute;
        top: 92px;
        width: 24px;
        height: 24px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        font-size: 10px;
        color: #666;
    }
    .timeline-nav-left { left: 5px; }
    .timeline-nav-right { right: 5px; }
    .timeline-nav-btn:hover {
        background: #f8f9fa;
        color: #333;
    }

    /* New Selection and Mobile Detail Styles */
    .card-mediacion.active-mediacion {
        border: 2px solid #28a745 !important;
        box-shadow: 0 0 15px rgba(40, 167, 69, 0.2);
        z-index: 5;
    }

    .personas-relacionadas-block {
        background: rgba(255, 255, 255, 0.5);
        padding: 10px;
        border-radius: 8px;
    }

    .avatar-mini img {
        transition: transform 0.2s ease;
    }

    .avatar-mini img:hover {
        transform: scale(1.1);
    }

    .leading-tight { line-height: 1.2; }
    .letter-spacing-1 { letter-spacing: 1px; }

    /* "More/Less" Styles */
    .reda-mediation-desc-clamped {
        display: -webkit-box;
        -webkit-line-clamp: 1; /* Initially 1 line as requested */
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .reda-mediation-desc-clamped.expanded {
        -webkit-line-clamp: unset;
    }

    .detalles-extra-collapsible {
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .mobile-detail-wrapper {
        position: relative;
        z-index: 4;
        margin-top: -2px;
    }

    .mobile-detail-toggle {
        cursor: pointer;
        transition: all 0.2s ease;
        border-top: none !important;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #f8f9fa;
    }

    .mobile-detail-toggle:hover {
        background-color: #e9ecef !important;
    }

    .mobile-detail-content {
        border-top: none !important;
        animation: slideDown 0.3s ease-out;
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 767.98px) {
        #disputas-info-lateral {
            display: none;
        }
        .card-mediacion.active-mediacion {
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            margin-bottom: 0 !important;
        }
        .container-mediacion {
            margin-bottom: 25px !important;
        }
    }
</style>
@endpush

@section('validation_script')
    <script>
        window.RedaAlojamientoJson = @json(__('reda-alojamiento::messages'));
    </script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/disputa/disputas/indexDisputas.min.js?v=' . time()) }}"></script>
@endsection
