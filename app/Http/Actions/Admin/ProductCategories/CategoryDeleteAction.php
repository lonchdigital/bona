<?php

namespace App\Http\Actions\Admin\ProductCategories;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Category;
use App\Models\ProductType;
use App\Services\ProductCategory\CategoryService;
use Illuminate\Http\Request;

class CategoryDeleteAction extends BaseAction
{
    public function __invoke(ProductType $productType, Category $productCategory, Request $request, CategoryService $service)
    {
        $result = $service->deleteCategory($productCategory);

        return $this->handleActionResult(route('admin.product-category.list.page', ['productType' => $productType]), $request, $result);
    }
}
