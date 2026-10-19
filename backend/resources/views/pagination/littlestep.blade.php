@php
    $pageClass = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-sm font-semibold transition';
    $linkClass = $pageClass.' text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500';
    $disabledClass = $pageClass.' cursor-not-allowed text-gray-300';
@endphp
<nav data-pagination aria-label="Pagination" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-gray-500">
        Showing <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span>–<span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span>
        of <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span>
    </p>

    @if ($paginator->hasPages())
        <ul class="flex flex-wrap items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $disabledClass }}" aria-disabled="true" aria-label="Previous page"><x-icon name="back" /></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $linkClass }}" aria-label="Previous page"><x-icon name="back" /></a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="{{ $pageClass }} text-gray-400" aria-hidden="true">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $pageClass }} bg-blue-600 text-white" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $linkClass }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $linkClass }}" aria-label="Next page"><x-icon name="next" /></a>
                @else
                    <span class="{{ $disabledClass }}" aria-disabled="true" aria-label="Next page"><x-icon name="next" /></span>
                @endif
            </li>
        </ul>
    @endif
</nav>
