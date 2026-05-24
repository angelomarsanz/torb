{{-- Archivo maestro para inyectar recursos al final del <body> del Usuario --}}

{{-- 1. Traducciones de Laravel a JS --}}
<script>
    window.RedaAlojamiento = @json(__('reda-alojamiento::messages'));
    window.RedaAlojamientoJson = @json(__('reda-alojamiento::es'));
</script>

{{-- 2. Modales de uso general --}}
@include('reda-alojamiento::general.modal_notificaciones')
@include('reda-alojamiento::general.modal_confirmacion')

{{-- 3. Scripts de uso general del plugin --}}
<script src="{{ asset('public/js/reda/general/reda-general-main.min.js') }}?v={{ time() }}"></script>
