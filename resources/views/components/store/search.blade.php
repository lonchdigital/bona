@props([
    'id' => 'bona-header-search',
])

<form
    class="bona-search"
    role="search"
    action="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}"
    method="get"
    data-storefront-search
    data-search-url="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.search') }}"
>
    <svg class="bona-search__icon" width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <circle cx="9" cy="9" r="6"></circle>
        <path d="m13.6 13.6 4.4 4.4"></path>
    </svg>
    <label class="sr-only" for="{{ $id }}">{{ trans('base.storefront_search_label') }}</label>
    <input
        id="{{ $id }}"
        name="query"
        type="search"
        minlength="3"
        maxlength="120"
        autocomplete="off"
        placeholder="{{ trans('base.storefront_search_placeholder') }}"
        role="combobox"
        aria-autocomplete="list"
        aria-controls="{{ $id }}-results"
        aria-expanded="false"
    >
    <button
        class="bona-search__clear"
        type="button"
        aria-label="{{ trans('base.storefront_search_clear') }}"
        data-search-clear
        hidden
    >
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M3 3 13 13M13 3 3 13"></path>
        </svg>
    </button>
    <div
        id="{{ $id }}-results"
        class="bona-search-results"
        role="listbox"
        aria-label="{{ trans('base.storefront_search_results') }}"
        aria-live="polite"
        hidden
    ></div>
</form>
