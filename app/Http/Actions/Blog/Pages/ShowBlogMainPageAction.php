<?php

namespace App\Http\Actions\Blog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;
use App\Services\BlogArticle\BlogArticleService;


class ShowBlogMainPageAction extends BaseAction
{
    public function __invoke(
        BlogArticleService $blogArticleService,
        ApplicationConfigService $applicationConfigService,
    )
    {
        return view('pages.blog.main', [
            'articles' => $blogArticleService->getBlogArticlesListPaginated(),
        ]);
    }
}
