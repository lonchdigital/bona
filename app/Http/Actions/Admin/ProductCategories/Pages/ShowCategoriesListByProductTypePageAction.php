<?php

namespace App\Http\Actions\Admin\ProductCategories\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Admin\ProductType\ProductTypeService;

class ShowCategoriesListByProductTypePageAction extends BaseAction
{
    public function __invoke(ProductTypeService $service)
    {
        return view('pages.admin.product-categories.list-by-product-type', [
            'productTypesWithCategories' => $service->getProductTypesWithCategoriesPaginated(),
        ]);
    }
}
