<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\ProductType;
use App\Models\Brand;
use App\Services\Brand\BrandService;
use App\Services\ProductCategory\CategoryService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductFiltersService;
use App\Services\Product\ProductService;
use App\Services\WishList\WishListService;

class ShowProductByBrandPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        CatalogFilterRequest $request,
        Brand $brand
    )
    {
        $productType->load(['fields', 'fields.options']);

        //get services from service container
        $catalogService = app()->make(ProductFiltersService::class);
        $colorService = app()->make(ColorService::class);
        $brandService = app()->make(BrandService::class);
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);


//        dd(ProductService::class);

        $filtersData = $request->toDTO();

        $baseCurrency = $currencyService->getBaseCurrency();
        $colors = $colorService->getAvailableColorsByProductType($productType);
        $brands = $brandService->getAvailableBrandsByProductType($productType);
        $brandsSortedByFirstLetter = $brandService->sortBrandsByFirstLetterByProductType($brands);


        $page = $filtersData->filters['page'] ?? 1;

//        dd($productType);
        $productsPaginated = $productService->getProductsByBrandPaginated(
            $filtersData->filters['per_page'] ?? 24,
            $page,
            $brand->id
        );



        return view('pages.store.catalog-by-brand', [
//            'filters' => $catalogService->getFiltersByProductType($productType),
//            'filtersData' => $filtersData->filters,
            'productType' => $productType,
//            'colors' => $colors,
//            'brandsSortedByFirstLetter' => $brandsSortedByFirstLetter,
            'brand' => $brand,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
        ]);
    }
}
