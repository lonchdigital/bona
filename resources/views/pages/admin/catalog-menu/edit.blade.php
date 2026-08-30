@extends('layouts.admin-main')

@php
    $configuredCards = old('cards');
    $savedCardIds = collect($menuConfiguration?->cards ?? $menuProductType->categories->take(5)->pluck('id')->all())
        ->map(fn ($id) => (int) $id)
        ->values();
    $cardOrder = $savedCardIds->flip();
    $columns = old('columns', $menuConfiguration?->columns ?? []);
@endphp

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <h2 class="mb-2 page-title">{{ trans('admin.catalog_menu') }} — {{ $menuProductType->name }}</h2>
                        <p class="card-text mb-0">{{ trans('admin.catalog_menu_content_description') }}</p>
                    </div>
                    <a href="{{ route('admin.catalog-menu.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
                </div>

                @if(Session::has('success'))
                    <div class="alert alert-success" role="alert">{{ Session::get('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>{{ trans('admin.catalog_menu_validation_error') }}</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.catalog-menu.edit', $menuProductType) }}">
                    @csrf

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <strong>{{ trans('admin.catalog_menu_visual_cards') }}</strong>
                            <div class="small text-muted mt-1">{{ trans('admin.catalog_menu_visual_cards_hint') }}</div>
                        </div>
                        <div class="card-body">
                            @if($menuProductType->categories->isEmpty())
                                <div class="alert alert-warning mb-0">{{ trans('admin.catalog_menu_no_categories') }}</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                        <tr>
                                            <th class="text-center" style="width: 100px">{{ trans('admin.catalog_menu_show') }}</th>
                                            <th>{{ trans('admin.category') }}</th>
                                            <th style="width: 150px">{{ trans('admin.catalog_menu_order') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($menuProductType->categories as $category)
                                            @php
                                                $oldCard = $configuredCards[$category->id] ?? null;
                                                $isEnabled = $oldCard !== null
                                                    ? (bool) ($oldCard['enabled'] ?? false)
                                                    : $savedCardIds->contains($category->id);
                                                $order = $oldCard['sort_order'] ?? $cardOrder->get($category->id, $loop->index);
                                            @endphp
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <input type="hidden" name="cards[{{ $category->id }}][enabled]" value="0">
                                                    <input type="checkbox" name="cards[{{ $category->id }}][enabled]" value="1" @checked($isEnabled)>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        @if($category->image_url)
                                                            <img src="{{ $category->image_url }}" alt="" class="rounded mr-3" style="width: 48px; height: 48px; object-fit: cover">
                                                        @endif
                                                        <strong>{{ $category->name }}</strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input class="form-control" type="number" min="0" max="999" name="cards[{{ $category->id }}][sort_order]" value="{{ $order }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <strong>{{ trans('admin.catalog_menu_text_columns') }}</strong>
                                <div class="small text-muted mt-1">{{ trans('admin.catalog_menu_text_columns_hint') }}</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-add-column>{{ trans('admin.catalog_menu_add_column') }}</button>
                        </div>
                        <div class="card-body" data-columns-container>
                            @foreach($columns as $columnIndex => $column)
                                <div class="border rounded p-3 mb-4 catalog-menu-column" data-column-index="{{ $columnIndex }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <strong>{{ trans('admin.catalog_menu_column') }} <span data-column-number>{{ $loop->iteration }}</span></strong>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-column>{{ trans('admin.delete') }}</button>
                                    </div>
                                    <div class="row">
                                        @foreach($availableLanguages as $language)
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label>{{ trans('admin.catalog_menu_column_title') }} {{ mb_strtoupper($language) }}</label>
                                                    <input class="form-control" type="text" name="columns[{{ $columnIndex }}][title][{{ $language }}]" value="{{ $column['title'][$language] ?? '' }}">
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>{{ trans('admin.catalog_menu_order') }}</label>
                                                <input class="form-control" type="number" min="0" max="999" name="columns[{{ $columnIndex }}][sort_order]" value="{{ $column['sort_order'] ?? $columnIndex }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div data-items-container>
                                        @foreach($column['items'] ?? [] as $itemIndex => $item)
                                            <div class="bg-light rounded p-3 mb-3 catalog-menu-item" data-item-index="{{ $itemIndex }}">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <strong class="small">{{ trans('admin.catalog_menu_item') }}</strong>
                                                    <button type="button" class="btn btn-sm btn-link text-danger" data-remove-item>{{ trans('admin.delete') }}</button>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <label>{{ trans('admin.catalog_menu_category_target') }}</label>
                                                            <select class="form-control" name="columns[{{ $columnIndex }}][items][{{ $itemIndex }}][category_id]">
                                                                <option value="">{{ trans('admin.catalog_menu_custom_link') }}</option>
                                                                @foreach($menuProductType->categories as $category)
                                                                    <option value="{{ $category->id }}" @selected((int) ($item['category_id'] ?? 0) === $category->id)>{{ $category->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>{{ trans('admin.catalog_menu_order') }}</label>
                                                            <input class="form-control" type="number" min="0" max="999" name="columns[{{ $columnIndex }}][items][{{ $itemIndex }}][sort_order]" value="{{ $item['sort_order'] ?? $itemIndex }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    @foreach($availableLanguages as $language)
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ trans('admin.catalog_menu_custom_label') }} {{ mb_strtoupper($language) }}</label>
                                                                <input class="form-control" type="text" name="columns[{{ $columnIndex }}][items][{{ $itemIndex }}][label][{{ $language }}]" value="{{ $item['label'][$language] ?? '' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>URL {{ mb_strtoupper($language) }}</label>
                                                                <input class="form-control" type="text" name="columns[{{ $columnIndex }}][items][{{ $itemIndex }}][url][{{ $language }}]" value="{{ $item['url'][$language] ?? '' }}" placeholder="/product-category/...">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-add-item>{{ trans('admin.catalog_menu_add_item') }}</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="text-right mb-5">
                        <a href="{{ route('admin.catalog-menu.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
                        <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="catalog-menu-column-template">
        <div class="border rounded p-3 mb-4 catalog-menu-column" data-column-index="__COLUMN__">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>{{ trans('admin.catalog_menu_column') }} <span data-column-number></span></strong>
                <button type="button" class="btn btn-sm btn-outline-danger" data-remove-column>{{ trans('admin.delete') }}</button>
            </div>
            <div class="row">
                @foreach($availableLanguages as $language)
                    <div class="col-md-5"><div class="form-group"><label>{{ trans('admin.catalog_menu_column_title') }} {{ mb_strtoupper($language) }}</label><input class="form-control" type="text" name="columns[__COLUMN__][title][{{ $language }}]"></div></div>
                @endforeach
                <div class="col-md-2"><div class="form-group"><label>{{ trans('admin.catalog_menu_order') }}</label><input class="form-control" type="number" min="0" max="999" name="columns[__COLUMN__][sort_order]" value="__COLUMN__"></div></div>
            </div>
            <div data-items-container></div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-item>{{ trans('admin.catalog_menu_add_item') }}</button>
        </div>
    </template>

    <template id="catalog-menu-item-template">
        <div class="bg-light rounded p-3 mb-3 catalog-menu-item" data-item-index="__ITEM__">
            <div class="d-flex justify-content-between align-items-center mb-2"><strong class="small">{{ trans('admin.catalog_menu_item') }}</strong><button type="button" class="btn btn-sm btn-link text-danger" data-remove-item>{{ trans('admin.delete') }}</button></div>
            <div class="row">
                <div class="col-md-5"><div class="form-group"><label>{{ trans('admin.catalog_menu_category_target') }}</label><select class="form-control" name="columns[__COLUMN__][items][__ITEM__][category_id]"><option value="">{{ trans('admin.catalog_menu_custom_link') }}</option>@foreach($menuProductType->categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div></div>
                <div class="col-md-2"><div class="form-group"><label>{{ trans('admin.catalog_menu_order') }}</label><input class="form-control" type="number" min="0" max="999" name="columns[__COLUMN__][items][__ITEM__][sort_order]" value="__ITEM__"></div></div>
            </div>
            <div class="row">
                @foreach($availableLanguages as $language)
                    <div class="col-md-6"><div class="form-group"><label>{{ trans('admin.catalog_menu_custom_label') }} {{ mb_strtoupper($language) }}</label><input class="form-control" type="text" name="columns[__COLUMN__][items][__ITEM__][label][{{ $language }}]"></div><div class="form-group mb-0"><label>URL {{ mb_strtoupper($language) }}</label><input class="form-control" type="text" name="columns[__COLUMN__][items][__ITEM__][url][{{ $language }}]" placeholder="/product-category/..."></div></div>
                @endforeach
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.querySelector('[data-columns-container]');
            const columnTemplate = document.getElementById('catalog-menu-column-template').innerHTML;
            const itemTemplate = document.getElementById('catalog-menu-item-template').innerHTML;

            const refreshColumnNumbers = () => {
                container.querySelectorAll('.catalog-menu-column').forEach((column, index) => {
                    const number = column.querySelector('[data-column-number]');
                    if (number) number.textContent = index + 1;
                });
            };

            document.querySelector('[data-add-column]').addEventListener('click', function () {
                const existingIndexes = Array.from(container.querySelectorAll('.catalog-menu-column'))
                    .map(column => Number(column.dataset.columnIndex));
                const columnIndex = existingIndexes.length ? Math.max(...existingIndexes) + 1 : 0;
                container.insertAdjacentHTML('beforeend', columnTemplate.replaceAll('__COLUMN__', columnIndex));
                refreshColumnNumbers();
            });

            container.addEventListener('click', function (event) {
                const removeColumn = event.target.closest('[data-remove-column]');
                if (removeColumn) {
                    removeColumn.closest('.catalog-menu-column').remove();
                    refreshColumnNumbers();
                    return;
                }

                const removeItem = event.target.closest('[data-remove-item]');
                if (removeItem) {
                    removeItem.closest('.catalog-menu-item').remove();
                    return;
                }

                const addItem = event.target.closest('[data-add-item]');
                if (!addItem) return;

                const column = addItem.closest('.catalog-menu-column');
                const columnIndex = column.dataset.columnIndex;
                const itemsContainer = column.querySelector('[data-items-container]');
                const existingIndexes = Array.from(itemsContainer.querySelectorAll('.catalog-menu-item'))
                    .map(item => Number(item.dataset.itemIndex));
                const itemIndex = existingIndexes.length ? Math.max(...existingIndexes) + 1 : 0;
                const html = itemTemplate
                    .replaceAll('__COLUMN__', columnIndex)
                    .replaceAll('__ITEM__', itemIndex);
                itemsContainer.insertAdjacentHTML('beforeend', html);
            });

            refreshColumnNumbers();
        });
    </script>
@endpush
