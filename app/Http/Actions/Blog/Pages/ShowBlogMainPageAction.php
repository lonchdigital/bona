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
        $config = $blogPageService->getConfigData();
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        return view('pages.blog.main', [
            'blogPageConfig' => $config,
            'articles' => $blogArticleService->getBlogArticlesListPaginated(),
        ]);
    }
}
