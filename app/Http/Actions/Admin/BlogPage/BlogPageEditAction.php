<?php

namespace App\Http\Actions\Admin\BlogPage;

use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogPage\BlogPageService;
use App\Http\Requests\Admin\Blog\BlogPageEditRequest;

class BlogPageEditAction extends BaseAction
{
    public function __invoke(BlogPageEditRequest $request, BlogPageService $blogPageService)
    {
        $result = $blogPageService->editBlogPage($request->toDTO());

        return $this->handleActionResult(route('admin.pages.list.page'), $request, $result);
    }
}
