
<nav class="reda-pagination-container mt-4" aria-label="Navegación de páginas">
    <ul class="pagination pagination-sm justify-content-center mb-0">
        {{-- Botón Anterior --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <span class="page-link" aria-hidden="true">
                    <i class="fa fa-chevron-left f-10"></i>
                </span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                    <i class="fa fa-chevron-left f-10"></i>
                </a>
            </li>
        @endif

        {{-- Elementos de la paginación --}}
        @if ($paginator->hasPages())
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @else
            {{-- Si no hay páginas (10 o menos registros), mostramos el número 1 activo y deshabilitado --}}
            <li class="page-item active" aria-current="page"><span class="page-link">1</span></li>
        @endif

        {{-- Botón Siguiente --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                    <i class="fa fa-chevron-right f-10"></i>
                </a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                <span class="page-link" aria-hidden="true">
                    <i class="fa fa-chevron-right f-10"></i>
                </span>
            </li>
        @endif
    </ul>
    
    <div class="text-center mt-2">
        <small class="text-muted">
            {{ __('Mostrando') }} {{ $paginator->firstItem() ?: 0 }} {{ __('a') }} {{ $paginator->lastItem() ?: 0 }} {{ __('de') }} {{ $paginator->total() }} {{ __('registros') }}
        </small>
    </div>
</nav>
