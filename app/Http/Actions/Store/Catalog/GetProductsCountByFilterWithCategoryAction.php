<?php

namespace App\Http\Actions\Store\Catalog;

use App\Models\ProductType;
use App\Models\Category;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Product\ProductService;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Http\Resources\Store\Catalog\ProductsCountResource;

class GetProductsCountByFilterWithCategoryAction extends BaseAction
{
    public function __invoke(ProductType $productType, Category $category, CatalogFilterRequest $request, ProductService $productService)
    {
        $result = $productService->getProductsWithCategoryCountByFilters($productType, $category, $request->toDTO());

        return ProductsCountResource::make($result);
    }
}
