@forelse($experiencias as $experiencia)
    @include('reda-alojamiento::experiencia.experiencias.frontend.partials.card_negocio', ['experiencia' => $experiencia])
@empty
    <div class="col-12 text-center py-5">
        <i class="fas fa-store-slash fa-4x mb-3 text-muted"></i>
        <p class="text-16">{{ __('No se encontraron negocios disponibles con estos criterios.') }}</p>
    </div>
@endforelse
