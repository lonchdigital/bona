<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\Color;
use App\Models\ProductType;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductService;

class ShowProductByColorPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        CatalogFilterRequest $request,
        Color $color
    ) {
        $productType->load(['fields', 'fields.options']);

        // get services from service container
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);

        $filtersData = $request->toDTO();
        $baseCurrency = $currencyService->getBaseCurrency();

        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getProductsByColorPaginated(
            (int) config('domain.store_catalog_items_per_page'),
            $page,
            $color
        );

        $pageTitle = trans('base.color').' '.$color->name;
        if ($color->id == 7) {
            $pageTitle = trans('base.white_doors');
        }

        return view('pages.store.catalog-sort.catalog-sort-by-color', [
            'productType' => $productType,
            'color' => $color,
            'pageTitle' => $pageTitle,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
        ]);
    }
}
