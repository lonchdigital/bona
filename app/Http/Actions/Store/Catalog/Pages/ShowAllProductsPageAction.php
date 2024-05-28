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

class ShowAllProductsPageAction extends BaseAction
{
    public function __invoke(
//        ProductType $productType,
        CatalogFilterRequest $request
    )
    {
        $catalogService = app()->make(ProductFiltersService::class);
        $colorService = app()->make(ColorService::class);
//        $colorService = app()->make(ColorService::class);
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);


        $filtersData = $request->toDTO();

        $baseCurrency = $currencyService->getBaseCurrency();
        $colors = $colorService->getAvailableColorsByProductType();
//        $colors = $colorService->getAvailableColorsByProductType($productType);

        $page = $filtersData->filters['page'] ?? 1;

        $allFilters = $catalogService->getAllFilters();

        $productsPaginated = $productService->getAllProductsPaginated(
//            $productType,
            $filtersData,
            $filtersData->filters['per_page'] ?? 18, // 3
            $page,
            $allFilters
        );

//        dd($colors);

        return view('pages.store.catalog-all-products', [
            'filters' => $allFilters,
            'filtersData' => $filtersData->filters,
//            'productType' => $productType,
            'colors' => $colors,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
            'productsMaxPrice' => $productService->getAllProductsMaxPrice($filtersData),
        ]);
    }
}
