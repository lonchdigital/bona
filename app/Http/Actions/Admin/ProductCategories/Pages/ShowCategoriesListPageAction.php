<?php

namespace App\Http\Actions\Admin\ProductCategories\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ProductType;
use App\Services\ProductCategory\CategoryService;

class ShowCategoriesListPageAction extends BaseAction
{
    public function __invoke(ProductType $productType, CategoryService $service)
    {
        return view('pages.admin.product-categories.list', [
            'productCategoriesPaginated' => $service->getProductCategoryPaginated($productType),
            'productType' => $productType,
        ]);
    }
}
