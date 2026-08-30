<?php

namespace App\Http\Actions\Store\Catalog;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Http\Resources\Store\Catalog\ProductsCountResource;
use App\Models\ProductType;
use App\Services\Product\ProductService;

class GetProductsCountByFilterAction extends BaseAction
{
    public function __invoke(ProductType $productType, CatalogFilterRequest $request, ProductService $productService)
    {
        $result = $productService->getProductsCountByFilters($productType, $request->toDTO());

        return ProductsCountResource::make($result);
    }
}
