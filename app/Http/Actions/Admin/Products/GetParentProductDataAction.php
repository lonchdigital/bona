<?php

namespace App\Http\Actions\Admin\Products;

use App\Models\Product;
use App\Models\ProductType;
use App\Services\Product\ProductService;
use App\Http\Resources\Admin\Product\ProductResource;

class GetParentProductDataAction
{
    public function __invoke(ProductType $productType, Product $product, ProductService $productService)
    {
        return ProductResource::make($productService->getParentProductData($product));
    }
}
