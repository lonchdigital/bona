<?php

namespace App\Http\Actions\Admin\ProductSubtypes;

use App\Models\ProductSubtype;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Admin\ProductSubtype\ProductSubtypeService;

class ProductSubtypeDeleteAction extends BaseAction
{
    public function __invoke(ProductSubtype $productSubtype, Request $request, ProductSubtypeService $service)
    {
        $result = $service->deleteProductSubtype($productSubtype);

        return $this->handleActionResult(route('admin.product-subtype.list.page'), $request, $result);
    }
}
