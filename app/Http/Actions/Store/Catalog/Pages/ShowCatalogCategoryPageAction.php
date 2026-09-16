<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\Category;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductFiltersService;
use App\Services\Product\ProductService;
use App\Services\ProductCategory\CategoryService;
// use App\Services\Seogen\SeogenService;
// use App\Services\WishList\WishListService;
use App\Support\LastModified;

class ShowCatalogCategoryPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        Category $category,
        CatalogFilterRequest $request
    ) {
        $productType->load(['fields', 'fields.options']);

        // get services from service container
        //        $categoryService = app()->make(CategoryService::class);
        $catalogService = app()->make(ProductFiltersService::class);
        $colorService = app()->make(ColorService::class);
        $countryService = app()->make(CountryService::class);
        $brandService = app()->make(BrandService::class);
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);

        $filtersData = $request->toDTO();

        $baseCurrency = $currencyService->getBaseCurrency();
        $colors = $colorService->getAvailableColorsByProductTypeAndCategory($productType, $category);
        $countries = $countryService->getAvailableCountriesByProductType($productType);
        $brands = $brandService->getAvailableBrandsByProductType($productType, $category);

        $selectedFiltersOptions = $catalogService->getOptionsByFilterData(
            $productType,
            $filtersData->filters,
            $baseCurrency,
            $colors,
            $countries,
            $brands,
        );

        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getProductsByTypePaginatedByCategory(
            $productType,
            $category,
            $filtersData,
            (int) config('domain.store_catalog_items_per_page'),
            $page,
        );

        // A category requested under a type it does not belong to renders an
        // empty duplicate. Send visitors and crawlers to its real address
        // instead of a 404, but only when this type genuinely has nothing to
        // show: products assigned to several types keep their extra URLs.
        if (
            $productsPaginated->total() === 0
            && $category->product_type_id !== null
            && (int) $category->product_type_id !== (int) $productType->id
            && $category->productType !== null
        ) {
            $route = request()->route();

            return redirect()->route(
                (string) $route->getName(),
                array_merge($route->originalParameters(), ['productTypeSlug' => $category->productType->slug], request()->query()),
                301,
            );
        }

        LastModified::set($category->updated_at);

        return view('pages.store.catalog-category', [
            'filters' => $catalogService->getFiltersByProductType($productType, $category),
            'filtersData' => $filtersData->filters,
            'productStatuses' => $catalogService->getAvailableProductStatuses($productType, $category),
            'selectedFiltersOptions' => $selectedFiltersOptions,
            'productType' => $productType,
            //            'categories' => $categoryService->getProductCategories($productType),
            'colors' => $colors,
            'countries' => $countries,
            'baseCurrency' => $baseCurrency,
            'selectedCategory' => $category,
            'productsPaginated' => $productsPaginated,
            'productsMaxPrice' => $productService->getProductsMaxPriceByCategory($productType, $category),
            'faqs' => $productService->getProductTypeFaqs($productType->slug),
            'seoText' => $productService->getProductTypeSeoTextByLanguage($category->slug, app()->getLocale()),
        ]);
    }
}
