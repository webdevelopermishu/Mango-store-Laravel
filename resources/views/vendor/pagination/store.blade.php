@if ($paginator->hasPages())
<nav class="pager" aria-label="{{ __('storefront.pagination.label') }}">
    @if ($paginator->onFirstPage())
        <span class="pager__link is-disabled" aria-disabled="true">{{ __('storefront.pagination.previous') }}</span>
    @else
        <a class="pager__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('storefront.pagination.previous') }}</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="pager__gap" aria-hidden="true">…</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pager__link is-current" aria-current="page">{{ $page }}</span>
                @else
                    <a class="pager__link" href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a class="pager__link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('storefront.pagination.next') }}</a>
    @else
        <span class="pager__link is-disabled" aria-disabled="true">{{ __('storefront.pagination.next') }}</span>
    @endif
</nav>
@endif
