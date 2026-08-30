<?php

namespace App\Http\Actions\Admin\ProductFields;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ProductField\ProductFieldCreateRequest;
use App\Services\Admin\ProductField\ProductFieldService;

class ProductFieldCreateAction extends BaseAction
{
    public function __invoke(ProductFieldCreateRequest $request, ProductFieldService $service)
    {
        $result = $service->createProductField($request->toDTO());

        return $this->handleActionResult(route('admin.product-field.list.page'), $request, $result);
    }
}
