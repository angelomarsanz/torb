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
                                    <h6 class="font-weight-500 mb-3 text-14 border-bottom pb-2">{{ __('Información adicional') }}</h6>
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

{{-- Modal Media Viewer (Imágenes y PDFs) --}}
<div class="modal fade reda-media-viewer" id="modal-media-viewer-reda" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content bg-dark text-white border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 d-flex align-items-center justify-content-between">
                <h5 class="modal-title text-white text-16 font-weight-700 text-truncate mr-3" id="media-viewer-title"></h5>
                <button type="button" class="close text-white opacity-100 m-0 p-0" data-dismiss="modal" aria-label="Close" style="text-shadow: none; outline: none;">
                    <span aria-hidden="true" class="text-30">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center bg-black-viewer" style="min-height: 80vh;">
                
                <!-- Zoom Controls (Solo para escritorio) -->
                <div class="zoom-controls position-absolute d-none d-md-flex" style="bottom: 25px; right: 25px; z-index: 1060; gap: 10px;">
                    <button class="btn btn-dark border-secondary rounded-circle btn-zoom-out" title="{{ __('Reducir') }}"><i class="fas fa-search-minus"></i></button>
                    <button class="btn btn-dark border-secondary rounded-circle btn-zoom-reset" title="{{ __('Restablecer') }}"><i class="fas fa-undo"></i></button>
                    <button class="btn btn-dark border-secondary rounded-circle btn-zoom-in" title="{{ __('Ampliar') }}"><i class="fas fa-search-plus"></i></button>
                </div>

                <!-- Media Content -->
                <div id="media-content-container" class="w-100 h-100 d-flex align-items-center justify-content-center overflow-auto" style="max-height: 80vh; -webkit-overflow-scrolling: touch;">
                    {{-- Inyectado vía JS --}}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-black-viewer { background-color: #0b0b0b; }
    #media-content-container img { transition: width 0.2s ease; cursor: default; }
    .zoom-controls button { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.6); }
    .zoom-controls button:hover { background: rgba(0,0,0,0.9); border-color: #fff; }
    
    /* Scrollbars personalizadas para el visor */
    #media-content-container::-webkit-scrollbar { width: 10px; height: 10px; }
    #media-content-container::-webkit-scrollbar-track { background: #1a1a1a; }
    #media-content-container::-webkit-scrollbar-thumb { background: #444; border-radius: 5px; }
    #media-content-container::-webkit-scrollbar-thumb:hover { background: #666; }

    @media (max-width: 767px) {
        #media-content-container { max-height: 85vh; }
        .zoom-controls { display: none !important; }
    }
</style>
@endsection

@section('validation_script')
    <script>
        window.RedaAlojamientoJson = @json(__('reda-alojamiento::messages'));
    </script>
    <script type="text/javascript" src="{{ asset('public/js/reda/vistas/disputa/disputas/indexDisputas.min.js?v=' . time()) }}"></script>
@endsection
