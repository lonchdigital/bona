<?php

namespace App\Http\Actions\Blog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\BlogPage\BlogPageService;

class ShowBlogMainPageAction extends BaseAction
{
    public function __invoke(
        BlogArticleService $blogArticleService,
        ApplicationConfigService $applicationConfigService,
        BlogPageService $blogPageService
    )
    {
        return view('pages.blog.main', [
            'blogPageConfig' => $blogPageService->getConfigData(),
            'articles' => $blogArticleService->getBlogArticlesListPaginated(),
        ]);
    }
}
