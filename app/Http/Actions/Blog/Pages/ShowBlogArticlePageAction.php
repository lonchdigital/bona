<?php

namespace App\Http\Actions\Blog\Pages;

use App\Models\BlogArticle;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Author\AuthorService;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\Currency\CurrencyService;
use Abordage\LastModified\Facades\LastModified;

class ShowBlogArticlePageAction extends BaseAction
{
    public function __invoke(
        BlogArticle $blogArticle,
        CurrencyService $currencyService,
        BlogArticleService $blogArticleService,
        AuthorService $authorService,
    )
    {
        $blogArticle->meta_tags = $this->handleFollowTag($blogArticle->meta_tags);
        LastModified::set($blogArticle->updated_at);

        return view('pages.blog.article', [
            'blogArticle' => $blogArticle,
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'latestArticles' => $blogArticleService->getLatestArticlesExceptCurrent($blogArticle->id),
            // Null until an author is created in the admin panel; the template
            // then falls back to the loose author fields in the global config.
            'articleAuthor' => $authorService->getDefaultAuthor(),
        ]);
    }
}
