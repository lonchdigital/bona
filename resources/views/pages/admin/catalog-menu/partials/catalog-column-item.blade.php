@php
    $selectedCategoryId = (int) data_get($item, 'category_id', 0);
    $itemName = $selectedCategoryId
        ? $menuProductType->categories->firstWhere('id', $selectedCategoryId)?->name
        : (data_get($item, 'label.uk') ?: data_get($item, 'label.ru'));
@endphp

<div
    class="catalog-menu-nested-item"
    data-menu-sort-item
    data-menu-removable
    data-item-index="{{ $itemIndex }}"
>
    <input
        type="hidden"
        name="columns[{{ $columnIndex }}][items][{{ $itemIndex }}][sort_order]"
        value="{{ data_get($item, 'sort_order', $itemIndex === '__ITEM__' ? 0 : $itemIndex) }}"
        data-menu-sort-order
    >

    @include('pages.admin.catalog-menu.partials.drag-handle', [
        'dragLabel' => trans('admin.menu_drag_item', [
            'ITEM' => $itemName ?: trans('admin.catalog_menu_new_item'),
        ]),
    ])

    <div class="catalog-menu-nested-item__body">
        <div class="catalog-menu-nested-item__top">
            <div class="form-group mb-0">
                <label>{{ trans('admin.catalog_menu_category_target') }}</label>
                <select
                    class="form-control"
                    name="columns[{{ $columnIndex }}][items][{{ $itemIndex }}][category_id]"
                    data-menu-category-select
                >
                    <option value="">{{ trans('admin.catalog_menu_custom_link') }}</option>
                    @foreach($menuProductType->categories as $category)
                        <option
                            value="{{ $category->id }}"
                            data-menu-localized-option
                            data-label-uk="{{ $category->getTranslation('name', 'uk') }}"
                            data-label-ru="{{ $category->getTranslation('name', 'ru') }}"
                            @selected($selectedCategoryId === $category->id)
                        >{{ $category->getTranslation('name', 'uk') }}</option>
                    @endforeach
                </select>
            </div>

            <button class="catalog-menu-icon-button catalog-menu-icon-button--danger" type="button" data-menu-remove aria-label="{{ trans('admin.delete') }}">
                <span class="fe fe-trash-2" aria-hidden="true"></span>
            </button>
        </div>

        <p class="catalog-menu-category-auto-hint" data-menu-category-auto-hint>
            <span class="fe fe-zap" aria-hidden="true"></span>{{ trans('admin.catalog_menu_category_auto_hint') }}
        </p>

        <div class="catalog-menu-custom-link-fields" data-menu-custom-link-fields>
            @foreach(['uk', 'ru'] as $locale)
                <div data-menu-locale-content="{{ $locale }}" @if($locale !== ($initialMenuLocale ?? 'uk')) hidden @endif>
                    <div class="catalog-menu-custom-link-grid">
                        <div class="form-group mb-0">
                            <label>{{ trans('admin.catalog_menu_custom_label') }}</label>
                            <input
                                class="form-control"
                                type="text"
                                name="columns[{{ $columnIndex }}][items][{{ $itemIndex }}][label][{{ $locale }}]"
                                value="{{ data_get($item, "label.$locale") }}"
                                maxlength="160"
                                placeholder="{{ trans('admin.catalog_menu_custom_label_placeholder') }}"
                            >
                        </div>
                        <div class="form-group mb-0">
                            <label>URL</label>
                            <input
                                class="form-control"
                                type="text"
                                name="columns[{{ $columnIndex }}][items][{{ $itemIndex }}][url][{{ $locale }}]"
                                value="{{ data_get($item, "url.$locale") }}"
                                maxlength="2048"
                                placeholder="{{ $locale === 'ru' ? '/ru/product-category/...' : '/product-category/...' }}"
                            >
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
