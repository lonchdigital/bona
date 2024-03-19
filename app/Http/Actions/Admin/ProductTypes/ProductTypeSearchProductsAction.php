<?php

namespace App\Http\Actions\Admin\ProductTypes;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Admin\ProductType\ProductTypeService;
use Illuminate\Http\Request;

class ProductTypeSearchProductsAction extends BaseAction
{
    public function __invoke(ProductType $productType, Request $request, ProductTypeService $service)
    {
        $request->validate([
            'search' => 'nullable|string',
            'excludePostIds' => 'nullable|string',
        ]);

        return $service->searchAdditionalProducts($productType, $request->all());
    }
}
