<?php

namespace App\Http\Actions\Admin\BlogSlides\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\BlogSlides\BlogSlidesService;

class ShowBlogSlidesEditPageAction extends BaseAction
{
    public function __invoke(BlogSlidesService $blogSlidesService)
    {
        return view('pages.admin.blog-slides.edit', [
            'slides' => $blogSlidesService->getBlogSlides(),
        ]);
    }
}
