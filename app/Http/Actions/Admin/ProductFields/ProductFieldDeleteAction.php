<?php

namespace App\Http\Actions\Admin\ProductFields;

use App\Models\ProductField;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Admin\ProductField\ProductFieldService;

class ProductFieldDeleteAction extends BaseAction
{
    public function __invoke(ProductField $productField, Request $request, ProductFieldService $service)
    {
        $result = $service->deleteProductField($productField);

        return $this->handleActionResult(route('admin.product-field.list.page'), $request, $result);
    }
}
