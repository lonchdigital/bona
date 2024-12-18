<?php

namespace App\Http\Actions\Admin\Products;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Product\ProductEditRequest;
use App\Models\Product;
use App\Models\ProductType;
use App\Services\Product\ProductService;

class ProductEditAction extends BaseAction
{
    public function __invoke(ProductType $productType, Product $product, ProductEditRequest $request, ProductService $productsService)
    {
        $result = $productsService->productEdit($productType, $product, $request->toDTO());

        return $this->handleActionResult(route('admin.product.edit.page', ['productType' => $productType,'product' => $product]), $request, $result);
    }
}
