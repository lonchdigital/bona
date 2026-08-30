<?php

namespace App\Services\CatalogMenu;

use App\Models\CatalogMenuConfiguration;
use App\Models\ProductType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CatalogMenuService
{
    public const CACHE_KEY = 'catalogMenuNavigation';

    public function getAdminProductTypes(): Collection
    {
        return ProductType::query()
            ->with(['catalogMenuConfiguration', 'categories'])
            ->orderByRaw('CASE WHEN sort_order > 0 THEN 0 ELSE 1 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
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
            ->map(function (array $column, int $columnIndex) use ($categoryIds) {
                $items = collect($column['items'] ?? [])
                    ->map(function (array $item, int $itemIndex) use ($categoryIds) {
                        $categoryId = isset($item['category_id']) && $item['category_id'] !== ''
                            ? (int) $item['category_id']
                            : null;

                        if ($categoryId !== null && ! $categoryIds->contains($categoryId)) {
                            $categoryId = null;
                        }

                        return [
                            'category_id' => $categoryId,
                            'label' => $this->normalizeTranslations($item['label'] ?? []),
                            'url' => $this->normalizeTranslations($item['url'] ?? []),
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
}
