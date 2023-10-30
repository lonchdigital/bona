<?php

namespace App\Http\Actions\Admin\ProductTypes;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Admin\ProductType\ProductTypeService;
use App\Http\Requests\Admin\ProductType\EditProductTypeRequest;

class ProductTypeEditAction extends BaseAction
{
    public function __invoke(ProductType $productType, EditProductTypeRequest $request, ProductTypeService $service)
    {
//        dd($request->toDTO());  productTypeAttributes

        $result = $service->updateProductType($productType, $request->toDTO());

        return $this->handleActionResult(route('admin.product-type.list.page'), $request, $result);
    }
}
