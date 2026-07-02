@extends('admin.template')

@section('main')
<div id="configuracion_planes_container"></div>
<div class="content-wrapper">
	<section class="content-header">
		<h1>{{ __('Configurar planes') }}</h1>
		@include('admin.common.breadcrumb')
	</section>

	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_opciones_generales" data-toggle="tab">{{ __('Opciones generales') }}</a></li>
                        <li><a href="#tab_planes" id="btn-tab-planes" data-toggle="tab">{{ __('Planes') }}</a></li>
                    </ul>
                    <div class="tab-content">
                        {{-- Pestaña 1: Opciones Generales --}}
                        <div class="tab-pane active" id="tab_opciones_generales">
                            <div class="row">
                                <div class="col-xs-12 col-md-11 col-lg-10">
                                    <form id="form-configuracion-planes" method="POST" action="{{ route('reda.admin.negocios.configuracion_planes.store') }}">
                                        @csrf
                                        <div class="box-body">
                                            
                                            {{-- Grupo: Requisitos para adquirir planes destacados --}}
                                            <div class="box box-solid planes-negocio-box-custom">
                                                <div class="box-header with-border planes-negocio-box-header-custom">
                                                    <h3 class="box-title planes-negocio-box-title-custom">
                                                        {{ __('Requisitos para adquirir planes destacados') }}
                                                    </h3>
                                                </div>
                                                <div class="box-body">
                                                    <div class="row">
                                                        {{-- Cantidad de tiempo --}}
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="cantidad">{{ __('Cantidad de tiempo') }} <span class="text-danger">*</span></label>
                                                                <input type="number" name="cantidad" id="cantidad" class="form-control f-14" step="0.1" min="0" value="{{ $configuracion['cantidad'] ?? 0 }}" required>
                                                            </div>
                                                        </div>
                                                        {{-- Unidad de tiempo (Sin etiqueta visible, pero ocupando el mismo espacio) --}}
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="unidad_tiempo" class="planes-negocio-label-block">&nbsp;</label>
                                                                <select name="unidad_tiempo" id="unidad_tiempo" class="form-control f-14" required>
                                                                    <option value="Año(s)" {{ ($configuracion['unidad_tiempo'] ?? '') == 'Año(s)' ? 'selected' : '' }}>{{ __('Año(s)') }}</option>
                                                                    <option value="Mes(es)" {{ ($configuracion['unidad_tiempo'] ?? '') == 'Mes(es)' ? 'selected' : '' }}>{{ __('Mes(es)') }}</option>
                                                                    <option value="Día(s)" {{ ($configuracion['unidad_tiempo'] ?? '') == 'Día(s)' ? 'selected' : '' }}>{{ __('Día(s)') }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- Promedio de calificaciones --}}
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="promedio_calificaciones">{{ __('Promedio de calificaciones') }} <span class="text-danger">*</span></label>
                                                                <input type="number" name="promedio_calificaciones" id="promedio_calificaciones" class="form-control f-14" step="0.01" min="0" value="{{ $promedio_calificaciones ?? 0 }}" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Fin Grupo: Planes destacados --}}

                                        </div>
                                        <div class="box-footer">
                                            <button type="submit" id="btn-save-config-planes" class="btn btn-primary btn-flat pull-right">
                                                <span class="btn-text">{{ __('Guardar') }}</span>
                                                <i class="fa fa-spinner fa-spin d-none"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Pestaña 2: Planes --}}
                        <div class="tab-pane" id="tab_planes" style="display: none;">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="box box-solid planes-negocio-box-custom">
                                        <div class="box-header with-border d-flex align-items-center justify-content-between flex-wrap gap-2 planes-negocio-box-header-custom">
                                            <h3 class="box-title planes-negocio-box-title-custom">{{ __('Configuración de Planes') }}</h3>
                                            <div class="box-tools position-static">
                                                <button type="button" id="btn-add-plan" class="btn btn-sm btn-success btn-flat">
                                                    <i class="fa fa-plus"></i> {{ __('Agregar nuevo plan') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="box-body" id="contenedor-tabla-planes">
                                            <p class="text-muted text-center p-5">
                                                <i class="fa fa-spinner fa-spin"></i> {{ __('Cargando planes...') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
			</div>
		</div>
	</section>
</div>

<!-- Modal para Ver Plan (No modificable) -->
<div class="modal fade" id="modal-ver-plan" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 position-relative">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <h4 class="modal-title" id="modal-title-ver-plan">{{ __('Detalles del Plan de Negocio') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body planes-negocio-modal-body-scroll">
                <div class="row">
                    <div class="col-md-6">
                        <span class="planes-negocio-label-detalle">{{ __('Nombre del plan') }}</span>
                        <div id="ver_nombre_plan" class="planes-negocio-valor-detalle mb-3"></div>
                    </div>
                    <div class="col-md-3">
                        <span class="planes-negocio-label-detalle">{{ __('Orden') }}</span>
                        <div id="ver_orden_plan" class="planes-negocio-valor-detalle mb-3"></div>
                    </div>
                    <div class="col-md-3">
                        <span class="planes-negocio-label-detalle">{{ __('Estatus') }}</span>
                        <div id="ver_estatus_plan" class="mb-3"></div>
                    </div>
                </div>

                <div class="box box-solid planes-negocio-well-ver">
                    <div class="box-header with-border">
                        <h5 class="box-title fw-700 f-14">{{ __('Planes de pago') }}</h5>
                    </div>
                    <div class="box-body" id="ver_contenedor_planes_pago">
                        {{-- Contenido dinámico --}}
                    </div>
                </div>

                <div class="box box-solid mt-3 planes-negocio-well-ver">
                    <div class="box-header with-border">
                        <h5 class="box-title fw-700 f-14">{{ __('Beneficios incluidos') }}</h5>
                    </div>
                    <div class="box-body">
                        <ul id="ver_contenedor_beneficios" class="list-group list-group-flush bg-transparent">
                            {{-- Contenido dinámico --}}
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-bs-dismiss="modal">{{ __('Cerrar') }}</button>
            </div>
            
            {{-- Botón Flotante para Editar --}}
            <button type="button" id="btn-flotante-edit-plan" class="planes-negocio-btn-flotante-editar" title="{{ __('Editar este plan') }}">
                <i class="fa fa-edit"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal para Agregar/Editar Plan -->
<div class="modal fade" id="modal-plan" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-plan" method="POST" action="">
                @csrf
                <input type="hidden" name="id" id="plan_id">
                <div class="modal-header d-flex align-items-center justify-content-between">
                    <h4 class="modal-title" id="modal-title-plan">{{ __('Agregar nuevo plan') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body planes-negocio-modal-body-scroll">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_plan">{{ __('Nombre del plan') }} <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre_plan" class="form-control f-14" placeholder="ej: Plan Oro" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="orden_plan">{{ __('Orden') }} <span class="text-danger">*</span></label>
                                <input type="number" name="orden" id="orden_plan" class="form-control f-14" min="0" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group planes-negocio-mt-25">
                                <label>
                                    <input type="checkbox" name="destacado" id="destacado_plan" value="1"> {{ __('Destacado') }}
                                </label>
                                &nbsp;&nbsp;
                                <label>
                                    <input type="checkbox" name="estatus" id="estatus_plan" value="1" checked> {{ __('Activo') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Sección: Planes de pago --}}
                    <div class="box box-solid mt-4 planes-negocio-box-custom">
                        <div class="box-header with-border planes-negocio-box-header-custom">
                            <h3 class="box-title planes-negocio-box-title-custom">
                                {{ __('Planes de pago') }}
                            </h3>
                        </div>
                        <div class="box-body">
                            <div id="contenedor-planes-pago">
                                {{-- Aquí se cargarán dinámicamente las filas de planes de pago --}}
                            </div>
                            <button type="button" id="btn-agregar-plan-pago" class="btn btn-sm btn-success btn-flat mt-2">
                                <i class="fa fa-plus"></i> {{ __('Agregar nuevo plan de pago') }}
                            </button>
                        </div>
                    </div>

                    {{-- Sección: Beneficios del plan --}}
                    <div class="box box-solid mt-4 planes-negocio-box-custom">
                        <div class="box-header with-border planes-negocio-box-header-custom">
                            <h3 class="box-title planes-negocio-box-title-custom">
                                {{ __('Beneficios del Plan') }}
                            </h3>
                        </div>
                        <div class="box-body">
                            <div id="contenedor-beneficios">
                                {{-- Aquí se cargarán dinámicamente los inputs de beneficios --}}
                            </div>
                            <button type="button" id="btn-agregar-beneficio" class="btn btn-sm btn-info btn-flat mt-2">
                                <i class="fa fa-plus"></i> {{ __('Agregar nuevo beneficio') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-save-plan" class="btn btn-primary btn-flat">
                        <span class="btn-text">{{ __('Guardar') }}</span>
                        <i class="fa fa-spinner fa-spin d-none"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('reda-alojamiento::admin.general.modal_confirmacion')
@stop

@section('validate_script')
    <script>
        window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));
        window.RedaAlojamientoJson = @json(__('reda-alojamiento::es'));
    </script>
    <script>
        window.RedaRutas = {
            store_config_planes: "{{ route('reda.admin.negocios.configuracion_planes.store') }}",
            index_planes: "{{ route('reda.admin.negocios.configuracion_planes.index_planes') }}",
            get_plan: "{{ url('admin/reda/negocios/configuracion-planes/get') }}",
            store_plan: "{{ route('reda.admin.negocios.configuracion_planes.store_plan') }}",
            update_plan: "{{ route('reda.admin.negocios.configuracion_planes.update_plan') }}",
            destroy_plan: "{{ url('admin/reda/negocios/configuracion-planes/destroy-plan') }}"
        };
    </script>
    <script type="text/javascript" src="{{ asset('public/js/reda/admin/vistas/experiencia/configuracionPlanes.min.js') }}?v={{ time() }}"></script>
@endsection
