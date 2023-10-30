<?php

namespace App\Http\Actions\Admin\ProductAttributes\Pages;

use App\Models\ProductAttribute;
//use App\Services\Admin\ProductField\ProductFieldService;

class ShowProductAttributeEditPageAction
{
    public function __invoke(ProductAttribute $productAttribute)
    {
        return view('pages.admin.product-attributes.edit', [
            'productAttribute' => $productAttribute,
        ]);
    }
}
