<?php

namespace App\Http\Actions\Admin\ProductAttributes;

use App\Models\ProductAttribute;
use App\Services\Admin\ProductAttribute\ProductAttributeService;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;


class ProductAttributeDeleteAction extends BaseAction
{
    public function __invoke(ProductAttribute $productAttribute, Request $request, ProductAttributeService $service)
    {
        $result = $service->deleteProductAttribute($productAttribute);

        return $this->handleActionResult(route('admin.product-attribute.list.page'), $request, $result);
    }
}
