<?php

namespace App\Services\Search;

use App\Helpers\MultiLangRoute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ServicesPageSections;
use App\Services\Product\DTO\SearchProductDTO;
use App\Support\Search\SearchTerm;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StorefrontSearchService
{
    private const PRODUCT_PREVIEW_LIMIT = 4;

    private const SERVICE_PREVIEW_LIMIT = 2;

    private const SUGGESTION_PREVIEW_LIMIT = 4;

    /**
     * @return array{
     *     query: string,
     *     products: Collection<int, Product>,
     *     product_total: int,
     *     services: Collection<int, array<string, mixed>>,
     *     service_total: int,
     *     suggestions: Collection<int, array<string, mixed>>,
     *     suggestion_total: int,
     *     full_results_url: string
     * }
     */
    public function search(SearchProductDTO $request): array
    {
        $query = trim((string) $request->query);
        $empty = [
            'query' => $query,
            'products' => collect(),
            'product_total' => 0,
            'services' => collect(),
            'service_total' => 0,
            'suggestions' => collect(),
            'suggestion_total' => 0,
            'full_results_url' => $this->fullResultsUrl($query),
        ];

        if (mb_strlen(SearchTerm::normalize($query)) < 3) {
            return $empty;
        }

        $productQuery = Product::query()->with(['brand', 'productType']);
        SearchTerm::applyToProducts($productQuery, $query);
        $productTotal = (clone $productQuery)->count();
        $products = $productQuery
            ->orderByAvailabilityStatus()
            ->orderByDesc('orders_count')
            ->orderBy('id')
            ->limit(self::PRODUCT_PREVIEW_LIMIT)
            ->get();

        [$services, $serviceTotal] = $this->searchServices($query);
        [$suggestions, $suggestionTotal] = $this->searchSuggestions($query);

        return [
            'query' => $query,
            'products' => $products,
            'product_total' => $productTotal,
            'services' => $services,
            'service_total' => $serviceTotal,
            'suggestions' => $suggestions,
            'suggestion_total' => $suggestionTotal,
            'full_results_url' => $this->fullResultsUrl($query),
        ];
    }

    /** @return array{Collection<int, array<string, mixed>>, int} */
    private function searchServices(string $query): array
    {
        $builder = ServicesPageSections::query();
        SearchTerm::applyToTranslatedColumns($builder, $query, ['title', 'description']);
        $total = (clone $builder)->count();

        $services = $builder
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(self::SERVICE_PREVIEW_LIMIT)
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
                    'link' => $section->slug
                        ? MultiLangRoute::getMultiLangRoute('store.service.page', ['serviceSlug' => $section->slug])
                        : MultiLangRoute::getMultiLangRoute('store.services').'#service-'.$section->id,
                ];
            });

        return [$services, $total];
    }

    /** @return array{Collection<int, array<string, mixed>>, int} */
    private function searchSuggestions(string $query): array
    {
        $typesQuery = ProductType::query();
        SearchTerm::applyToTranslatedColumns($typesQuery, $query, ['name']);

        $categoriesQuery = Category::query()->with('productType');
        SearchTerm::applyToTranslatedColumns($categoriesQuery, $query, ['name']);

        $brandsQuery = Brand::query();
        SearchTerm::applyToTranslatedColumns($brandsQuery, $query, ['name']);

        $total = (clone $typesQuery)->count() + (clone $categoriesQuery)->count() + (clone $brandsQuery)->count();

        $types = $typesQuery->orderBy('name->uk')->limit(self::SUGGESTION_PREVIEW_LIMIT)->get()->map(fn (ProductType $type) => [
            'id' => 'type-'.$type->id,
            'title' => $type->name,
            'meta' => trans('base.storefront_search_product_type'),
            'link' => MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $type->slug]),
        ]);

        $categories = $categoriesQuery->orderBy('name->uk')->limit(self::SUGGESTION_PREVIEW_LIMIT)->get()->map(fn (Category $category) => [
            'id' => 'category-'.$category->id,
            'title' => $category->name,
            'meta' => collect([trans('base.storefront_search_category'), $category->productType?->name])->filter()->join(' · '),
            'link' => MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                'productTypeSlug' => $category->productType->slug,
                'categorySlug' => $category->slug,
            ]),
        ]);

        $brands = $brandsQuery->orderBy('name->uk')->limit(self::SUGGESTION_PREVIEW_LIMIT)->get()->map(fn (Brand $brand) => [
            'id' => 'brand-'.$brand->id,
            'title' => $brand->name,
            'meta' => trans('base.storefront_search_brand'),
            'link' => MultiLangRoute::getMultiLangRoute('store.brand.page', ['brandSlug' => $brand->slug]),
        ]);

        return [
            $types->concat($categories)->concat($brands)->take(self::SUGGESTION_PREVIEW_LIMIT)->values(),
            $total,
        ];
    }

    private function fullResultsUrl(string $query): string
    {
        return MultiLangRoute::getMultiLangRoute('store.all-products.page').'?'.http_build_query(['query' => $query]);
    }
}
