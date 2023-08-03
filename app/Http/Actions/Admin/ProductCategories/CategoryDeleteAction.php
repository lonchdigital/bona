<?php

namespace App\Http\Actions\Admin\ProductCategories;

use App\Models\Category;
use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\ProductCategory\CategoryService;

class CategoryDeleteAction extends BaseAction
{
    public function __invoke(ProductType $productType, Category $productCategory, Request $request, CategoryService $service)
    {
        $result = $service->deleteCategory($productCategory);

        return $this->handleActionResult(route('admin.product-category.list.page', ['productType' => $productType]), $request, $result);
    }
}
