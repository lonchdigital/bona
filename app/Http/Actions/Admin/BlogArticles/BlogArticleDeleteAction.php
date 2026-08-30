<?php

namespace App\Http\Actions\Admin\BlogArticles;

use App\Http\Actions\Admin\BaseAction;
use App\Models\BlogArticle;
use App\Services\BlogArticle\BlogArticleService;
use Illuminate\Http\Request;

class BlogArticleDeleteAction extends BaseAction
{
    public function __invoke(BlogArticle $blogArticle, Request $request, BlogArticleService $blogArticleService)
    {
        return $this->handleActionResult(route('admin.blog-article.list.page'), $request, $blogArticleService->deleteBlogArticle($blogArticle));
    }
}
