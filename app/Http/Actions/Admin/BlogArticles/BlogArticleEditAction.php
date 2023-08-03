<?php

namespace App\Http\Actions\Admin\BlogArticles;

use App\Http\Actions\Admin\BaseAction;
use App\Models\BlogArticle;
use App\Services\BlogArticle\BlogArticleService;
use App\Http\Requests\Admin\BlogArticle\BlogArticleEditRequest;

class BlogArticleEditAction extends BaseAction
{
    public function __invoke(BlogArticle $blogArticle, BlogArticleEditRequest $request, BlogArticleService $blogArticleService)
    {
        $result = $blogArticleService->editBlogArticle($blogArticle, $request->toDTO(), $this->getAuthUser());

        return $this->handleActionResult(route('admin.blog-article.list.page'), $request, $result);
    }
}
