<?php

namespace App\Http\Actions\Admin\ProductFields\Pages;

use App\Services\Admin\ProductField\ProductFieldService;

class ShowProductFieldsListPageAction
{
    public function __invoke(ProductFieldService $service)
    {
        $productFieldsPaginated = $service->getProductFieldsPaginated();

        return view('pages.admin.product-fields.list', [
            'productFieldsPaginated' => $productFieldsPaginated,
        ]);
    }
}
