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

@section('validation_script')
    <script>
        window.RedaAlojamientoJson = @json(__('reda-alojamiento::messages'));
    </script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/disputa/disputas/indexDisputas.min.js?v=' . time()) }}"></script>
@endsection
