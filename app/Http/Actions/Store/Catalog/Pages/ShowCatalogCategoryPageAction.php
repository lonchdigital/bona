<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\Category;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\ProductCategory\CategoryService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductFiltersService;
use App\Services\Product\ProductService;
//use App\Services\Seogen\SeogenService;
//use App\Services\WishList\WishListService;
use Abordage\LastModified\Facades\LastModified;

class ShowCatalogCategoryPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        Category $category,
        CatalogFilterRequest $request
    )
    {
        $productType->load(['fields', 'fields.options']);

        //get services from service container
//        $categoryService = app()->make(CategoryService::class);
        $catalogService = app()->make(ProductFiltersService::class);
        $colorService = app()->make(ColorService::class);
        $countryService = app()->make(CountryService::class);
        $brandService = app()->make(BrandService::class);
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);

        $filtersData = $request->toDTO();

        $baseCurrency = $currencyService->getBaseCurrency();
        $colors = $colorService->getAvailableColorsByProductType($productType);
        $countries = $countryService->getAvailableCountriesByProductType($productType);
        $brands = $brandService->getAvailableBrandsByProductType($productType);
        $brandsSortedByFirstLetter = $brandService->sortBrandsByFirstLetterByProductType($brands);

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
            $filtersData->filters['per_page'] ?? 24,
            $page,
        );


        LastModified::set($category->updated_at);

        $allFields = $productType->fields;
        $allFields->map(function ($field) use ($productType, $category) {
            $field->options = $field->optionsWithProductsInCategory($productType, $category);
            return $field;
        });

//        dd($category);

        return view('pages.store.catalog-category', [
            'filters' => $catalogService->getFiltersByProductType($productType),
            'filtersData' => $filtersData->filters,
            'selectedFiltersOptions' => $selectedFiltersOptions,
            'productType' => $productType,
//            'categories' => $categoryService->getProductCategories($productType),
            'colors' => $colors,
            'countries' => $countries,
            'brandsSortedByFirstLetter' => $brandsSortedByFirstLetter,
            'baseCurrency' => $baseCurrency,
            'selectedCategory' => $category,
            'productsPaginated' => $productsPaginated,
            'productsMaxPrice' => $productService->getProductsMaxPrice($productType),
            'faqs' => $productService->getProductTypeFaqs($productType->slug),
            'seoText' => $productService->getProductTypeSeoTextByLanguage($category->slug, app()->getLocale())
        ]);
    }
}
