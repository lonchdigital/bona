<?php

namespace App\Http\Actions\Store\Product;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Product\SearchProductRequest;
use App\Http\Resources\Store\Calculator\ProductCalculatorResource;
use App\Services\Product\ProductService;

class SearchProductAction extends BaseAction
{
    public function __invoke(SearchProductRequest $request, ProductService $productService)
    {
        return ProductCalculatorResource::collection($productService->searchProducts($request->toDTO()));
    }
}
