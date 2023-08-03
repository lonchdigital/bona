<?php

namespace App\Http\Actions\Admin\ProductTypes\Pages;

use App\Services\Admin\ProductType\ProductTypeService;

class ShowProductTypesListPageAction
{
    public function __invoke(ProductTypeService $service)
    {
        $productTypesPaginated = $service->getProductTypesPaginated();

        return view('pages.admin.product-types.list', [
            'productTypesPaginated' => $productTypesPaginated,
        ]);
    }
}
