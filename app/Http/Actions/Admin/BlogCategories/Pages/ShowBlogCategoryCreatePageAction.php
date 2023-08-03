<?php

namespace App\Http\Actions\Admin\BlogCategories\Pages;

use App\Http\Actions\Admin\BaseAction;

class ShowBlogCategoryCreatePageAction extends BaseAction
{
    public function __invoke()
    {
        return view('pages.admin.blog-categories.edit');
    }
}
