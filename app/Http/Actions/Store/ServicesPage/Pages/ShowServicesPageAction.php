<?php

namespace App\Http\Actions\Store\ServicesPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ServicesConfig;
use App\Services\ServicesPage\ServicesPageService;
use Abordage\LastModified\Facades\LastModified;

class ShowServicesPageAction extends BaseAction
{
    public function __invoke(
        ServicesPageService $servicesPageService,
    )
    {
        $config = $servicesPageService->getServicesConfig();
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        LastModified::set($config->updated_at);

        return view('pages.store.services', [
            'config' => $config,
            'sections' => $servicesPageService->getServicesPageSections(),
        ]);
    }
}
