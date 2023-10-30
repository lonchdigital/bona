<?php

namespace App\Http\Actions\Admin\ProductSubtypes;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ProductSubtype\EditProductSubtypeRequest;
use App\Services\Admin\ProductSubtype\ProductSubtypeService;

class ProductSubtypeCreateAction extends BaseAction
{
    public function __invoke(EditProductSubtypeRequest $request, ProductSubtypeService $service)
    {
        $result = $service->createProductSubtype($request->toDTO());

        return $this->handleActionResult(route('admin.product-subtype.list.page'), $request, $result);
    }
}
