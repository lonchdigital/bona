<?php

namespace App\Http\Actions\Store\Catalog;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Catalog\CatalogFilterRequest;
use App\Http\Resources\Store\Catalog\ProductsCountResource;
use App\Models\Category;
use App\Models\ProductType;
use App\Services\Product\ProductService;

class GetAvailabilityProductsCountByFilterWithCategoryAction extends BaseAction
{
    public function __invoke(ProductType $productType, Category $category, CatalogFilterRequest $request, ProductService $productService)
    {

        $result = $productService->getProductsCountWithCategoryByAvailability($productType, $category, $request->toDTO());

        return ProductsCountResource::make($result);
    }
}
