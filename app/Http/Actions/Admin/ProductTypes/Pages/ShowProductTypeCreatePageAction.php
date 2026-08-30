<?php

namespace App\Http\Actions\Admin\ProductTypes\Pages;

use App\Services\Admin\ProductAttribute\ProductAttributeService;
use App\Services\Admin\ProductField\ProductFieldService;

class ShowProductTypeCreatePageAction
{
    public function __invoke(ProductFieldService $productFieldService, ProductAttributeService $productAttributeService)
    {
        return view('pages.admin.product-types.edit', [
            'productFields' => $productFieldService->getProductFields(),
            'productAttributes' => $productAttributeService->getProductAttributes(),
        ]);
    }
}
