<?php

namespace App\Http\Actions\Store\ServicesPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\ServicesPage\ServicesPageService;

class ShowServicesPageAction extends BaseAction
{
    public function __invoke(
        ServicesPageService $servicesPageService,
    )
    {
        return view('pages.store.services', [
            'config' => $servicesPageService->getServicesConfig(),
            'sections' => $servicesPageService->getServicesPageSections(),
        ]);
    }
}
