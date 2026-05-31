@if(!empty($horarios) && is_array($horarios))
    <div class="table-responsive mt-3">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ __('Días') }}</th>
                    <th>{{ __('Horas') }}</th>
                    <th class="text-center">{{ __('Acciones') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($horarios as $index => $horario)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @php
                                    $diasSemana = [
                                        'lun' => 'Lun',
                                        'mar' => 'Mar',
                                        'mie' => 'Mie',
                                        'jue' => 'Jue',
                                        'vie' => 'Vie',
                                        'sab' => 'Sáb',
                                        'dom' => 'Dom'
                                    ];
                                @endphp
                                @foreach($diasSemana as $key => $label)
                                    <div class="mr-2 text-center" style="width: 35px;">
                                        <div class="custom-control custom-checkbox p-0">
                                            <input type="checkbox" class="d-none" @if(in_array($key, $horario['dias'])) checked @endif disabled>
                                            <span class="badge {{ in_array($key, $horario['dias']) ? 'badge-success' : 'badge-light border' }}" style="font-size: 10px; padding: 4px 6px;">
                                                {{ $label }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @foreach($horario['bloques'] as $bloque)
                                <div class="badge badge-outline-primary mb-1">
                                    {{ $bloque['hora_desde'] }} {{ strtoupper($bloque['ampm_desde']) }} - {{ $bloque['hora_hasta'] }} {{ strtoupper($bloque['ampm_hasta']) }}
                                </div>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-info btn-ver-horario" data-index="{{ $index }}" data-horario="{{ json_encode($horario) }}" title="{{ __('Ver horario') }}">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-editar-horario" data-index="{{ $index }}" data-horario="{{ json_encode($horario) }}" title="{{ __('Editar') }}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-horario" data-index="{{ $index }}" title="{{ __('Eliminar') }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Vista móvil (Cards) -->
    <div class="d-block d-sm-none mt-3">
        @foreach($horarios as $index => $horario)
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex flex-wrap">
                            @foreach($diasSemana as $key => $label)
                                <span class="badge {{ in_array($key, $horario['dias']) ? 'badge-success' : 'badge-light border' }} mr-1 mb-1" style="font-size: 10px;">
                                    {{ $label }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        @foreach($horario['bloques'] as $bloque)
                            <div class="badge badge-light border mb-1">
                                {{ $bloque['hora_desde'] }} {{ strtoupper($bloque['ampm_desde']) }} - {{ $bloque['hora_hasta'] }} {{ strtoupper($bloque['ampm_hasta']) }}
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-end border-top pt-2">
                        <button type="button" class="btn btn-sm btn-outline-info mr-2 btn-ver-horario" data-index="{{ $index }}" data-horario="{{ json_encode($horario) }}">
                            <i class="fa fa-eye"></i> {{ __('Ver') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary mr-2 btn-editar-horario" data-index="{{ $index }}" data-horario="{{ json_encode($horario) }}">
                            <i class="fa fa-edit"></i> {{ __('Editar') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-horario" data-index="{{ $index }}">
                            <i class="fa fa-trash"></i> {{ __('Eliminar') }}
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center p-5 mt-3 border rounded bg-light">
        <i class="fa fa-clock fa-4x text-muted mb-3"></i>
        <p class="text-muted">{{ __('No se han configurado horarios aún.') }}</p>
    </div>
@endif
