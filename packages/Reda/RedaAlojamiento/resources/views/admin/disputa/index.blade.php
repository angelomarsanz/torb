@extends('admin.template')

@section('main')
<div class="content-wrapper">
	<section class="content-header">
		<h1>{{ __('Mediaciones') }}</h1>
		@include('admin.common.breadcrumb')
	</section>

	<section class="content">
        {{-- Contenedor principal de la vista de Disputas (Dashboard style) --}}
        <div id="index_disputas_admin" class="mt-2 reda-admin-disputas">
            <div class="row">
                {{-- Columna Central: Pestañas y Listado --}}
                <div class="col-md-8">
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

                    {{-- Bloque: Información de la Reservación --}}
                    <div class="card border rounded-3 p-3 shadow-sm bg-white mb-4 d-none" id="disputas-reservacion-sidebar">
                        <h6 class="fw-600 mb-3 text-14 border-bottom pb-2">{{ __('Reservación') }}</h6>
                        <div id="disputas-reservacion-content">
                            {{-- Se inyecta vía JS --}}
                        </div>
                    </div>

                    {{-- Bloque 3: Detalle Colapsable --}}
                    <div class="card border rounded-3 p-3 shadow-sm bg-white mb-4">
                        <h6 class="fw-600 mb-3 text-14 border-bottom pb-2">{{ __('Detalle') }}</h6>
                        <div id="disputas-info-extra-content">
                            <p class="text-14 text-muted">{{ __('Aquí aparecerá información relevante sobre el estado general de las mediaciones.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</section>
</div>
@stop

@section('validate_script')
    <script>
        window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));
        window.RedaAlojamientoJson = @json(__('reda-alojamiento::es'));
        window.RedaTrans = @json(__('reda-alojamiento::messages'));
        @php
            $adminId = Auth::guard('admin')->id();
            $isFullAdmin = \DB::table('role_admin')
                ->where('admin_id', $adminId)
                ->where('role_id', 1)
                ->exists();
        @endphp
        window.RedaAdminAccess = {
            isFullAdmin: @json($isFullAdmin)
        };
    </script>
    <script type="text/javascript" src="{{ asset('public/js/reda/admin/vistas/disputa/indexDisputas.min.js') }}?v={{ time() }}"></script>
@endsection
