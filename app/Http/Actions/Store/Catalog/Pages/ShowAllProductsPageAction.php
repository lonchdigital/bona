<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\DataClasses\ProductStatusDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\ProductType;
use App\Services\Color\ColorService;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductFiltersService;
use App\Services\Product\ProductService;
use App\Services\Search\SearchAnalyticsService;

class ShowAllProductsPageAction extends BaseAction
{
    public function __invoke(
        //        ProductType $productType,
        CatalogFilterRequest $request,
        SearchAnalyticsService $analyticsService,
    ) {
        $catalogService = app()->make(ProductFiltersService::class);
        $colorService = app()->make(ColorService::class);
        //        $colorService = app()->make(ColorService::class);
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);

        $filtersData = $request->toDTO();

        $baseCurrency = $currencyService->getBaseCurrency();
        $colors = $colorService->getAllColors();
        //        $colors = $colorService->getAvailableColorsByProductType($productType);

        $page = $filtersData->filters['page'] ?? 1;

        $allFilters = $catalogService->getAllFilters();

        $productsPaginated = $productService->getAllProductsPaginated(
            //            $productType,
            $filtersData,
            (int) config('domain.store_catalog_items_per_page'),
            $page,
            $allFilters
        );

        $searchQuery = trim((string) $request->validated('query', ''));
        if ($searchQuery !== '') {
            $analyticsService->record($searchQuery, $productsPaginated->total());
        }

        //        dd($colors);

        return view('pages.store.catalog-all-products', [
            'filters' => $allFilters,
            'filtersData' => $filtersData->filters,
            'productStatuses' => ProductStatusDataClass::getForWeb(),
            //            'productType' => $productType,
            'colors' => $colors,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
            'productsMaxPrice' => $productService->getAllProductsMaxPrice($filtersData),
            'searchQuery' => $searchQuery,
        ]);
    }
}
