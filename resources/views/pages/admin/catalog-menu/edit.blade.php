@extends('layouts.admin-main')

@php
    $configuredCards = old('cards', []);
    $savedCardIds = collect($menuConfiguration?->cards ?? $menuProductType->categories->take(5)->pluck('id')->all())
        ->map(fn ($id) => (int) $id)
        ->values();
    $savedCardOrder = $savedCardIds->flip();
    $orderedCategories = $menuProductType->categories->sortBy(function ($category) use ($configuredCards, $savedCardIds, $savedCardOrder) {
        $oldCard = $configuredCards[$category->id] ?? null;
        $isEnabled = $oldCard !== null
            ? (bool) ($oldCard['enabled'] ?? false)
            : $savedCardIds->contains($category->id);
        $order = $oldCard['sort_order'] ?? $savedCardOrder->get($category->id, PHP_INT_MAX);

        return [$isEnabled ? 0 : 1, (int) $order, $category->id];
    })->values();
    $selectedCardCount = $orderedCategories->filter(function ($category) use ($configuredCards, $savedCardIds) {
        $oldCard = $configuredCards[$category->id] ?? null;

        return $oldCard !== null
            ? (bool) ($oldCard['enabled'] ?? false)
            : $savedCardIds->contains($category->id);
    })->count();
    $columns = collect(old('columns', $menuConfiguration?->columns ?? []))
        ->sortBy(fn ($column, $index) => [(int) data_get($column, 'sort_order', $index), $index]);
    $initialMenuLocale = collect($errors->keys())->contains(fn ($key) => str_contains($key, '.ru'))
        ? 'ru'
        : (in_array(app()->getLocale(), ['uk', 'ru'], true) ? app()->getLocale() : 'uk');
@endphp

