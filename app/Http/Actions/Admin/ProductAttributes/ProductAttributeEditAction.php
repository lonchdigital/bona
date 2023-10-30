<?php

namespace App\Http\Actions\Admin\ProductAttributes;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ProductAttribute;
use App\Services\Admin\ProductAttribute\ProductAttributeService;
use App\Http\Requests\Admin\ProductAttribute\ProductAttributeEditRequest;

class ProductAttributeEditAction extends BaseAction
{
    public function __invoke(ProductAttribute $productAttribute, ProductAttributeEditRequest $request, ProductAttributeService $service)
    {
        $result = $service->updateProductField($productAttribute, $request->toDTO());

        return $this->handleActionResult(route('admin.product-attribute.list.page'), $request, $result);
    }
}
