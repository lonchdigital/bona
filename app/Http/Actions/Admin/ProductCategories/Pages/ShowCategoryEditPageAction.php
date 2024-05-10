<?php

namespace App\Http\Actions\Admin\ProductCategories\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Category;
use App\Services\Admin\ProductField\ProductFieldService;
use App\Models\ProductType;

class ShowCategoryEditPageAction extends BaseAction
{
    public function __invoke(ProductType $productType, Category $productCategory, ProductFieldService $productFieldService,)
    {
        return view('pages.admin.product-categories.edit', [
            'productCategory' => $productCategory,
            'productType' => $productType,
            'seoData' => $productFieldService->getProductTypeSeoText($productCategory->slug),
        ]);
    }
}
