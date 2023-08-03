<?php

namespace App\Http\Actions\Admin\BlogCategories\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogCategory\BlogCategoryService;

class ShowBlogCategoriesListPageAction extends BaseAction
{
    public function __invoke(BlogCategoryService $blogCategoryService)
    {
        return view('pages.admin.blog-categories.list', [
            'blogCategoriesPaginated' => $blogCategoryService->getBlogCategoriesListPaginated(),
        ]);
    }
}
