{{-- packages/Reda/RedaAlojamiento/resources/views/general/paginacion.blade.php --}}

{{-- packages/Reda/RedaAlojamiento/resources/views/general/paginacion.blade.php --}}

<div class="d-flex flex-column flex-md-row align-items-center justify-content-center w-100 mt-4 pagination-reda-container">
    {{-- Botón Anterior --}}
    <nav class="mb-3 mb-md-0 mr-md-3">
        <ul class="pagination mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left mr-2"></i> {{ __('Anterior') }}
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="fas fa-chevron-left mr-2"></i> {{ __('Anterior') }}
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    {{-- Cuadros con los números de página --}}
    <nav class="mb-3 mb-md-0 mr-md-3">
        <ul class="pagination mb-0 flex-wrap justify-content-center">
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
        </ul>
    </nav>

    {{-- Botón Siguiente --}}
    <nav class="mb-3 mb-md-0">
        <ul class="pagination mb-0">
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        {{ __('Siguiente') }} <i class="fas fa-chevron-right ml-2"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">{{ __('Siguiente') }} <i class="fas fa-chevron-right ml-2"></i></span>
                </li>
            @endif
        </ul>
    </nav>
</div>

{{-- Información de registros --}}
<div class="text-center mt-3 text-muted small">
    {{ __('Mostrando') }} {{ $paginator->firstItem() ?? 0 }} 
    {{ __('a') }} {{ $paginator->lastItem() ?? 0 }} 
    {{ __('de') }} {{ $paginator->total() }} {{ __('registros') }}
</div>
