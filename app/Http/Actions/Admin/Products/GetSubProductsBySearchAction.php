<?php

namespace App\Http\Actions\Admin\Products;

use App\Http\Requests\Store\Product\SearchProductRequest;
use App\Http\Resources\Admin\Product\ProductSearchResource;
use App\Services\Product\ProductService;

class GetSubProductsBySearchAction
{
    public function __invoke(SearchProductRequest $request, ProductService $productsService)
    {
        return ProductSearchResource::collection($productsService->searchSubProducts($request->toDTO(), 13));
    }
}
