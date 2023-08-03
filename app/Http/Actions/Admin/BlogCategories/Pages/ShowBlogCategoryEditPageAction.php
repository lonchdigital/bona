<?php

namespace App\Http\Actions\Admin\BlogCategories\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\BlogCategory;

class ShowBlogCategoryEditPageAction extends BaseAction
{
    public function __invoke(BlogCategory $blogCategory)
    {
        return view('pages.admin.blog-categories.edit', [
            'blogCategory' => $blogCategory,
        ]);
    }
}
