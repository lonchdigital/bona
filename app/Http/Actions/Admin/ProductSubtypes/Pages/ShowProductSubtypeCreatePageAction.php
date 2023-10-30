<?php

namespace App\Http\Actions\Admin\ProductSubtypes\Pages;

use App\Services\Admin\ProductField\ProductFieldService;

class ShowProductSubtypeCreatePageAction
{
    public function __invoke(ProductFieldService $service)
    {
        return view('pages.admin.product-subtypes.edit', [
//            'productFields' => $service->getProductFields(),
        ]);
    }
}
