<?php

namespace App\Http\Actions\Blog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\BlogPageConfig;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\BlogPage\BlogPageService;

class ShowBlogMainPageAction extends BaseAction
{
    public function __invoke(
        BlogArticleService $blogArticleService,
        BlogPageService $blogPageService
    ) {
        $config = $blogPageService->getConfigData() ?? new BlogPageConfig;
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        return view('pages.blog.main', [
            'blogPageConfig' => $config,
            'articles' => $blogArticleService->getBlogArticlesListPaginated(),
        ]);
    }
}
