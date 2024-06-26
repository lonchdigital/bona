<?php

namespace App\Http\Actions\Admin\StaticPages;

use App\DataClasses\StaticPageTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Services\StaticPage\StaticPageService;
use App\Http\Requests\Admin\StaticPage\StaticPageEditRequest;

class StaticPageEditAction extends BaseAction
{
    public function __invoke(int $staticPage, StaticPageEditRequest $request, StaticPageService $staticPageService)
    {
        if (!StaticPageTypesDataClass::get($staticPage)) {
            abort(404);
        }

        $result = $staticPageService->update($staticPage, $request->toDTO());

        return $this->handleActionResult(route('admin.pages.list.page'), $request, $result);
    }
}
