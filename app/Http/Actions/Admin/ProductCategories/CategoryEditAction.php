<?php

namespace App\Http\Actions\Admin\ProductCategories;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Category\CategoryEditRequest;
use App\Models\Category;
use App\Models\ProductType;
use App\Services\ProductCategory\CategoryService;

class CategoryEditAction extends BaseAction
{
    public function __invoke(ProductType $productType, Category $productCategory, CategoryEditRequest $request, CategoryService $service)
    {
        $result = $service->editCategory($productCategory, $request->toDTO());

        return $this->handleActionResult(route('admin.product-category.list.page', ['productType' => $productType]), $request, $result);
    }
}
