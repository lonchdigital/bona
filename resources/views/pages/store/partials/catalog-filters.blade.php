@php
    $showColorFilter = isset($colors)
        && $colors->isNotEmpty()
        && (! isset($productType) || $productType->has_color);
@endphp

<aside id="art-products-filter" class="filters bona-catalog__filters" aria-label="{{ trans('base.filter_noun') }}">
    <div class="filter-top-wrapper bona-catalog__filters-head">
        <strong>{{ trans('base.filter_noun') }}</strong>
        <button type="button" aria-label="{{ trans('base.catalog_close_filters') }}">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                <path d="M4 4l12 12M16 4 4 16"></path>
            </svg>
        </button>
    </div>

    <form action="#" id="filter-left-form">
        @include('pages.store.partials.sidebar_filters', [
            'filters' => $filters,
            'filtersData' => $filtersData,
            'productsMaxPrice' => $productsMaxPrice,
            'productStatuses' => $productStatuses,
            'selectedBrand' => $selectedBrand ?? null,
        ])

        @if($showColorFilter)
            <div class="archive-catalog-filter-left filter-box active">
                <div class="filter-box filter-item1 filter-item--colors active">
                    <div class="title font-title">{{ trans('base.color') }}</div>
                    <div class="filter-content">
                        <div id="art-filter-color-content" class="art-filter-color-content colors-wrapper {{ $colors->count() > 5 ? 'content-hidden' : 'content-expanded' }}">
                            @foreach($colors as $color)
                                @include('pages.store.partials.color_item', ['color' => $color, 'filtersData' => $filtersData])
                            @endforeach
                        </div>

                        @if($colors->count() > 5)
                            <button id="art-filter-color-control" class="art-filter-color-control" type="button">
                                <span class="art-show-colors">{{ trans('base.filter_show_more_colors') }}</span>
                                <span class="art-hide-colors d-none">{{ trans('base.filter_show_less_colors') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="bona-catalog__filter-actions">
            <button type="button" class="filter-submit-main bona-button bona-button--dark">
                {{ trans('base.apply') }}
            </button>
            <button type="button" class="filter-reset">{{ trans('base.filter_reset') }}</button>
        </div>
    </form>
</aside>
