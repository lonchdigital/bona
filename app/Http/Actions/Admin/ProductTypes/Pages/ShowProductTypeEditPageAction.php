<?php

namespace App\Http\Actions\Admin\ProductTypes\Pages;

use App\Models\ProductType;
use App\Services\Admin\ProductField\ProductFieldService;
use App\Services\Admin\ProductAttribute\ProductAttributeService;

class ShowProductTypeEditPageAction
{
    public function __invoke(ProductType $productType, ProductFieldService $productFieldService, ProductAttributeService $productAttributeService)
    {

        return view('pages.admin.product-types.edit', [
            'productType' => $productType,
            'productFields' => $productFieldService->getProductFields(),
            'productAttributes' => $productAttributeService->getProductAttributes(),
        ]);
    }
}
