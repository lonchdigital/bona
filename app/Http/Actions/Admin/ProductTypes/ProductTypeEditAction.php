<?php

namespace App\Http\Actions\Admin\ProductTypes;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ProductType\EditProductTypeRequest;
use App\Models\ProductType;
use App\Services\Admin\ProductType\ProductTypeService;

class ProductTypeEditAction extends BaseAction
{
    public function __invoke(ProductType $productType, EditProductTypeRequest $request, ProductTypeService $service)
    {
        $result = $service->updateProductType($productType, $request->toDTO());

        return $this->handleActionResult(route('admin.product-type.list.page'), $request, $result);
    }
}
