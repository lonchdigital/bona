@props([
    'productTypes',
])

@php
    $menuTypes = $productTypes->values();
    $locale = app()->getLocale();
    $translatedValue = static function ($values) use ($locale) {
        if (!is_array($values)) {
            return trim((string) $values);
        }

        $localized = trim((string) ($values[$locale] ?? ''));

        if ($localized !== '') {
            return $localized;
        }

        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->first(fn ($value) => $value !== '') ?? '';
    };
@endphp

<div class="bona-mega" id="bona-catalog-menu" data-mega-menu>
    <div class="bona-shell bona-mega__inner">
        <div class="bona-mega__aside" role="tablist" aria-label="{{ trans('base.storefront_catalog') }}">
            @forelse($menuTypes as $productType)
                <button
                    class="bona-mega__tab{{ $loop->first ? ' is-active' : '' }}"
                    id="bona-mega-tab-{{ $productType->id }}"
                    type="button"
                    role="tab"
                    aria-controls="bona-mega-panel-{{ $productType->id }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    tabindex="{{ $loop->first ? '0' : '-1' }}"
                    data-mega-tab="{{ $productType->id }}"
                >
                    {{ $productType->name }}
                </button>
            @empty
                <span class="bona-mega__empty">{{ trans('base.storefront_catalog_empty') }}</span>
            @endforelse

            <a class="bona-mega__all" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}">
                {{ trans('base.all_products') }}
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="bona-mega__panels">
            @foreach($menuTypes as $productType)
                @php
                    $categories = $productType->categories->values();
                    $categoryLookup = $categories->keyBy('id');
                    $configuration = $productType->catalogMenuConfiguration;
                    $configuredCardIds = $configuration?->cards;
                    $cardCategories = is_array($configuredCardIds)
                        ? collect($configuredCardIds)->map(fn ($id) => $categoryLookup->get((int) $id))->filter()->values()
                        : $categories->take(5);
                    $menuColumns = $configuration?->columns;

                    if (!is_array($menuColumns)) {
                        $remainingCategories = $categories->skip(5)->values();
                        $menuColumns = $remainingCategories->isEmpty() ? [] : [[
                            'title' => ['uk' => 'Інші категорії', 'ru' => 'Другие категории'],
                            'items' => $remainingCategories->map(fn ($category) => [
                                'category_id' => $category->id,
                            ])->all(),
                        ]];
                    }

                    $typeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', [
                        'productTypeSlug' => $productType->slug,
                    ]);
                @endphp
                <section
                    class="bona-mega__panel{{ $loop->first ? ' is-active' : '' }}"
                    id="bona-mega-panel-{{ $productType->id }}"
                    role="tabpanel"
                    aria-labelledby="bona-mega-tab-{{ $productType->id }}"
                    data-mega-panel="{{ $productType->id }}"
                    @if(!$loop->first) hidden @endif
                >
                    <div class="bona-mega__heading">
                        <div>
                            <span>{{ trans('base.storefront_collection') }}</span>
                            <h2>{{ $productType->name }}</h2>
                        </div>
                        <a href="{{ $typeUrl }}">{{ trans('base.storefront_view_all') }} <span aria-hidden="true">→</span></a>
                    </div>

                    @if($cardCategories->isNotEmpty() || count($menuColumns) > 0)
                        @if($cardCategories->isNotEmpty())
                        <div class="bona-mega__cards">
                            @foreach($cardCategories as $category)
                                <a class="bona-mega-card" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                                    'productTypeSlug' => $productType->slug,
                                    'categorySlug' => $category->slug,
                                ]) }}">
                                    <span class="bona-mega-card__image">
                                        @if($category->image_url)
                                            <img src="{{ $category->image_url }}" alt="" loading="lazy" decoding="async" width="320" height="240">
                                        @else
                                            <span aria-hidden="true">BONA</span>
                                        @endif
                                    </span>
                                    <span>{{ $category->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        @endif

                        @if(count($menuColumns) > 0)
                            <div class="bona-mega__columns" aria-label="{{ trans('base.storefront_more_categories') }}">
                                @foreach(collect($menuColumns)->sortBy('sort_order') as $column)
                                    @php $columnTitle = $translatedValue($column['title'] ?? []); @endphp
                                    <div class="bona-mega__column">
                                        @if($columnTitle !== '')
                                            <h3>{{ $columnTitle }}</h3>
                                        @endif
                                        <div class="bona-mega__column-links">
                                            @foreach(collect($column['items'] ?? [])->sortBy('sort_order') as $item)
                                                @php
                                                    $category = !empty($item['category_id'])
                                                        ? $categoryLookup->get((int) $item['category_id'])
                                                        : null;
                                                    $itemLabel = $category?->name ?? $translatedValue($item['label'] ?? []);
                                                    $itemUrl = $category
                                                        ? App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                                                            'productTypeSlug' => $productType->slug,
                                                            'categorySlug' => $category->slug,
                                                        ])
                                                        : app(App\Services\CatalogMenu\CatalogMenuService::class)->resolveStorefrontUrl(
                                                            $translatedValue($item['url'] ?? []),
                                                            $productType->slug,
                                                            $locale,
                                                        );
                                                @endphp
                                                @if($itemLabel !== '' && $itemUrl !== '')
                                                    <a href="{{ $itemUrl }}">{{ $itemLabel }}</a>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <a class="bona-mega__type-card" href="{{ $typeUrl }}">
                            @if($productType->menu_image_url)
                                <img src="{{ $productType->menu_image_url }}" alt="" loading="lazy" decoding="async" width="320" height="240">
                            @else
                                <span class="bona-mega__type-placeholder" aria-hidden="true">BONA</span>
                            @endif
                            <span>
                                <small>{{ trans('base.storefront_open_catalog') }}</small>
                                <strong>{{ $productType->name }}</strong>
                            </span>
                            <b aria-hidden="true">→</b>
                        </a>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</div>
