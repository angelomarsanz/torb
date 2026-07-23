@extends('admin.template')

@section('main')
<div id="index_disputas_admin"></div>
<div class="content-wrapper">
	<section class="content-header">
		<h1>{{ __('Mediaciones') }}</h1>
		@include('admin.common.breadcrumb')
	</section>

	<section class="content">

	</section>
</div>
@stop

@section('validate_script')
    <script>
        window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));
        window.RedaAlojamientoJson = @json(__('reda-alojamiento::es'));
    </script>
    <script type="text/javascript" src="{{ asset('public/js/reda/admin/vistas/disputa/indexDisputas.min.js') }}?v={{ time() }}"></script>
@endsection
