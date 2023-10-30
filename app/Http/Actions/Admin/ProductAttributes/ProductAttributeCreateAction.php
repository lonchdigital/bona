<?php

namespace App\Http\Actions\Admin\ProductAttributes;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ProductAttribute\ProductAttributeCreateRequest;
use App\Services\Admin\ProductAttribute\ProductAttributeService;
use App\Services\Admin\ProductField\ProductFieldService;

class ProductAttributeCreateAction extends BaseAction
{
    public function __invoke(ProductAttributeCreateRequest $request, ProductAttributeService $service)
    {
        $result = $service->createProductAttribute($request->toDTO());

        return $this->handleActionResult(route('admin.product-attribute.list.page'), $request, $result);
    }
}
