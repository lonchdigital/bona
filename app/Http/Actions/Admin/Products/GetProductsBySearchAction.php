<?php

namespace App\Http\Actions\Admin\Products;

use App\Http\Requests\Admin\Product\ProductFilterRequest;
use App\Http\Resources\Admin\Product\ProductSearchResource;
use App\Services\Product\ProductService;

class GetProductsBySearchAction
{
    public function __invoke(ProductFilterRequest $request, ProductService $productsService)
    {
        return ProductSearchResource::collection($productsService->searchParentProducts($request->toDTO()));
    }
}
