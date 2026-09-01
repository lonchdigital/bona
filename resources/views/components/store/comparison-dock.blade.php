@php
    $comparisonUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.comparison.page');
    $maxProducts = App\Services\Product\ProductComparisonService::MAX_PRODUCTS;
@endphp

<aside
    class="bona-compare-dock"
    aria-label="{{ trans('base.comparison') }}"
    data-comparison-dock
    data-comparison-base-url="{{ $comparisonUrl }}"
    data-max-products="{{ $maxProducts }}"
    data-added-message="{{ trans('base.comparison_added') }}"
    data-removed-message="{{ trans('base.comparison_removed') }}"
    data-limit-message="{{ trans('base.comparison_max_reached', ['count' => $maxProducts]) }}"
    data-minimum-message="{{ trans('base.comparison_need_two') }}"
    hidden
>
    <div class="bona-compare-dock__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M5 7h14M16 4l3 3-3 3M19 17H5M8 14l-3 3 3 3"></path></svg>
    </div>
    <div class="bona-compare-dock__copy">
        <strong>{{ trans('base.comparison') }}</strong>
        <span>
            {{ trans('base.comparison_selected') }}
            <b data-comparison-count>0</b> / {{ $maxProducts }}
        </span>
        <small data-comparison-message aria-live="polite"></small>
    </div>
    <div class="bona-compare-dock__actions">
        <button type="button" data-comparison-clear>{{ trans('base.comparison_clear') }}</button>
        <a href="{{ $comparisonUrl }}" data-comparison-link data-comparison-open>
            {{ trans('base.comparison_open') }} <span aria-hidden="true">→</span>
        </a>
    </div>
</aside>
