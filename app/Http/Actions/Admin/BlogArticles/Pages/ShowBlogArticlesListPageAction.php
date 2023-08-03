<?php

namespace App\Http\Actions\Admin\BlogArticles\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogArticle\BlogArticleService;

class ShowBlogArticlesListPageAction extends BaseAction
{
    public function __invoke(BlogArticleService $blogArticleService)
    {
        return view('pages.admin.blog-articles.list', [
            'blogArticlesPaginated' => $blogArticleService->getBlogArticlesListPaginated(),
        ]);
    }
}
