@if ($paginator->hasPages())
    <nav class="bona-page-pagination" aria-label="{{ trans('base.pagination_label') }}">
        <ul class="bona-page-pagination__list">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="bona-page-pagination__disabled" aria-disabled="true" aria-label="{{ trans('base.pagination_previous') }}">
                        <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m11 4-5 5 5 5"></path></svg>
                    </span>
                @else
                    <a class="bona-page-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ trans('base.pagination_previous') }}">
                        <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m11 4-5 5 5 5"></path></svg>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li aria-hidden="true"><span class="bona-page-pagination__ellipsis">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page === $paginator->currentPage())
                                <span class="bona-page-pagination__current" aria-current="page" aria-label="{{ trans('base.pagination_current_page', ['page' => $page]) }}">{{ $page }}</span>
                            @else
                                <a class="bona-page-pagination__link" href="{{ $url }}" aria-label="{{ trans('base.pagination_go_to_page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a class="bona-page-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ trans('base.pagination_next') }}">
                        <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m7 4 5 5-5 5"></path></svg>
                    </a>
                @else
                    <span class="bona-page-pagination__disabled" aria-disabled="true" aria-label="{{ trans('base.pagination_next') }}">
                        <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m7 4 5 5-5 5"></path></svg>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
