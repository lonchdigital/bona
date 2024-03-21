<?php

namespace App\Http\Actions\Admin\BlogPage\Pages;


use App\Services\BlogPage\BlogPageService;

class ShowBlogPageEditPageAction
{
    public function __invoke(
        BlogPageService $blogPageService
    )
    {
        return view('pages.admin.blog-page.edit', [
            'blogPageConfig' => $blogPageService->getConfigData(),
        ]);
    }
}
