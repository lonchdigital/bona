<?php

namespace App\Http\Actions\Store\Catalog;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Http\Resources\Store\Catalog\ProductsCountResource;
use App\Models\ProductType;
use App\Services\Product\ProductFiltersService;
use App\Services\Product\ProductService;

class GetAllProductsCountByFilterAction extends BaseAction
{
    public function __invoke(ProductType $productType, CatalogFilterRequest $request, ProductService $productService, ProductFiltersService $catalogService)
    {
        $allFilters = $catalogService->getAllFilters();

        $result = $productService->getAllProductsCountByFilters($request->toDTO(), $allFilters);

        return ProductsCountResource::make($result);
    }
}
