<?php

namespace App\Http\Actions\Admin\BlogArticles;

use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogArticle\BlogArticleService;
use App\Http\Requests\Admin\BlogArticle\BlogArticleCreateRequest;

class BlogArticleCreateAction extends BaseAction
{
    public function __invoke(BlogArticleCreateRequest $request, BlogArticleService $blogArticleService)
    {
        $result = $blogArticleService->createBlogArticle($request->toDTO(), $this->getAuthUser());

        return $this->handleActionResult(route('admin.blog-article.list.page'), $request, $result);
    }
}
