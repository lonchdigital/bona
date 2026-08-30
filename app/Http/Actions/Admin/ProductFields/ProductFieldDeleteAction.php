<?php

namespace App\Http\Actions\Admin\ProductFields;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ProductField;
use App\Services\Admin\ProductField\ProductFieldService;
use Illuminate\Http\Request;

class ProductFieldDeleteAction extends BaseAction
{
    public function __invoke(ProductField $productField, Request $request, ProductFieldService $service)
    {
        $result = $service->deleteProductField($productField);

        return $this->handleActionResult(route('admin.product-field.list.page'), $request, $result);
    }
}
