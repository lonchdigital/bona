<?php

namespace App\Http\Actions\Admin\ApplicationConfig\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;


class ShowApplicationConfigEditPageAction extends BaseAction
{

    public function __invoke(
        ApplicationConfigService $applicationConfigService,
    )
    {
        return view('pages.admin.application-config.edit', [
            'availableLanguages' => $applicationConfigService->getAvailableLanguages(),
            'applicationConfig' => $applicationConfigService->getAllApplicationConfigOptions(),
        ]);
    }

}
