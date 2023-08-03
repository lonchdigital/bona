<?php

namespace App\Http\Actions\Admin\BlogSlides;

use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogSlides\BlogSlidesService;
use App\Http\Requests\Admin\BlogSlides\BlogSlidesEditRequest;

class BlogSlidesEditAction extends BaseAction
{
    public function __invoke(BlogSlidesEditRequest $request, BlogSlidesService $blogSlidesService)
    {
        $result = $blogSlidesService->blogSlidesEdit($request->toDTO());

        return $this->handleActionResult(route('admin.blog-slide.edit.page'), $request, $result);
    }
}
