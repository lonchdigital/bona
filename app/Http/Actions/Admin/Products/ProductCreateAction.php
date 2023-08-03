<?php

namespace App\Http\Actions\Admin\Products;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Product\ProductCreateRequest;
use App\Models\ProductType;
use App\Services\Product\ProductService;

class ProductCreateAction extends BaseAction
{
    public function __invoke(ProductType $productType, ProductCreateRequest $request, ProductService $productsService)
    {
        $creator = $this->getAuthUser();

        $result = $productsService->createProduct($creator, $productType, $request->toDTO());

        return $this->handleActionResult(route('admin.product.list.page', ['productType' => $productType->id]), $request, $result);
    }
}
