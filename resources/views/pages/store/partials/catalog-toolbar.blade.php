@php
    $sortOptions = App\DataClasses\ProductSortOptionsDataClass::get();
    $selectedSort = $sortOptions->firstWhere('id', $filtersData['sort_by'] ?? null)
        ?? $sortOptions->firstWhere('is_active_by_default', true)
        ?? $sortOptions->first();
@endphp

<div class="bona-catalog__toolbar">
    <button id="art-filter-display" class="art-filter-display bona-catalog__filter-toggle" type="button" aria-controls="art-products-filter" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M3 5h14M5.5 10h9M8 15h4"></path>
        </svg>
        <span>{{ trans('base.catalog_filters') }}</span>
    </button>

    <div class="bona-catalog__programs">
        <span class="bona-catalog__program-title">{{ trans('base.catalog_payment_programs') }}</span>
        <ul aria-label="{{ trans('base.catalog_payment_programs') }}">
            <li class="is-recovery">
                <img src="{{ Vite::asset('bona-html/eVidnovlennya.svg') }}" alt="єВідновлення">
            </li>
            <li>
                <img class="is-bank-logo" src="{{ Vite::asset('bona-html/monobank-logo.svg') }}" alt="">
                <span><small>{{ trans('base.catalog_pay_parts') }}</small><b>monobank</b></span>
            </li>
            <li>
                <img class="is-bank-logo" src="{{ Vite::asset('bona-html/privatbank-chastyny.svg') }}" alt="">
                <span><small>{{ trans('base.catalog_pay_parts') }}</small><b>ПриватБанк</b></span>
            </li>
        </ul>
    </div>

    <div class="bona-catalog__controls">
        <div class="dropdown dropdown-custom bona-catalog__select">
            <button class="btn btn-dropdown dropdown-toggle" type="button" id="catalog-sort-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span>{{ trans('base.sort_by') }}</span>
                <strong>{{ $selectedSort['name'] ?? '' }}</strong>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="catalog-sort-menu">
                @foreach($sortOptions as $sortFilter)
                    <a
                        class="dropdown-item sort-by-option {{ ($selectedSort['id'] ?? null) === $sortFilter['id'] ? 'active' : '' }}"
                        href="#"
                        id="{{ $sortFilter['id'] }}"
                    >{{ $sortFilter['name'] }}</a>
                @endforeach
            </div>
        </div>

    </div>
</div>
