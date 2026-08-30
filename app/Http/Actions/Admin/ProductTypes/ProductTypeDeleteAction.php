<?php

namespace App\Http\Actions\Admin\ProductTypes;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ProductType;
use App\Services\Admin\ProductType\ProductTypeService;
use Illuminate\Http\Request;

class ProductTypeDeleteAction extends BaseAction
{
    public function __invoke(ProductType $productType, Request $request, ProductTypeService $service)
    {
        $result = $service->deleteProductType($productType);

        return $this->handleActionResult(route('admin.product-type.list.page'), $request, $result);
    }
}
