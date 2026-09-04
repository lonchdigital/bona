<?php

namespace App\Http\Actions\Store\ServicesPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ServicesPageSections;
use App\Services\ServicesPage\ServicesPageService;
use App\Support\LastModified;

class ShowServicePageAction extends BaseAction
{
    public function __invoke(
        ServicesPageSections $serviceSlug,
        ServicesPageService $servicesPageService,
    ) {
        $serviceSlug->meta_tags = $this->handleFollowTag($serviceSlug->meta_tags);
        LastModified::set($serviceSlug->updated_at);

        return view('pages.store.service-detail', [
            'service' => $serviceSlug,
            'otherServices' => $servicesPageService->getOtherServices($serviceSlug),
        ]);
    }
}
