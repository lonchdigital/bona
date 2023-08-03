<?php

namespace App\Http\Actions\Admin\BlogCategories;

use App\Models\BlogCategory;
use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogCategory\BlogCategoryService;
use App\Http\Requests\Admin\BlogCategory\EditBlogCategoryRequest;

class BlogCategoryEditAction extends BaseAction
{
    public function __invoke(BlogCategory $blogCategory, EditBlogCategoryRequest $request, BlogCategoryService $blogCategoryService)
    {
        $result = $blogCategoryService->editBlogCategory($blogCategory, $request->toDTO());

        return $this->handleActionResult(route('admin.blog-category.list.page'), $request, $result);
    }
}
