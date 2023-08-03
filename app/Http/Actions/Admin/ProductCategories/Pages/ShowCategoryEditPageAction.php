<?php

namespace App\Http\Actions\Admin\ProductCategories\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Category;
use App\Models\ProductType;

class ShowCategoryEditPageAction extends BaseAction
{
    public function __invoke(ProductType $productType, Category $productCategory)
    {
        return view('pages.admin.product-categories.edit', [
            'productCategory' => $productCategory,
            'productType' => $productType,
        ]);
    }
}
