<?php

namespace App\Services\CatalogMenu;

use App\Models\ApplicationConfig;
use App\Models\CatalogMenuConfiguration;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatalogMenuService
{
    public const CACHE_KEY = 'catalogMenuNavigation';

    public const FOOTER_NAVIGATION_CONFIG = 'footerNavigation';

    public const FOOTER_CATEGORIES_CONFIG = 'footerCategories';

    public function getAdminProductTypes(): Collection
    {
        return ProductType::query()
            ->with(['catalogMenuConfiguration', 'categories'])
            ->orderByRaw('CASE WHEN sort_order > 0 THEN 0 ELSE 1 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getStorefrontProductTypes(): Collection
    {
        return Cache::remember(self::CACHE_KEY, 43200, function () {
            if (! CatalogMenuConfiguration::query()->exists()) {
                $productTypes = ProductType::query()
                    ->with(['catalogMenuConfiguration', 'categories'])
                    ->where('sort_order', '>', 0)
                    ->orderBy('sort_order')
                    ->get();
            } else {
                $productTypes = ProductType::query()
                    ->with(['catalogMenuConfiguration', 'categories'])
                    ->whereHas('catalogMenuConfiguration', function ($query) {
                        $query->where('is_visible', true)
                            ->orWhere('show_in_header', true);
                    })
                    ->orderBy('id')
                    ->get();
            }

            return $productTypes->each(function (ProductType $productType): void {
                $productType->setAttribute('menu_image_url', $this->resolveMenuImageUrl($productType));
            });
        });
    }

    private function resolveMenuImageUrl(ProductType $productType): ?string
    {
        $disk = Storage::disk(config('app.images_disk_default'));

        if (filled($productType->image_path) && $disk->exists($productType->image_path)) {
            return $disk->url($productType->image_path);
        }

        $candidates = Product::query()
            ->select(['id', 'preview_image_path', 'main_image_path'])
            ->where('is_active', true)
            ->where(function ($query) use ($productType): void {
                $query->where('product_type_id', $productType->id)
                    ->orWhereHas('productTypes', function ($query) use ($productType): void {
                        $query->where('product_types.id', $productType->id);
                    });
            })
            ->where(function ($query): void {
                $query->whereNotNull('preview_image_path')
                    ->orWhereNotNull('main_image_path');
            })
            ->orderByDesc('orders_count')
            ->orderBy('id')
            ->limit(20)
            ->get();

        foreach ($candidates as $product) {
            foreach ([$product->preview_image_path, $product->main_image_path] as $path) {
                if (filled($path) && $disk->exists($path)) {
                    return $disk->url($path);
                }
            }
        }

        return null;
    }

    public function updateOverview(array $configurations): void
    {
        $productTypeIds = ProductType::query()->pluck('id')->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($configurations, $productTypeIds) {
            foreach ($configurations as $productTypeId => $configuration) {
                $productTypeId = (int) $productTypeId;

                if (! in_array($productTypeId, $productTypeIds, true)) {
                    continue;
                }

                CatalogMenuConfiguration::query()->updateOrCreate(
                    ['product_type_id' => $productTypeId],
                    [
                        'is_visible' => (bool) ($configuration['is_visible'] ?? false),
                        'sort_order' => max(0, (int) ($configuration['sort_order'] ?? 0)),
                        'show_in_header' => (bool) ($configuration['show_in_header'] ?? false),
                        'header_order' => max(0, (int) ($configuration['header_order'] ?? 0)),
                    ],
                );
            }
        });

        $this->forgetCache();
    }

    public function updateContent(ProductType $productType, array $cards, array $columns): void
    {
        $categoryIds = $productType->categories()->pluck('id')->map(fn ($id) => (int) $id);

        $normalizedCards = collect($cards)
            ->filter(fn (array $card) => (bool) ($card['enabled'] ?? false))
            ->map(fn (array $card, $categoryId) => [
                'category_id' => (int) $categoryId,
                'sort_order' => max(0, (int) ($card['sort_order'] ?? 0)),
            ])
            ->filter(fn (array $card) => $categoryIds->contains($card['category_id']))
            ->sortBy([['sort_order', 'asc'], ['category_id', 'asc']])
            ->pluck('category_id')
            ->values()
            ->all();

        $normalizedColumns = collect($columns)
            ->map(function (array $column, int $columnIndex) use ($categoryIds, $productType) {
                $items = collect($column['items'] ?? [])
                    ->map(function (array $item, int $itemIndex) use ($categoryIds, $productType) {
                        $categoryId = isset($item['category_id']) && $item['category_id'] !== ''
                            ? (int) $item['category_id']
                            : null;

                        if ($categoryId !== null && ! $categoryIds->contains($categoryId)) {
                            $categoryId = null;
                        }

                        $urls = $this->normalizeTranslations($item['url'] ?? []);

                        foreach (['uk', 'ru'] as $locale) {
                            $urls[$locale] = $this->resolveStorefrontUrl(
                                $urls[$locale],
                                $productType->slug,
                                $locale,
                            );
                        }

                        return [
                            'category_id' => $categoryId,
                            'label' => $this->normalizeTranslations($item['label'] ?? []),
                            'url' => $urls,
                            'sort_order' => max(0, (int) ($item['sort_order'] ?? $itemIndex)),
                        ];
                    })
                    ->filter(fn (array $item) => $item['category_id'] !== null || $this->hasTranslation($item['label']))
                    ->sortBy('sort_order')
                    ->values()
                    ->all();

                return [
                    'title' => $this->normalizeTranslations($column['title'] ?? []),
                    'sort_order' => max(0, (int) ($column['sort_order'] ?? $columnIndex)),
                    'items' => $items,
                ];
            })
            ->filter(fn (array $column) => $this->hasTranslation($column['title']) || $column['items'] !== [])
            ->sortBy('sort_order')
            ->values()
            ->all();

        CatalogMenuConfiguration::query()->updateOrCreate(
            ['product_type_id' => $productType->id],
            [
                'cards' => $normalizedCards,
                'columns' => $normalizedColumns,
            ],
        );

        $this->forgetCache();
    }

    /**
     * @return array{navigation: array<int, array<string, mixed>>, categories: array<int, array<string, mixed>>}
     */
    public function getAdminFooterMenus(Collection $productTypes): array
    {
        $storedMenus = ApplicationConfig::query()
            ->whereIn('config_name', [self::FOOTER_NAVIGATION_CONFIG, self::FOOTER_CATEGORIES_CONFIG])
            ->get()
            ->keyBy('config_name');

        $navigation = $storedMenus->get(self::FOOTER_NAVIGATION_CONFIG)?->config_data;
        $categories = $storedMenus->get(self::FOOTER_CATEGORIES_CONFIG)?->config_data;

        return [
            'navigation' => is_array($navigation) ? $navigation : $this->defaultFooterNavigation(),
            'categories' => is_array($categories) ? $categories : $this->defaultFooterCategories($productTypes),
        ];
    }

    /**
     * @return array{navigation: array<int, array{label: string, url: string}>, categories: array<int, array{label: string, url: string}>}
     */
    public function getStorefrontFooterMenus(array $options, Collection $productTypes, string $locale): array
    {
        $navigation = data_get($options, self::FOOTER_NAVIGATION_CONFIG);
        $categories = data_get($options, self::FOOTER_CATEGORIES_CONFIG);

        return [
            'navigation' => $this->localizeFooterItems(
                is_array($navigation) ? $navigation : $this->defaultFooterNavigation(),
                $locale,
            ),
            'categories' => $this->localizeFooterItems(
                is_array($categories) ? $categories : $this->defaultFooterCategories($productTypes),
                $locale,
            ),
        ];
    }

    public function updateFooterMenus(array $navigation, array $categories): void
    {
        DB::transaction(function () use ($navigation, $categories) {
            ApplicationConfig::query()->updateOrCreate(
                ['config_name' => self::FOOTER_NAVIGATION_CONFIG],
                ['config_data' => $this->normalizeFooterItems($navigation)],
            );
            ApplicationConfig::query()->updateOrCreate(
                ['config_name' => self::FOOTER_CATEGORIES_CONFIG],
                ['config_data' => $this->normalizeFooterItems($categories)],
            );
        });

        Cache::forget('applicationGlobalOptions');
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('sortedProductTypes');
    }

    private function normalizeTranslations(array $translations): array
    {
        return collect(['uk', 'ru'])
            ->mapWithKeys(fn (string $locale) => [$locale => trim((string) ($translations[$locale] ?? ''))])
            ->all();
    }

    private function hasTranslation(array $translations): bool
    {
        return collect($translations)->contains(fn ($value) => trim((string) $value) !== '');
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFooterItems(array $items): array
    {
        return collect($items)
            ->map(fn (array $item, int $index) => [
                'label' => $this->normalizeTranslations($item['label'] ?? []),
                'url' => $this->normalizeTranslations($item['url'] ?? []),
                'is_visible' => (bool) ($item['is_visible'] ?? false),
                'sort_order' => max(0, (int) ($item['sort_order'] ?? $index)),
            ])
            ->filter(fn (array $item) => $this->hasTranslation($item['label']) && $this->hasTranslation($item['url']))
            ->sortBy('sort_order')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{label: string, url: string}>
     */
    private function localizeFooterItems(array $items, string $locale): array
    {
        return collect($items)
            ->filter(fn (array $item) => (bool) ($item['is_visible'] ?? true))
            ->sortBy('sort_order')
            ->map(function (array $item) use ($locale): array {
                $label = trim((string) data_get($item, "label.$locale"));
                $url = trim((string) data_get($item, "url.$locale"));

                if ($label === '') {
                    $label = trim((string) data_get($item, 'label.uk'));
                }

                if ($url === '') {
                    $url = trim((string) data_get($item, 'url.uk'));
                }

                return ['label' => $label, 'url' => $url];
            })
            ->filter(fn (array $item) => $item['label'] !== '' && $item['url'] !== '')
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function defaultFooterNavigation(): array
    {
        $definitions = [
            ['route' => 'store.about-us', 'translation' => 'base.about_us'],
            ['route' => 'store.delivery-info', 'translation' => 'base.delivery'],
            ['route' => 'store.services', 'translation' => 'base.services'],
            ['route' => 'store.works.page', 'translation' => 'base.our_works'],
            ['route' => 'blog.main.page', 'translation' => 'base.blog'],
            ['route' => 'store.contacts', 'translation' => 'base.contacts'],
            ['route' => 'store.faq.page', 'translation' => 'base.faq'],
        ];

        return collect($definitions)
            ->map(fn (array $definition, int $index) => [
                'label' => $this->translatedValues($definition['translation']),
                'url' => $this->localizedRouteValues($definition['route']),
                'is_visible' => true,
                'sort_order' => $index,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultFooterCategories(Collection $productTypes): array
    {
        $hasMenuConfiguration = $productTypes->contains(
            fn (ProductType $productType) => $productType->catalogMenuConfiguration !== null,
        );

        $visibleTypes = $productTypes
            ->filter(function (ProductType $productType) use ($hasMenuConfiguration): bool {
                if (! $hasMenuConfiguration) {
                    return $productType->sort_order > 0;
                }

                return (bool) ($productType->catalogMenuConfiguration?->is_visible
                    || $productType->catalogMenuConfiguration?->show_in_header);
            })
            ->sortBy(function (ProductType $productType) use ($hasMenuConfiguration): array {
                if ($hasMenuConfiguration) {
                    return [
                        $productType->catalogMenuConfiguration?->sort_order ?? PHP_INT_MAX,
                        $productType->id,
                    ];
                }

                return [$productType->sort_order, $productType->id];
            })
            ->values();

        $items = $visibleTypes
            ->map(fn (ProductType $productType, int $index) => [
                'label' => collect(['uk', 'ru'])->mapWithKeys(fn (string $locale) => [
                    $locale => $productType->getTranslation('name', $locale),
                ])->all(),
                'url' => $this->localizedRouteValues('store.catalog.page', [
                    'productTypeSlug' => $productType->slug,
                ]),
                'is_visible' => true,
                'sort_order' => $index,
            ]);

        $items->push([
            'label' => $this->translatedValues('shop.door_handles'),
            'url' => $this->localizedRouteValues('store.catalog-category.page', [
                'productTypeSlug' => 'aksessuar',
                'categorySlug' => 'dverni-rucky',
            ]),
            'is_visible' => true,
            'sort_order' => $items->count(),
        ]);

        return $items->all();
    }

    /** @return array{uk: string, ru: string} */
    private function translatedValues(string $key): array
    {
        return collect(['uk', 'ru'])
            ->mapWithKeys(fn (string $locale) => [$locale => trans($key, [], $locale)])
            ->all();
    }

    /** @return array{uk: string, ru: string} */
    private function localizedRouteValues(string $routeName, array $parameters = []): array
    {
        return collect(['uk', 'ru'])
            ->mapWithKeys(function (string $locale) use ($routeName, $parameters): array {
                if ($locale === config('app.fallback_locale')) {
                    return [$locale => route($routeName, $parameters, false)];
                }

                return [$locale => route(
                    'localized.'.$routeName,
                    ['lang' => $locale, ...$parameters],
                    false,
                )];
            })
            ->all();
    }

    public function resolveStorefrontUrl(string $url, string $productTypeSlug, string $locale): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! preg_match('#^/(?:ru/)?brands/([a-z0-9-]+)/?$#i', $path, $matches)) {
            return $url;
        }

        $prefix = $locale === 'ru' ? '/ru' : '';

        return $prefix.'/product-category/'.$productTypeSlug.'/manufacturer/'.mb_strtolower($matches[1]);
    }
}
