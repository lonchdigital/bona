<?php

namespace App\Http\Actions\Admin\StaticPages\Pages;

use App\DataClasses\StaticPageTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Services\StaticPage\StaticPageService;

class StaticPageEditPageAction extends BaseAction
{
    public function __invoke(int $staticPageID, StaticPageService $staticPageService)
    {
        if (!StaticPageTypesDataClass::get($staticPageID)) {
            abort(404);
        }

        $staticPage = $staticPageService->getContent($staticPageID);

        return view('pages.admin.static-pages.edit', [
            'staticPageId' => $staticPageID,
            'staticPage' => $staticPage,
        ]);
    }
}
