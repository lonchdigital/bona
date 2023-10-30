<?php

namespace App\Http\Actions\Admin\ProductAttributes\Pages;

use App\Services\Admin\ProductAttribute\ProductAttributeService;

class ShowProductAttributesListPageAction
{
    public function __invoke(ProductAttributeService $service)
    {
        $productAttributesPaginated = $service->getProductAttributesPaginated();

        return view('pages.admin.product-attributes.list', [
            'productAttributesPaginated' => $productAttributesPaginated,
        ]);
    }
}
