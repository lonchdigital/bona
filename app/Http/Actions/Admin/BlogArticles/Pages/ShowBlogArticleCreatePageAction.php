<?php

namespace App\Http\Actions\Admin\BlogArticles\Pages;

use App\Http\Actions\Admin\BaseAction;

class ShowBlogArticleCreatePageAction extends BaseAction
{
    public function __invoke()
    {
        return view('pages.admin.blog-articles.edit');
    }
}
