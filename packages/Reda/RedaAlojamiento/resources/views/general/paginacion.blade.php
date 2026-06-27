{{-- packages/Reda/RedaAlojamiento/resources/views/general/paginacion.blade.php --}}

<div class="mt-4">
    <div class="text-16 mt-4">
        <span>{{ __('Mostrando') }} {{ $paginator->firstItem() ?? 0 }}</span> 
        {{ __('a') }} <span id="page-to">{{ $paginator->lastItem() ?? 0 }} </span> 
        {{ __('de') }} <span>{{ $paginator->total() }}</span> {{ __('registros') }}
    </div>
</div>

<div class="mt-4">
    <ul class="pagination">
        {{-- Botón Anterior --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link">{{ __('Anterior') }}</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('Anterior') }}</a>
            </li>
        @endif
    
        {{-- Elementos de la paginación --}}
        @foreach ($elements as $element)
            {{-- Separador de puntos --}}
            @if (is_string($element))
                <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
            @endif
    
            {{-- Array de Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a href="{{ $url }}" class="page-link">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach
    
        {{-- Botón Siguiente --}}
        @if ($paginator->hasMorePages())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('Siguiente') }}</a></li>
        @else
            <li class="page-item disabled">
                <span class="page-link">{{ __('Siguiente') }} </span>
            </li>
        @endif
    </ul>
</div>
