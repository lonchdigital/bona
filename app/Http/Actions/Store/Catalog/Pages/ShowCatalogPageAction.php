<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\ProductCategory\CategoryService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductFiltersService;
use App\Services\Product\ProductService;
use App\Services\WishList\WishListService;

class ShowCatalogPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
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
        $wishListService = app()->make(WishListService::class);


//        dd(ProductService::class);

        $filtersData = $request->toDTO();

        $baseCurrency = $currencyService->getBaseCurrency();
        $colors = $colorService->getAvailableColorsByProductType($productType);
//        $countries = $countryService->getAvailableCountriesByProductType($productType);
        $brands = $brandService->getAvailableBrandsByProductType($productType);
        $brandsSortedByFirstLetter = $brandService->sortBrandsByFirstLetterByProductType($brands);

        /*$selectedFiltersOptions = $catalogService->getOptionsByFilterData(
            $productType,
            $filtersData->filters,
            $baseCurrency,
            $colors,
            $countries,
            $brands,
        );*/

        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getProductsByTypePaginated(
            $productType,
            $filtersData,
            $filtersData->filters['per_page'] ?? 18,
            $page,
        );


        return view('pages.store.catalog', [
            'filters' => $catalogService->getFiltersByProductType($productType),
            'filtersData' => $filtersData->filters,
            'productType' => $productType,
            'colors' => $colors,
            'brandsSortedByFirstLetter' => $brandsSortedByFirstLetter,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
            'faqs' => $productService->getProductTypeFaqs($productType->slug),
            'seoText' => $productService->getProductTypeSeoTextByLanguage($productType->slug, app()->getLocale())
        ]);
    }
}
