<?php

namespace App\Http\Actions\Admin\Services\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;
use App\Services\ServicesPage\ServicesPageService;

class ShowServiceEditPageAction extends BaseAction
{

    public function __invoke(
        ApplicationConfigService $applicationService,
        ServicesPageService $servicesPageService,
    )
    {
        return view('pages.admin.services.edit', [
            'config' => $servicesPageService->getServicesConfig(),
            'availableLanguages' => $applicationService->getAvailableLanguages(),
            'sections' => $servicesPageService->getServicesPageSections(),
        ]);
    }

}
