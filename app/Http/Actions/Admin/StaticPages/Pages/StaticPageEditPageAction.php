<?php

namespace App\Http\Actions\Admin\StaticPages\Pages;

use App\DataClasses\StaticPageTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Services\StaticPage\StaticPageService;

class StaticPageEditPageAction extends BaseAction
{
    public function __invoke(int $staticPage, StaticPageService $staticPageService)
    {
        if (!StaticPageTypesDataClass::get($staticPage)) {
            abort(404);
        }

        $content = $staticPageService->getContent($staticPage);

        return view('pages.admin.static-pages.edit', [
            'staticPageId' => $staticPage,
            'content' => $content,
        ]);
    }
}
