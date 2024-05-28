<?php

namespace App\Http\Actions\Store\Catalog;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Product\ProductService;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Http\Resources\Store\Catalog\ProductsCountResource;
use App\Services\Product\ProductFiltersService;

class GetAllProductsCountByFilterAction extends BaseAction
{
    public function __invoke(ProductType $productType, CatalogFilterRequest $request, ProductService $productService, ProductFiltersService $catalogService)
    {
        $allFilters = $catalogService->getAllFilters();

        $result = $productService->getAllProductsCountByFilters($request->toDTO(), $allFilters);

        return ProductsCountResource::make($result);
    }
}
