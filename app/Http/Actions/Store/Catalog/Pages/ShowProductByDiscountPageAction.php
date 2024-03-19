<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\ProductType;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductService;


class ShowProductByDiscountPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        CatalogFilterRequest $request,
    )
    {
        $productType->load(['fields', 'fields.options']);

        //get services from service container
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);

        $filtersData = $request->toDTO();
        $baseCurrency = $currencyService->getBaseCurrency();

        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getProductsByDiscountPaginated(
            $filtersData->filters['per_page'] ?? 24,
            $page,
        );

        return view('pages.store.catalog-sort.catalog-sort-by-discount', [
            'productType' => $productType,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
        ]);
    }
}
