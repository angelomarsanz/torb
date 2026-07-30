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
                                <span class="text-18 pt-4 pb-4 font-weight-500">{{ __('Mediaciones') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Contenedor principal de la vista de Disputas (Dashboard style) --}}
                    <div id="indexDisputas" class="mt-2">
                        <div class="row">
                            {{-- Columna Central: Pestañas y Listado --}}
                            <div class="col-md-8 p-0">
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
                            <div class="col-md-4 d-none d-md-block" id="disputas-info-lateral">
                                {{-- Bloque 1: Información Principal (Estatus, ID, Motivo) - SIN MARCO --}}
                                <div class="mb-4 d-none" id="disputas-cabecera-lateral">
                                    <div id="disputas-header-content" class="px-2">
                                        {{-- Inyectado vía JS: Estatus, ID y Motivo --}}
                                    </div>
                                </div>

                                {{-- Bloque 2: Estado del Trámite (Cronograma) --}}
                                <div class="card border rounded-3 p-3 shadow-sm bg-light mb-4">
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

                                {{-- Bloque NUEVO: Información de la Reservación --}}
                                <div class="card border rounded-3 p-3 shadow-sm bg-white mb-4 d-none" id="disputas-reservacion-sidebar">
                                    <h6 class="font-weight-500 mb-3 text-14 border-bottom pb-2">{{ __('Reservación') }}</h6>
                                    <div id="disputas-reservacion-content">
                                        {{-- Se inyecta vía JS --}}
                                    </div>
                                </div>

                                {{-- Bloque 3: Detalle Colapsable --}}
                                <div class="card border rounded-3 p-3 shadow-sm bg-white mb-4">
                                    <h6 class="font-weight-500 mb-3 text-14 border-bottom pb-2">{{ __('Detalle') }}</h6>
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

{{-- Modal de Mensajes de Mediación --}}
<div class="modal fade reda-modal-messages" id="modal-mensajes-mediacion-reda" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-700 text-18" id="modal-mensajes-titulo">{{ __('Mensajes de la Mediación') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2">
                <div class="container-inbox-reda">
                    <div class="message-wrap-reda" id="reda-mensajes-container">
                        {{-- Mensajes inyectados vía JS --}}
                        <div class="text-center py-5">
                            <div class="spinner-border text-success" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="message-footer-reda mt-3 border-top pt-3">
                        <div class="form-row align-items-center">
                            <div class="col">
                                <input type="text" class="form-control cht_msg_reda" id="input-mensaje-reda" placeholder="{{ __('Escribe un mensaje...') }}" />
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-success send-btn-reda" type="button" id="btn-enviar-mensaje-reda" title="{{ __('Enviar mensaje') }}">
                                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button>
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
