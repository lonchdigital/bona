<?php

namespace App\Http\Actions\Admin\ProductTypes\Pages;

use App\Services\Admin\ProductField\ProductFieldService;

class ShowProductTypeCreatePageAction
{
    public function __invoke(ProductFieldService $service)
    {
        return view('pages.admin.product-types.edit', [
            'productFields' => $service->getProductFields(),
        ]);
    }
}
