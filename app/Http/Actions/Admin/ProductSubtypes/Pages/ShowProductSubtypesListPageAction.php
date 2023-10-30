<?php

namespace App\Http\Actions\Admin\ProductSubtypes\Pages;

use App\Services\Admin\ProductSubtype\ProductSubtypeService;

class ShowProductSubtypesListPageAction
{
    public function __invoke(ProductSubtypeService $service)
    {
        $productSubtypesPaginated = $service->getProductSubtypesPaginated();

        return view('pages.admin.product-subtypes.list', [
            'productSubtypesPaginated' => $productSubtypesPaginated,
        ]);
    }
}
