{{-- Archivo maestro para inyectar recursos al final del <body> del Administrador --}}

{{-- 1. Traducciones de Laravel a JS --}}
<script>window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));</script>

{{-- 2. Modales de uso general --}}
@include('reda-alojamiento::admin.general.modal_notificaciones')

{{-- 3. Scripts de uso general del plugin --}}
<script type="text/javascript" src="{{  asset('public/js/reda/admin/general/reda-admin-general-main.min.js') }}?v={{ time() }}"></script>
