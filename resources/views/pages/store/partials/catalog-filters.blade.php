@php
    $showBrandFilter = isset($productType)
        && $productType->has_brand
        && isset($brandsSortedByFirstLetter)
        && $brandsSortedByFirstLetter->isNotEmpty();
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
        ])

        @if($showBrandFilter)
            <div class="archive-catalog-filter-left filter-box active">
                <div class="title font-title">{{ trans('base.manufacturer') }}</div>
                <div class="filter-content">
                    <div class="filter-item filter-item--brands position-relative checkbox-preview-wrap">
                        <input
                            class="search-input art-form-light-control"
                            type="search"
                            placeholder="{{ trans('base.search_by_brand') }}"
                            aria-label="{{ trans('base.search_by_brand') }}"
                        >
                        <div class="brands">
                            @foreach($brandsSortedByFirstLetter as $letter => $brandGroup)
                                <div class="option-letter">{{ $letter }}</div>
                                @foreach($brandGroup as $brand)
                                    @php
                                        $brandIsSelected = App\Services\Product\ProductFiltersService::filterOptionChecked($filtersData, 'brand', $brand->slug);
                                    @endphp
                                    <div class="checkbox checkbox-preview" data-toggle="tooltip">
                                        <div class="custom-control custom-checkbox position-relative {{ $brandIsSelected ? 'checked' : '' }}">
                                            <input
                                                class="custom-control-input sync-input"
                                                id="brand-{{ $brand->id }}-main"
                                                type="checkbox"
                                                name="brand"
                                                value="{{ $brand->slug }}"
                                                @checked($brandIsSelected)
                                            >
                                            <label class="custom-control-label" for="brand-{{ $brand->id }}-main">{{ $brand->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

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
