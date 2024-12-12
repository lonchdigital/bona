<?php

namespace App\Http\Actions\Admin\Products;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Product;
use App\Models\ProductType;
use App\Services\Base\ServiceActionResult;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductEditCreatedAtAction extends BaseAction
{
    public function __invoke(ProductType $productType, Product $product, Request $request, ProductService $productsService)
    {
        $validated = $request->validate([
            'created_at' => 'required|string|date_format:Y-m-d H:i:s',
        ]);

        $product->update($validated);
        $result = ServiceActionResult::make(true, trans('admin.product_date_edit_success'));

        return $this->handleActionResult(route('admin.product.list.page', ['productType' => $productType->id]), $request, $result);
    }
}
