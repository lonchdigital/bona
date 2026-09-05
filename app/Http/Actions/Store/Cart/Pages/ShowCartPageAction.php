<?php

namespace App\Http\Actions\Store\Cart\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\ServicesPage\ServicesPageService;
use Illuminate\Support\Str;

class ShowCartPageAction extends BaseAction
{
    public function __invoke(ServicesPageService $servicesPageService)
    {
        $services = $servicesPageService->getServicesPageSections()
            ->filter(fn ($service) => filled($service->slug) && filled($service->title))
            ->values();

        $measurementService = $services->first(
            fn ($service) => Str::contains($service->slug, ['vyklyk-maistra', 'zamer', 'vymir', 'measurement'])
        ) ?? $services->first();

        return view('pages.store.cart', [
            'cartServices' => $services->take(2),
            'measurementService' => $measurementService,
        ]);
    }
}
