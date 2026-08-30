<?php

namespace App\Http\Actions\Admin\BlogCategories;

use App\Http\Actions\Admin\BaseAction;
use App\Models\BlogCategory;
use App\Services\BlogCategory\BlogCategoryService;
use Illuminate\Http\Request;

class BlogCategoryDeleteAction extends BaseAction
{
    public function __invoke(BlogCategory $blogCategory, Request $request, BlogCategoryService $blogCategoryService)
    {
        $result = $blogCategoryService->deleteBlogCategory($blogCategory);

        return $this->handleActionResult(route('admin.blog-category.list.page'), $request, $result);
    }
}
