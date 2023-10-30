<?php

namespace App\Http\Actions\Admin\ProductSubtypes\Pages;

use App\Models\ProductSubtype;

class ShowProductSubtypeEditPageAction
{
    public function __invoke(
        ProductSubtype $productSubtype,
//        ProductFieldService $service
    )
    {
        return view('pages.admin.product-subtypes.edit', [
            'productSubtype' => $productSubtype,
        ]);
    }
}
