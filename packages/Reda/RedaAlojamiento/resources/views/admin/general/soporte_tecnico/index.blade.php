@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Soporte Técnico</h1>
        @include('admin.common.breadcrumb')
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Panel de Control de Soporte Técnico</h3>
                    </div>
                    <div class="box-body">
                        <p>Esta es la página de inicio para el Soporte Técnico. Aquí podrás gestionar las solicitudes de asistencia.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/reda/admin/general/soporteTecnico.min.js') }}"></script>
@endpush
