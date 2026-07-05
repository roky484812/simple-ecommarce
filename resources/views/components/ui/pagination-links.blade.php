@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" {{ $attributes->class(['flex justify-center']) }}>
        <div class="join">
            @if ($paginator->onFirstPage())
                <span class="join-item btn btn-disabled" aria-disabled="true" aria-label="Previous">&laquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="join-item btn" rel="prev" aria-label="Previous">&laquo;</a>
            @endif

            @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                <a
                    href="{{ $url }}"
                    class="join-item btn {{ $page === $paginator->currentPage() ? 'btn-active' : '' }}"
                    @if ($page === $paginator->currentPage()) aria-current="page" @endif
                >
                    {{ $page }}
                </a>
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="join-item btn" rel="next" aria-label="Next">&raquo;</a>
            @else
                <span class="join-item btn btn-disabled" aria-disabled="true" aria-label="Next">&raquo;</span>
            @endif
        </div>
    </nav>
@endif
