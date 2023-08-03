<?php

namespace App\Http\Actions\Admin\BlogArticles\Pages;

use App\Models\BlogArticle;
use Illuminate\Contracts\View\View;


class ShowBlogArticleEditPageAction
{
    public function __invoke(BlogArticle $blogArticle): View
    {
        return view('pages.admin.blog-articles.edit', [
            'blogArticle' => $blogArticle,
        ]);
    }
}
