<?php

namespace App\Http\Actions\Blog\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;
use App\Services\BlogArticle\BlogArticleService;
use App\Services\BlogCategory\BlogCategoryService;
use App\Services\BlogSlides\BlogSlidesService;

class ShowBlogMainPageAction extends BaseAction
{
    public function __invoke(
        BlogArticleService $blogArticleService,
        BlogCategoryService $blogCategoryService,
        ApplicationConfigService $applicationConfigService,
        BlogSlidesService $blogSlidesService,
    )
    {
        return view('pages.blog.main', [
            'categories' => $blogCategoryService->getBlogCategoriesCollection(),
            'articles' => $blogArticleService->getBlogArticlesListPaginated(),
            'slides' => $blogSlidesService->getBlogSlides(),
        ]);
    }
}
