<?php

namespace App\Http\Actions\Admin\AboutUs\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;
use App\Services\AboutUsPage\AboutUsPageService;

class ShowAboutUsEditPageAction extends BaseAction
{

    public function __invoke(
        ApplicationConfigService $applicationService,
        AboutUsPageService $aboutUsService,
    )
    {
        return view('pages.admin.about-us.edit', [
            'availableLanguages' => $applicationService->getAvailableLanguages(),
            'aboutUsConfig' => $aboutUsService->getAboutUsConfig(),
        ]);
    }

}
