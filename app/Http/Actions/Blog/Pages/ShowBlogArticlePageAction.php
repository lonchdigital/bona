<?php

namespace App\Http\Actions\Blog\Pages;

use App\Models\BlogArticle;
use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;

class ShowBlogArticlePageAction extends BaseAction
{
    public function __invoke(
        BlogArticle $blogArticle,
        CurrencyService $currencyService,
        BlogArticleService $blogArticleService,
    )
    {
        return view('pages.blog.article', [
            'blogArticle' => $blogArticle,
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'latestArticles' => $blogArticleService->getLatestArticlesExceptCurrent($blogArticle->id),
        ]);
    }
}
