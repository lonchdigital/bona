<?php

namespace App\Http\Actions\Admin\ProductCategories\Pages;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;

class ShowCategoryCreatePageAction extends BaseAction
{
    public function __invoke(ProductType $productType)
    {
        return view('pages.admin.product-categories.edit', [
            'productType' => $productType,
        ]);
    }
}
