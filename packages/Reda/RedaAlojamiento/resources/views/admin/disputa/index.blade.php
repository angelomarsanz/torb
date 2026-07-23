@extends('admin.template')

@section('main')
<div id="index_disputas_admin"></div>
<div class="content-wrapper">
	<section class="content-header">
		<h1>{{ __('Mediaciones') }}</h1>
		@include('admin.common.breadcrumb')
	</section>

	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				<div class="box">
					<div class="box-header with-border d-flex align-items-center justify-content-between flex-wrap gap-2">
						<h3 class="box-title">{{ __('Listado de mediaciones') }}</h3>
					</div>
					<div class="box-body">
                        <!-- Vista de Escritorio: Tabla tradicional -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-bordered table-striped table-hover f-14">
                                <thead>
                                    <tr style="background-color: #f4f4f4;">
                                        <th style="width: 10%">{{ __('ID') }}</th>
                                        <th style="width: 25%">{{ __('Usuario') }}</th>
                                        <th style="width: 25%">{{ __('Motivo') }}</th>
                                        <th style="width: 15%">{{ __('Estatus') }}</th>
                                        <th style="width: 15%">{{ __('Fecha') }}</th>
                                        <th style="width: 10%; text-align: center;">{{ __('Acciones') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="contenedor-disputas-desktop">
                                    {{-- El contenido se cargará dinámicamente o vía Blade según se prefiera más adelante --}}
                                    <tr>
                                        <td colspan="6" class="text-center text-muted" style="padding: 20px;">
                                            <i class="fa fa-info-circle"></i> {{ __('No se encontraron mediaciones.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Vista Móvil: Lista de Tarjetas (Cards) -->
                        <div class="d-md-none" id="contenedor-disputas-mobile">
                            <div class="text-center text-muted p-5 bg-light rounded">
                                <i class="fa fa-info-circle fa-3x mb-3" style="opacity: 0.3;"></i>
                                <p>{{ __('No se encontraron mediaciones.') }}</p>
                            </div>
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
    <script type="text/javascript" src="{{ asset('public/js/reda/admin/vistas/disputa/indexDisputas.min.js') }}?v={{ time() }}"></script>
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection
