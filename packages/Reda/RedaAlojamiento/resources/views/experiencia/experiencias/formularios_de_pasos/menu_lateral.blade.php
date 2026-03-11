<ul class="list-group customlisting">
    @php
        // Definimos los pasos para iterar o simplemente los listamos
        $steps = [
            'descripcion'           => __('reda-alojamiento::messages.general.descripcion'),
            'fotos'                 => __('reda-alojamiento::messages.general.fotos'),
            'actividades'           => __('reda-alojamiento::messages.general.productos_y_servicios'),
            'ubicacion'             => __('reda-alojamiento::messages.general.ubicacion'),
            'horario'               => __('reda-alojamiento::messages.general.horario'),
            'precio'                => __('reda-alojamiento::messages.general.precio'),
            'informacion_adicional' => __('reda-alojamiento::messages.general.informacion_adicional'),
            'anfitrion'             => __('reda-alojamiento::messages.general.anfitrion')
        ];
    @endphp

    @foreach($steps as $key => $label)
        <li>
            <a class="btn text-16 font-weight-700 pl-5 pr-5 pt-3 pb-3 rounded-3 {{ $paso == $key ? 'vbtn-outline-success active-side' : 'btn-outline-secondary' }}" 
               href="{{ route('reda.experiencias.pasos', [$result->id, $key]) }}">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>