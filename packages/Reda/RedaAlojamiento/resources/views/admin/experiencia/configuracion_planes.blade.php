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
                                <div class="col-xs-12 col-md-8 col-lg-6">
                                    <form id="form-configuracion-planes" method="POST" action="{{ route('reda.admin.negocios.configuracion_planes.store') }}">
                                        @csrf
                                        <div class="box-body">
                                            {{-- Opción 1: Antigüedad --}}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="cantidad">{{ __('Cantidad') }} <span class="text-danger">*</span></label>
                                                        <input type="number" name="cantidad" id="cantidad" class="form-control f-14" step="0.1" min="0" value="{{ $configuracion['cantidad'] ?? 0 }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="unidad_tiempo">{{ __('Unidad de tiempo') }} <span class="text-danger">*</span></label>
                                                        <select name="unidad_tiempo" id="unidad_tiempo" class="form-control f-14" required>
                                                            <option value="Año(s)" {{ ($configuracion['unidad_tiempo'] ?? '') == 'Año(s)' ? 'selected' : '' }}>{{ __('Año(s)') }}</option>
                                                            <option value="Mes(es)" {{ ($configuracion['unidad_tiempo'] ?? '') == 'Mes(es)' ? 'selected' : '' }}>{{ __('Mes(es)') }}</option>
                                                            <option value="Día(s)" {{ ($configuracion['unidad_tiempo'] ?? '') == 'Día(s)' ? 'selected' : '' }}>{{ __('Día(s)') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            {{-- Opción 2: Promedio de calificaciones --}}
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="promedio_calificaciones">{{ __('Promedio de calificaciones para planes destacados') }} <span class="text-danger">*</span></label>
                                                        <input type="number" name="promedio_calificaciones" id="promedio_calificaciones" class="form-control f-14" step="0.01" min="0" max="5" value="{{ $promedio_calificaciones ?? 0 }}" required>
                                                    </div>
                                                </div>
                                            </div>
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
                        <div class="tab-pane" id="tab_planes">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="box-header with-border d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <h3 class="box-title">{{ __('Configuración de Planes') }}</h3>
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
	</section>
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
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_plan">{{ __('Nombre del plan') }} <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre_plan" class="form-control f-14" placeholder="ej: Plan Oro" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="precio_plan">{{ __('Precio') }} <span class="text-danger">*</span></label>
                                <input type="number" name="precio" id="precio_plan" class="form-control f-14" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="moneda_plan">{{ __('Moneda') }} <span class="text-danger">*</span></label>
                                <select name="moneda" id="moneda_plan" class="form-control f-14" required>
                                    <option value="dólar">Dólar ($)</option>
                                    <option value="Bs">Bolívares (Bs)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lapso_pago_plan">{{ __('Lapso de pago') }} <span class="text-danger">*</span></label>
                                <select name="lapso_pago" id="lapso_pago_plan" class="form-control f-14" required>
                                    <option value="quincenal">{{ __('Quincenal') }}</option>
                                    <option value="mensual">{{ __('Mensual') }}</option>
                                    <option value="anual">{{ __('Anual') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="orden_plan">{{ __('Orden') }}</label>
                                <input type="number" name="orden" id="orden_plan" class="form-control f-14" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group" style="margin-top: 25px;">
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
                    <div class="form-group">
                        <label for="beneficios_plan">{{ __('Beneficios') }} (JSON)</label>
                        <textarea name="beneficios" id="beneficios_plan" class="form-control f-14" rows="3" placeholder='["Beneficio 1", "Beneficio 2"]'></textarea>
                        <small class="text-muted">Formato: ["Beneficio 1", "Beneficio 2"]</small>
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
@stop

@section('validate_script')
    <script>window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));</script>
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
