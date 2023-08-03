<?php

namespace App\Http\Actions\Admin\ProductFields\Pages;

use App\Models\ProductField;
use App\Services\Admin\ProductField\ProductFieldService;

class ShowProductFieldEditPageAction
{
    public function __invoke(ProductField $productField, ProductFieldService $productFieldService)
    {
        return view('pages.admin.product-fields.edit', [
            'productField' => $productField,
            'optionsInUse' => $productFieldService->getOptionsInUse($productField),
        ]);
    }
}
