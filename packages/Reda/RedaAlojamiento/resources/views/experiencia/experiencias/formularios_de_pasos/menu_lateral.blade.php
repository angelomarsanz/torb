<ul class="list-group customlisting">
    @php
        // Definimos los pasos para iterar o simplemente los listamos
        $steps = [
            'descripcion' => __('Descripción'),
            'fotos'       => __('Fotos'),
            'actividades' => __('Actividades'),
            'ubicacion'   => __('Ubicación'),
            'horario'     => __('Horario'),
            'precio'      => __('Precio'),
            'informacion' => __('Información adicional'),
            'anfitrion'   => __('Anfitrión'),
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