@section('content')
    <div
        class="container-fluid catalog-menu-admin"
        data-menu-builder
        data-default-locale="{{ $initialMenuLocale }}"
        data-force-default-locale="{{ $errors->any() ? 'true' : 'false' }}"
        data-unsaved-warning="{{ trans('admin.menu_unsaved_warning') }}"
    >
        <div class="row justify-content-center">
            <div class="col-12">
                <a class="catalog-menu-back-link" href="{{ route('admin.catalog-menu.page') }}">
                    <span class="fe fe-arrow-left" aria-hidden="true"></span>{{ trans('admin.catalog_menu_back_to_structure') }}
                </a>

                <header class="catalog-menu-page-header catalog-menu-page-header--editor">
                    <div>
                        <p class="catalog-menu-page-header__eyebrow">{{ trans('admin.catalog_menu_tab_path', ['TYPE' => $menuProductType->name]) }}</p>
                        <h2 class="page-title mb-2">{{ trans('admin.catalog_menu_content_title') }}</h2>
                        <p class="card-text mb-0">{{ trans('admin.catalog_menu_content_description') }}</p>
                    </div>
                    @include('pages.admin.catalog-menu.partials.language-switch')
                </header>

                @if(Session::has('success'))
                    <div class="alert alert-success mt-3" role="status">{{ Session::get('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mt-3" role="alert">
                        <strong>{{ trans('admin.catalog_menu_validation_error') }}</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.catalog-menu.edit', $menuProductType) }}" data-menu-form>
                    @csrf

                    <div class="catalog-menu-context-note">
                        <span class="fe fe-eye" aria-hidden="true"></span>
                        <p>{{ trans('admin.catalog_menu_content_context', ['TYPE' => $menuProductType->name]) }}</p>
                    </div>

                    <section class="catalog-menu-panel" aria-labelledby="visual-cards-title">
                        <div class="catalog-menu-panel__header">
                            <div>
                                <span class="catalog-menu-panel__step">01</span>
                                <div>
                                    <h3 id="visual-cards-title">{{ trans('admin.catalog_menu_visual_cards') }}</h3>
                                    <p>{{ trans('admin.catalog_menu_visual_cards_hint') }}</p>
                                </div>
                            </div>
                            <span
                                class="catalog-menu-selection-count"
                                data-card-selected-count
                                data-label="{{ trans('admin.catalog_menu_selected_short') }}"
                            >{{ trans('admin.catalog_menu_selected_short') }}: {{ $selectedCardCount }}</span>
                        </div>

                        @if($menuProductType->categories->isEmpty())
                            <div class="catalog-menu-list-empty catalog-menu-list-empty--standalone">
                                <span class="fe fe-image" aria-hidden="true"></span>
                                <p>{{ trans('admin.catalog_menu_no_categories') }}</p>
                            </div>
                        @else
                            <div class="catalog-menu-card-list" data-menu-sort-list data-visual-card-list>
                                @foreach($orderedCategories as $category)
                                    @php
                                        $oldCard = $configuredCards[$category->id] ?? null;
                                        $isEnabled = $oldCard !== null
                                            ? (bool) ($oldCard['enabled'] ?? false)
                                            : $savedCardIds->contains($category->id);
                                        $order = $oldCard['sort_order'] ?? $savedCardOrder->get($category->id, $loop->index);
                                    @endphp
                                    <article
                                        class="catalog-menu-category-card {{ $isEnabled ? '' : 'is-muted' }}"
                                        data-menu-sort-item
                                        data-menu-visibility-item
                                    >
                                        <input type="hidden" name="cards[{{ $category->id }}][sort_order]" value="{{ $order }}" data-menu-sort-order>

                                        @include('pages.admin.catalog-menu.partials.drag-handle', [
                                            'dragLabel' => trans('admin.menu_drag_item', ['ITEM' => $category->name]),
                                        ])

                                        <span class="catalog-menu-category-card__image">
                                            @if($category->image_url)
                                                <img src="{{ $category->image_url }}" alt="" width="68" height="54" loading="lazy">
                                            @else
                                                <span class="fe fe-image" aria-hidden="true"></span>
                                            @endif
                                        </span>

                                        <span class="catalog-menu-category-card__name">
                                            <strong data-menu-locale-content="uk" @if($initialMenuLocale !== 'uk') hidden @endif>{{ $category->getTranslation('name', 'uk') }}</strong>
                                            <strong data-menu-locale-content="ru" @if($initialMenuLocale !== 'ru') hidden @endif>{{ $category->getTranslation('name', 'ru') }}</strong>
                                            <small>/{{ $category->slug }}</small>
                                        </span>

                                        <label class="catalog-menu-switch">
                                            <input type="hidden" name="cards[{{ $category->id }}][enabled]" value="0">
                                            <input
                                                type="checkbox"
                                                name="cards[{{ $category->id }}][enabled]"
                                                value="1"
                                                data-menu-visibility-toggle
                                                data-visual-card-toggle
                                                @checked($isEnabled)
                                            >
                                            <span class="catalog-menu-switch__track" aria-hidden="true"><span></span></span>
                                            <span class="catalog-menu-switch__label">{{ trans('admin.catalog_menu_card_enabled') }}</span>
                                        </label>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="catalog-menu-panel" aria-labelledby="text-columns-title">
                        <div class="catalog-menu-panel__header">
                            <div>
                                <span class="catalog-menu-panel__step">02</span>
                                <div>
                                    <h3 id="text-columns-title">{{ trans('admin.catalog_menu_text_columns') }}</h3>
                                    <p>{{ trans('admin.catalog_menu_text_columns_hint') }}</p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-dark" data-add-column>
                                <span class="fe fe-plus mr-1" aria-hidden="true"></span>{{ trans('admin.catalog_menu_add_column') }}
                            </button>
                        </div>

                        <div class="catalog-menu-columns" data-columns-container data-menu-sort-list>
                            @foreach($columns as $columnIndex => $column)
                                @include('pages.admin.catalog-menu.partials.catalog-column', [
                                    'columnIndex' => $columnIndex,
                                    'column' => $column,
                                    'menuProductType' => $menuProductType,
                                ])
                            @endforeach

                            <div class="catalog-menu-list-empty catalog-menu-list-empty--standalone" data-menu-list-empty>
                                <span class="fe fe-columns" aria-hidden="true"></span>
                                <p>{{ trans('admin.catalog_menu_columns_empty') }}</p>
                                <button type="button" class="btn btn-sm btn-outline-dark" data-add-column>
                                    <span class="fe fe-plus mr-1" aria-hidden="true"></span>{{ trans('admin.catalog_menu_add_column') }}
                                </button>
                            </div>
                        </div>
                    </section>

                    <div class="catalog-menu-savebar" data-menu-savebar>
                        <a href="{{ route('admin.catalog-menu.page') }}" class="btn btn-link text-muted">{{ trans('admin.back') }}</a>
                        <span
                            class="catalog-menu-savebar__status"
                            data-menu-dirty-status
                            data-clean="{{ trans('admin.menu_changes_saved') }}"
                            data-dirty="{{ trans('admin.menu_changes_unsaved') }}"
                            data-saving="{{ trans('admin.menu_changes_saving') }}"
                        >{{ trans('admin.menu_changes_saved') }}</span>
                        <button type="submit" class="btn btn-dark">
                            <span class="fe fe-check mr-1" aria-hidden="true"></span>{{ trans('admin.menu_save_changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <template id="catalog-menu-column-template">
            @include('pages.admin.catalog-menu.partials.catalog-column', [
                'columnIndex' => '__COLUMN__',
                'column' => [
                    'title' => ['uk' => '', 'ru' => ''],
                    'sort_order' => 0,
                    'items' => [],
                ],
                'menuProductType' => $menuProductType,
            ])
        </template>

        <template id="catalog-menu-item-template">
            @include('pages.admin.catalog-menu.partials.catalog-column-item', [
                'columnIndex' => '__COLUMN__',
                'itemIndex' => '__ITEM__',
                'item' => [
                    'category_id' => null,
                    'label' => ['uk' => '', 'ru' => ''],
                    'url' => ['uk' => '', 'ru' => ''],
                    'sort_order' => 0,
                ],
                'menuProductType' => $menuProductType,
            ])
        </template>
    </div>
@endsection

@push('scripts')
    <script src="/static-admin/js/catalog-menu-builder.js?v={{ filemtime(public_path('static-admin/js/catalog-menu-builder.js')) }}"></script>
@endpush
