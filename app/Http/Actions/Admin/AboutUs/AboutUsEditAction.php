<?php

namespace App\Http\Actions\Admin\AboutUs;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\AboutUsPage\AboutUsPageEditRequest;
use App\Services\AboutUsPage\AboutUsPageService;

class AboutUsEditAction extends BaseAction
{
    public function __invoke(
        AboutUsPageEditRequest $request,
        AboutUsPageService $aboutUsPageService,
    )
    {
        $result = $aboutUsPageService->editAboutUsPage($request->toDTO());

        return $this->handleActionResult(route('admin.pages.list.page'), $request, $result);
    }
}

