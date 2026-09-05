@php
    $columnItems = collect(data_get($column, 'items', []))
        ->sortBy(fn ($columnItem, $index) => [(int) data_get($columnItem, 'sort_order', $index), $index]);
    $columnName = data_get($column, 'title.uk') ?: data_get($column, 'title.ru') ?: trans('admin.catalog_menu_new_column');
@endphp

<article
    class="catalog-menu-column"
    data-menu-sort-item
    data-menu-removable
    data-column-index="{{ $columnIndex }}"
>
    <input
        type="hidden"
        name="columns[{{ $columnIndex }}][sort_order]"
        value="{{ data_get($column, 'sort_order', $columnIndex === '__COLUMN__' ? 0 : $columnIndex) }}"
        data-menu-sort-order
    >

    <div class="catalog-menu-column__rail">
        @include('pages.admin.catalog-menu.partials.drag-handle', [
            'dragLabel' => trans('admin.menu_drag_item', ['ITEM' => $columnName]),
        ])
        <span class="catalog-menu-column__line" aria-hidden="true"></span>
    </div>

    <div class="catalog-menu-column__content">
        <div class="catalog-menu-column__header">
            <div>
                <span>{{ trans('admin.catalog_menu_column') }} <b data-column-number></b></span>
                <strong data-column-summary-locale="uk" data-empty-label="{{ trans('admin.catalog_menu_new_column') }}" @if(($initialMenuLocale ?? 'uk') !== 'uk') hidden @endif>{{ data_get($column, 'title.uk') ?: trans('admin.catalog_menu_new_column') }}</strong>
                <strong data-column-summary-locale="ru" data-empty-label="{{ trans('admin.catalog_menu_new_column') }}" @if(($initialMenuLocale ?? 'uk') !== 'ru') hidden @endif>{{ data_get($column, 'title.ru') ?: trans('admin.catalog_menu_new_column') }}</strong>
            </div>
            <div class="catalog-menu-column__actions">
                <button
                    class="catalog-menu-icon-button"
                    type="button"
                    data-menu-collapse
                    aria-expanded="true"
                    aria-label="{{ trans('admin.catalog_menu_collapse') }}"
                    data-expand-label="{{ trans('admin.catalog_menu_expand') }}"
                    data-collapse-label="{{ trans('admin.catalog_menu_collapse') }}"
                >
                    <span class="fe fe-chevron-up" aria-hidden="true"></span>
                </button>
                <button class="catalog-menu-icon-button catalog-menu-icon-button--danger" type="button" data-menu-remove aria-label="{{ trans('admin.delete') }}">
                    <span class="fe fe-trash-2" aria-hidden="true"></span>
                </button>
            </div>
        </div>

        <div class="catalog-menu-column__body" data-menu-collapsible-body>
            <div class="catalog-menu-column__title-fields">
                @foreach(['uk', 'ru'] as $locale)
                    <div class="form-group mb-0" data-menu-locale-content="{{ $locale }}" @if($locale !== ($initialMenuLocale ?? 'uk')) hidden @endif>
                        <label>{{ trans('admin.catalog_menu_column_title') }}</label>
                        <input
                            class="form-control"
                            type="text"
                            name="columns[{{ $columnIndex }}][title][{{ $locale }}]"
                            value="{{ data_get($column, "title.$locale") }}"
                            maxlength="120"
                            data-column-summary-input="{{ $locale }}"
                            placeholder="{{ trans('admin.catalog_menu_column_title_placeholder') }}"
                        >
                    </div>
                @endforeach
            </div>

            <div class="catalog-menu-nested-list" data-items-container data-menu-sort-list>
                @foreach($columnItems as $itemIndex => $item)
                    @include('pages.admin.catalog-menu.partials.catalog-column-item', [
                        'columnIndex' => $columnIndex,
                        'itemIndex' => $itemIndex,
                        'item' => $item,
                        'menuProductType' => $menuProductType,
                    ])
                @endforeach

                <div class="catalog-menu-list-empty catalog-menu-list-empty--compact" data-menu-list-empty>
                    <p>{{ trans('admin.catalog_menu_column_empty') }}</p>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-secondary catalog-menu-add-nested" data-add-item>
                <span class="fe fe-plus mr-1" aria-hidden="true"></span>{{ trans('admin.catalog_menu_add_item') }}
            </button>
        </div>
    </div>
</article>
