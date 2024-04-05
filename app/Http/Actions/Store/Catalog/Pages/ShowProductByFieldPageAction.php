<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\ProductField;
use App\Models\ProductType;
use App\Services\Currency\CurrencyService;
use App\Services\Product\ProductService;

class ShowProductByFieldPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        CatalogFilterRequest $request,
        ProductField $productField,
        $productOptionID
    )
    {
        $productType->load(['fields', 'fields.options']);

        //get services from service container
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);

        $filtersData = $request->toDTO();
        $baseCurrency = $currencyService->getBaseCurrency();

        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getProductsByFieldPaginated(
            $filtersData->filters['per_page'] ?? 18,
            $page,
            $productField,
            $productOptionID
        );

        return view('pages.store.catalog-sort.catalog-sort-by-field', [
            'productType' => $productType,
            'productField' => $productField,
            'selectedOption' => $productField->options->where('id', $productOptionID)->first(),
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
        ]);
    }
}
