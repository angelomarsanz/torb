@extends('admin.template')

@section('main')
<div id="opciones_tipos_de_negocios"></div>
<div class="content-wrapper">
	<section class="content-header">
		<h1>{{ __('reda-alojamiento::messages.general.opciones_de_tipos_de_negocios') }}</h1>
		@include('admin.common.breadcrumb')
	</section>

	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header with-border d-flex align-items-center justify-content-between flex-wrap gap-2">
						<h3 class="box-title">{{ __('reda-alojamiento::messages.general.listado_de_categorias_configuradas') }}</h3>
                        <div class="box-tools position-static">
                            <button type="button" id="btn-add-category" class="btn btn-sm btn-success btn-flat"><i class="fa fa-plus"></i> {{ __('reda-alojamiento::messages.general.agregar_nueva') }}</button>
                        </div>
					</div>
					<div class="box-body">
                        <!-- Vista de Escritorio: Tabla tradicional -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-bordered table-striped table-hover f-14">
                                <thead>
                                    <tr style="background-color: #f4f4f4;">
                                        <th style="width: 30%">{{ __('reda-alojamiento::messages.general.clave_key') }}</th>
                                        <th style="width: 50%">{{ __('reda-alojamiento::messages.general.descripcion_opcion') }}</th>
                                        <th style="width: 20%; text-align: center;">{{ __('reda-alojamiento::messages.general.acciones') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!empty($categorias) && count($categorias) > 0)
                                        @foreach($categorias as $clave => $descripcion)
                                            <tr>
                                                <td>
                                                    <strong class="text-blue">{{ $clave }}</strong>
                                                </td>
                                                <td>
                                                    {{ $descripcion }}
                                                </td>
                                                <td style="text-align: center;">
                                                    <div class="btn-group">
                                                        <a href="#" class="btn btn-xs btn-primary btn-flat" data-toggle="tooltip" title="Editar">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-xs btn-danger btn-flat" data-toggle="tooltip" title="Eliminar">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="3" class="text-center text-muted" style="padding: 20px;">
                                                <i class="fa fa-info-circle"></i> {{ __('reda-alojamiento::messages.general.no_se_encontraron_opciones_de_tipos_de_negocios') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- Vista Móvil: Lista de Tarjetas (Cards) -->
                        <div class="d-md-none">
                            @if(!empty($categorias) && count($categorias) > 0)
                                @foreach($categorias as $clave => $descripcion)
                                    <div class="card mb-3 shadow-sm border-light" style="border-radius: 10px; border: 1px solid #eee;">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div style="flex: 1;">
                                                    <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">{{ __('reda-alojamiento::messages.general.clave_key') }}</small>
                                                    <strong class="text-blue" style="font-size: 15px;">{{ $clave }}</strong>
                                                </div>
                                                <div class="btn-group">
                                                    <a href="#" class="btn btn-sm btn-default btn-flat border" data-toggle="tooltip" title="Editar">
                                                        <i class="fa fa-edit text-primary"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-default btn-flat border" data-toggle="tooltip" title="Eliminar">
                                                        <i class="fa fa-trash text-danger"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-2 pt-2 border-top">
                                                <small class="text-muted d-block" style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">{{ __('reda-alojamiento::messages.general.descripcion_opcion') }}</small>
                                                <p class="mb-0 text-dark f-14">{{ $descripcion }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted p-5 bg-light rounded">
                                    <i class="fa fa-info-circle fa-3x mb-3" style="opacity: 0.3;"></i>
                                    <p>{{ __('reda-alojamiento::messages.general.no_se_encontraron_opciones_de_tipos_de_negocios') }}</p>
                                </div>
                            @endif
                        </div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<!-- Modal para Agregar Categoría -->
<div class="modal fade" id="modal-add-category" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-add-category" method="POST" action="{{ route('reda.admin.opciones_tipos_de_negocios.store') }}">
                @csrf
                <div class="modal-header d-flex align-items-center justify-content-between">
                    <h4 class="modal-title">{{ __('reda-alojamiento::messages.general.agregar_nueva_categoria') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="clave">{{ __('reda-alojamiento::messages.general.clave_key') }} <span class="text-danger">*</span></label>
                        <input type="text" name="clave" id="clave" class="form-control" placeholder="ej: restaurante" required>
                        <small class="text-muted">{{ __('reda-alojamiento::messages.general.identificador_unico_sin_espacios_ni_caracteres_especiales') }}</small>
                    </div>
                    <div class="form-group">
                        <label for="nombre">{{ __('reda-alojamiento::messages.general.nombre_descripcion') }} <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="ej: Restaurante / Gastronomía" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn-save-category" class="btn btn-primary btn-flat">
                        <span class="btn-text">{{ __('reda-alojamiento::messages.general.guardar') }}</span>
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
    <script type="text/javascript" src="{{ asset('public/js/jquery.validate.min.js') }}"></script>
    {{-- Corregí la comilla de cierre perdida que tenías en tu asset anterior (js}}?v=) --}}
    <script type="text/javascript" src="{{ asset('public/js/reda/admin/vistas/experiencia/opcionesTipoDeNegocios.min.js') }}?v={{ time() }}"></script>
    <script>
        $(document).ready(function() {
            // Inicializa los tooltips de Bootstrap para los botones editar/eliminar
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
