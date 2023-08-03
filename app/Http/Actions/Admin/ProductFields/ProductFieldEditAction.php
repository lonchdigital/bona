<?php

namespace App\Http\Actions\Admin\ProductFields;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ProductField;
use App\Services\Admin\ProductField\ProductFieldService;
use App\Http\Requests\Admin\ProductField\ProductFieldEditRequest;

class ProductFieldEditAction extends BaseAction
{
    public function __invoke(ProductField $productField, ProductFieldEditRequest $request, ProductFieldService $service)
    {
        $result = $service->updateProductField($productField, $request->toDTO());

        return $this->handleActionResult(route('admin.product-field.list.page'), $request, $result);
    }
}
