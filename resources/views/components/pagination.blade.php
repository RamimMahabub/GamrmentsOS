@if ($paginator->hasPages())
<nav aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-4">
    <p class="text-xs text-muted">Showing <span class="text-ink">{{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }}</span> of {{ $paginator->total() }} records</p>
    <div class="flex flex-wrap items-center gap-2">
        @if ($paginator->onFirstPage())<span class="btn btn-secondary opacity-40" aria-disabled="true">Previous</span>@else<a class="btn btn-secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>@endif
        <div class="hidden items-center gap-1 sm:flex">
        @foreach ($elements as $element)
            @if (is_string($element))<span class="px-2 text-muted">{{ $element }}</span>@endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())<span class="btn btn-primary" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>@else<a class="btn btn-secondary" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>@endif
                @endforeach
            @endif
        @endforeach
        </div>
        @if ($paginator->hasMorePages())<a class="btn btn-secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>@else<span class="btn btn-secondary opacity-40" aria-disabled="true">Next</span>@endif
    </div>
</nav>
@endif
