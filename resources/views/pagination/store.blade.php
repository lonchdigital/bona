@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $visiblePages = collect([
            1,
            $currentPage <= 3 ? 2 : null,
            $currentPage <= 3 ? 3 : null,
            $currentPage - 1,
            $currentPage,
            $currentPage + 1,
            $lastPage,
        ])->filter(fn ($page) => is_int($page) && $page >= 1 && $page <= $lastPage)
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div
        class="bona-catalog__pagination-block"
        data-catalog-pagination
        data-current-page="{{ $currentPage }}"
        data-last-page="{{ $lastPage }}"
    >
        @if ($paginator->hasMorePages())
            <a
                class="bona-catalog__load-more"
                href="{{ $paginator->nextPageUrl() }}"
                data-catalog-load-more
                data-loading-label="{{ trans('base.catalog_loading_more') }}"
                data-loaded-label="{{ trans('base.catalog_page_loaded', ['page' => $currentPage + 1]) }}"
            >
                <span>{{ trans('base.catalog_load_more') }}</span>
                <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M9 3v11"></path>
                    <path d="m4.5 9.5 4.5 4.5 4.5-4.5"></path>
                </svg>
            </a>
        @endif

        <nav class="pagination-wrapper" aria-label="{{ trans('base.catalog_pagination') }}">
            <ul class="pagination">
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    @if ($paginator->onFirstPage())
                        <span class="page-link page-link--arrow" aria-disabled="true" aria-label="{{ trans('base.pagination_previous') }}">
                            <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m11 4-5 5 5 5"></path></svg>
                        </span>
                    @else
                        <a class="page-link page-link--arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ trans('base.pagination_previous') }}">
                            <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m11 4-5 5 5 5"></path></svg>
                        </a>
                    @endif
                </li>

                @foreach ($visiblePages as $page)
                    @if (! $loop->first && $page - $visiblePages[$loop->index - 1] > 1)
                        <li class="page-item page-item--ellipsis" aria-hidden="true">
                            <span class="page-link">…</span>
                        </li>
                    @endif

                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                        @if ($page === $currentPage)
                            <span class="page-link" aria-current="page" aria-label="{{ trans('base.pagination_current_page', ['page' => $page]) }}">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $paginator->url($page) }}" aria-label="{{ trans('base.pagination_go_to_page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    </li>
                @endforeach

                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    @if ($paginator->hasMorePages())
                        <a class="page-link page-link--arrow" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ trans('base.pagination_next') }}">
                            <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m7 4 5 5-5 5"></path></svg>
                        </a>
                    @else
                        <span class="page-link page-link--arrow" aria-disabled="true" aria-label="{{ trans('base.pagination_next') }}">
                            <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m7 4 5 5-5 5"></path></svg>
                        </span>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
@endif
