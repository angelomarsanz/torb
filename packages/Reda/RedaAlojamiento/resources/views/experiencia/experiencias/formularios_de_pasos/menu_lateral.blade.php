<div class="stepper-menu-container">
    <ul class="list-group customlisting">
        @php
            $steps = [
                'descripcion'           => __('reda-alojamiento::messages.general.descripcion'),
                'fotos'                 => __('reda-alojamiento::messages.general.fotos'),
                'actividades'           => __('reda-alojamiento::messages.general.productos_y_servicios'),
                'ubicacion'             => __('reda-alojamiento::messages.general.ubicacion'),
                'horario'               => __('reda-alojamiento::messages.general.horario'),
                'anfitrion'             => __('reda-alojamiento::messages.general.anfitrion'),
                'informacion_adicional' => __('reda-alojamiento::messages.general.informacion_adicional'),
                'precio'                => __('reda-alojamiento::messages.general.precio')
            ];
        @endphp

        @foreach($steps as $key => $label)
            <li class="{{ $paso == $key ? 'is-active' : '' }}">
                <a class="btn text-16 font-weight-700 pl-lg-5 pr-lg-5 pt-3 pb-3 rounded-3 {{ $paso == $key ? 'vbtn-outline-success active-side' : 'btn-outline-secondary' }}"
                href="{{ route('reda.experiencias.pasos', [$result->id, $key]) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
