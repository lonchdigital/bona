<?php

namespace App\Http\Actions\Admin\ProductSubtypes;

use App\Models\ProductSubtype;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Admin\ProductSubtype\ProductSubtypeService;
use App\Http\Requests\Admin\ProductSubtype\EditProductSubtypeRequest;

class ProductSubtypeEditAction extends BaseAction
{
    public function __invoke(ProductSubtype $productSubtype, EditProductSubtypeRequest $request, ProductSubtypeService $service)
    {
        $result = $service->updateProductType($productSubtype, $request->toDTO());

        return $this->handleActionResult(route('admin.product-subtype.list.page'), $request, $result);
    }
}
