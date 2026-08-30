<?php

namespace App\Services\Search;

use App\Helpers\MultiLangRoute;
use App\Models\Product;
use App\Models\ServicesPageSections;
use App\Services\Product\DTO\SearchProductDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StorefrontSearchService
{
    /**
     * @return array{products: Collection<int, Product>, services: Collection<int, array<string, mixed>>}
     */
    public function search(SearchProductDTO $request): array
    {
        $query = trim((string) $request->query);

        if (mb_strlen($query) < 3) {
            return [
                'products' => collect(),
                'services' => collect(),
            ];
        }

        return [
            'products' => $this->searchProducts($query),
            'services' => $this->searchServices($query),
        ];
    }

    /** @return Collection<int, Product> */
    private function searchProducts(string $query): Collection
    {
        return Product::query()
            ->with(['brand', 'productType'])
            ->where('is_active', true)
            ->where(function (Builder $builder) use ($query) {
                $this->whereTranslatedLike($builder, 'name', $query);
                $builder->orWhere('sku', 'like', '%'.$query.'%');
            })
            ->orderByAvailabilityStatus()
            ->limit(3)
            ->get();
    }

    /** @return Collection<int, array<string, mixed>> */
    private function searchServices(string $query): Collection
    {
        return ServicesPageSections::query()
            ->where(function (Builder $builder) use ($query) {
                $this->whereTranslatedLike($builder, 'title', $query);
                $builder->orWhere(function (Builder $descriptionQuery) use ($query) {
                    $this->whereTranslatedLike($descriptionQuery, 'description', $query);
                });
            })
            ->limit(2)
            ->get()
            ->map(function (ServicesPageSections $section) {
                $description = Str::of(strip_tags((string) $section->description))
                    ->squish()
                    ->limit(90)
                    ->toString();

                return [
                    'id' => $section->id,
                    'title' => $section->title,
                    'description' => $description,
                    'link' => MultiLangRoute::getMultiLangRoute('store.services').'#service-'.$section->id,
                ];
            });
    }

    private function whereTranslatedLike(Builder $builder, string $column, string $query): void
    {
        foreach (['uk', 'ru'] as $index => $locale) {
            $method = $index === 0 ? 'where' : 'orWhere';
            $builder->{$method}($column.'->'.$locale, 'like', '%'.$query.'%');
        }
    }
}
