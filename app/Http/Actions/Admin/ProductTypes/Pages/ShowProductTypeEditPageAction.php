<?php

namespace App\Http\Actions\Admin\ProductTypes\Pages;

use App\Models\ProductType;
use App\Services\Admin\ProductField\ProductFieldService;

class ShowProductTypeEditPageAction
{
    public function __invoke(ProductType $productType, ProductFieldService $service)
    {
        $productFields = $service->getProductFields();

        return view('pages.admin.product-types.edit', [
            'productType' => $productType,
            'productFields' => $productFields,
        ]);
    }
}
