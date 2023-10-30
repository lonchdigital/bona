<?php

namespace App\Http\Actions\Store\StaticPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\DataClasses\StaticPageTypesDataClass;
use App\Services\StaticPage\SeoTextsService;

class ShowStaticPagePageAction extends BaseAction
{
    public function __invoke(string $staticPageSlug, SeoTextsService $staticPageService)
    {
        $staticPage = StaticPageTypesDataClass::get()->where('slug', $staticPageSlug)->first();

        if (!$staticPage) {
            abort(404);
        }

        return view('pages.store.static-page', [
            'heading' => $staticPage['name'],
            'content' => $staticPageService->getContentByLanguage($staticPage['id'], app()->getLocale()),
        ]);
    }
}
