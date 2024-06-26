<?php

namespace App\Http\Actions\Store\StaticPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\DataClasses\StaticPageTypesDataClass;
use App\Services\StaticPage\StaticPageService;

class ShowStaticPagePageAction extends BaseAction
{
    public function __invoke(string $staticPageSlug, StaticPageService $staticPageService)
    {
        $staticPage = StaticPageTypesDataClass::get()->where('slug', $staticPageSlug)->first();

        if (!$staticPage) {
            abort(404);
        }

        $allData = $staticPageService->getAllDataByLanguage($staticPage['id'], app()->getLocale());
        $allData['meta_tags'] = $this->handleFollowTag($allData['meta_tags']);

        return view('pages.store.static-page', [
            'heading' => $staticPage['name'],
            'allData' => $allData,
        ]);
    }
}
