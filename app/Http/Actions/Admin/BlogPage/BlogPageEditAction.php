<?php

namespace App\Http\Actions\Admin\BlogPage;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Blog\BlogPageEditRequest;
use App\Services\BlogPage\BlogPageService;

class BlogPageEditAction extends BaseAction
{
    public function __invoke(BlogPageEditRequest $request, BlogPageService $blogPageService)
    {
        $result = $blogPageService->editBlogPage($request->toDTO());

        return $this->handleActionResult(route('admin.pages.list.page'), $request, $result);
    }
}
