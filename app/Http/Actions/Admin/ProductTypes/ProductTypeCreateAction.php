<?php

namespace App\Http\Actions\Admin\ProductTypes;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ProductType\EditProductTypeRequest;
use App\Services\Admin\ProductType\ProductTypeService;

class ProductTypeCreateAction extends BaseAction
{
    public function __invoke(EditProductTypeRequest $request, ProductTypeService $service)
    {
        $result = $service->createProductType($request->toDTO());

        return $this->handleActionResult(route('admin.product-type.list.page'), $request, $result);
    }
}
