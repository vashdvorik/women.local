{{-- Постраничная навигация в стиле темы miro (стандартный шаблон Laravel рассчитан на Tailwind, которого на сайте нет). --}}
@if($paginator && $paginator->hasPages())
    <nav class="miro-pager" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="miro-button miro-button--secondary is-disabled" aria-disabled="true">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="miro-button miro-button--secondary" rel="prev">←</a>
        @endif

        <span class="miro-pager__status">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="miro-button miro-button--secondary" rel="next">→</a>
        @else
            <span class="miro-button miro-button--secondary is-disabled" aria-disabled="true">→</span>
        @endif
    </nav>
@endif
