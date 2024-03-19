<?php

namespace App\Http\Actions\Store\Catalog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Models\Color;
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

class ShowProductByColorPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        CatalogFilterRequest $request,
        Color $color
    )
    {
        $productType->load(['fields', 'fields.options']);

        //get services from service container
        $currencyService = app()->make(CurrencyService::class);
        $productService = app()->make(ProductService::class);

        $filtersData = $request->toDTO();
        $baseCurrency = $currencyService->getBaseCurrency();

        $page = $filtersData->filters['page'] ?? 1;

        $productsPaginated = $productService->getProductsByColorPaginated(
            $filtersData->filters['per_page'] ?? 24,
            $page,
            $color
        );

        return view('pages.store.catalog-sort.catalog-sort-by-color', [
            'productType' => $productType,
            'color' => $color,
            'baseCurrency' => $baseCurrency,
            'productsPaginated' => $productsPaginated,
        ]);
    }
}
