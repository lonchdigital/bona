<?php

namespace App\Services\Catalog;

use App\Models\Color;
use App\Models\ProductType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

final class CatalogColorUrlService
{
    /**
     * Legacy colour pages used numeric ids while the current catalogue filter
     * contract uses stable slugs. Accept both so every historical URL can be
     * moved to one canonical catalogue address.
     */
    public function find(string|int $identifier): ?Color
    {
        $identifier = trim((string) $identifier);

        if ($identifier === '') {
            return null;
        }

        $query = Color::query();

        if (ctype_digit($identifier)) {
            return $query->whereKey((int) $identifier)->first()
                ?? Color::query()->where('slug', $identifier)->first();
        }

        return $query->where('slug', $identifier)->first();
    }

    public function findOrFail(string|int $identifier): Color
    {
        return $this->find($identifier) ?? throw (new ModelNotFoundException)->setModel(Color::class, [$identifier]);
    }

    public function productTypeFilterUrl(
        ProductType|string $productType,
        Color $color,
        ?string $locale = null,
    ): string {
        return $this->localizedRoute('store.catalog.filter.page', [
            'productTypeSlug' => $productType instanceof ProductType ? $productType->slug : $productType,
            'catalogFiltersString' => 'color='.$color->slug,
        ], $locale);
    }

    public function allProductsFilterUrl(Color $color, ?string $locale = null): string
    {
        return $this->localizedRoute('store.all-products.filter.page', [
            'catalogFiltersString' => 'color='.$color->slug,
        ], $locale);
    }

    /**
     * Only a lone colour facet is an SEO landing page. Sorting and pagination
     * remain variants of that landing page; any additional facet canonicalizes
     * through the ordinary catalogue rules to avoid indexable filter sprawl.
     */
    public function landingColor(?array $filters, Collection $availableColors): ?Color
    {
        $meaningfulFilters = collect($filters ?? [])->except(['page', 'sort_by']);

        if ($meaningfulFilters->keys()->all() !== ['color']) {
            return null;
        }

        $colorFilter = $meaningfulFilters->get('color');

        if (is_array($colorFilter)) {
            if (count($colorFilter) !== 1) {
                return null;
            }

            $colorFilter = reset($colorFilter);
        }

        if (! is_string($colorFilter) || $colorFilter === '') {
            return null;
        }

        return $availableColors->first(
            fn (Color $color): bool => hash_equals((string) $color->slug, $colorFilter),
        );
    }

    /** @param array<string, mixed> $parameters */
    private function localizedRoute(string $routeName, array $parameters, ?string $locale): string
    {
        $locale ??= app()->getLocale();

        if ($locale === config('app.fallback_locale')) {
            return route($routeName, $parameters, false);
        }

        return route('localized.'.$routeName, ['lang' => $locale, ...$parameters], false);
    }
}
