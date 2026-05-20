@extends('admin.template')

@section('main')
<div class="content-wrapper">
	<section class="content-header">
		<h1>Opciones de Tipos de Negocios</h1>
		@include('admin.common.breadcrumb')
	</section>
	
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Listado de Categorías Configuradas</h3>
                        <div class="box-tools pull-right">
                            <a href="#" class="btn btn-sm btn-success btn-flat="><i class="fa fa-plus"></i> Agregar Nueva</a>
                        </div>
					</div>
					<div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover f-14">
                                <thead>
                                    <tr style="background-color: #f4f4f4;">
                                        <th style="width: 30%">Clave (Key)</th>
                                        <th style="width: 50%">Descripción / Opción</th>
                                        <th style="width: 20%; text-align: center;">Acciones</th>
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
                                                <i class="fa fa-info-circle"></i> No se encontraron opciones de tipos de negocios registradas.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</section>
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