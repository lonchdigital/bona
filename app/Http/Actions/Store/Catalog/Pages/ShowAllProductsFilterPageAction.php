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

class ShowAllProductsFilterPageAction extends BaseAction
{
    public function __invoke(

        CatalogFilterRequest $request
    )
    {
//        $productType->load(['fields', 'fields.options']);

        $catalogService = app()->make(ProductFiltersService::class);
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);


        $filtersData = $request->toDTO();

        $baseCurrency = $currencyService->getBaseCurrency();

        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getAllProductsPaginated(
//            $productType,
            $filtersData,
            $filtersData->filters['per_page'] ?? 18, // 3
            $page,
        );


        return view('pages.store.catalog-all-products', [
            'filters' => $catalogService->getAllFilters(),
            'filtersData' => $filtersData->filters,
//            'productType' => $productType,
//            'colors' => $colors,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
            'productsMaxPrice' => $productService->getAllProductsMaxPrice($filtersData),
        ]);
    }
}
