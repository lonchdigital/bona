<?php

namespace App\Http\Actions\Admin\ProductCategories;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Category\CategoryCreateRequest;
use App\Models\ProductType;
use App\Services\ProductCategory\CategoryService;

class CategoryCreateAction extends BaseAction
{
    public function __invoke(ProductType $productType, CategoryCreateRequest $request, CategoryService $service)
    {
        $creator = $this->getAuthUser();

        $result = $service->createCategory($productType, $creator, $request->toDTO());

        return $this->handleActionResult(route('admin.product-category.list.page', ['productType' => $productType->id]), $request, $result);
    }
}
