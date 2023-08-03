<?php

namespace App\Http\Actions\Admin\Products;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Product;
use App\Models\ProductType;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductDeleteAction extends BaseAction
{
    public function __invoke(ProductType $productType, Product $product, Request $request, ProductService $productsService)
    {
        $result = $productsService->productDelete($product);

        return $this->handleActionResult(route('admin.product.list.page', ['productType' => $productType->id]), $request, $result);
    }
}
