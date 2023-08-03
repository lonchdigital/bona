<?php

namespace App\Http\Actions\Admin\BlogCategories;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\BlogCategory\EditBlogCategoryRequest;
use App\Services\BlogCategory\BlogCategoryService;

class BlogCategoryCreateAction extends BaseAction
{
    public function __invoke(EditBlogCategoryRequest $request, BlogCategoryService $blogCategoryService)
    {
        $creator = $this->getAuthUser();

        $result = $blogCategoryService->createBlogCategory($creator, $request->toDTO());

        return $this->handleActionResult(route('admin.blog-category.list.page'), $request, $result);
    }
}
