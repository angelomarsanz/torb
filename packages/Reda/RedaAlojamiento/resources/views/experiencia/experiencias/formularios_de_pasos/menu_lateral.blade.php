<ul class="list-group customlisting">
    @php
        // Definimos los pasos para iterar o simplemente los listamos
        $steps = [
            'descripcion'           => __('reda-alojamiento::messages.php.descripcion'),
            'fotos'                 => __('reda-alojamiento::messages.php.fotos'),
            'actividades'           => __('reda-alojamiento::messages.php.productos_y_servicios'),
            'ubicacion'             => __('reda-alojamiento::messages.php.ubicacion'),
            'horario'               => __('reda-alojamiento::messages.php.horario'),
            'precio'                => __('reda-alojamiento::messages.php.precio'),
            'informacion_adicional' => __('reda-alojamiento::messages.php.informacion_adicional'),
            'anfitrion'             => __('reda-alojamiento::messages.php.anfitrion')
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