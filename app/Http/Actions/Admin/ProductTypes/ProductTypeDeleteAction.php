<?php

namespace App\Http\Actions\Admin\ProductTypes;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Admin\ProductType\ProductTypeService;

class ProductTypeDeleteAction extends BaseAction
{
    public function __invoke(ProductType $productType, Request $request, ProductTypeService $service)
    {
        $result = $service->deleteProductType($productType);

        return $this->handleActionResult(route('admin.product-type.list.page'), $request, $result);
    }
}
