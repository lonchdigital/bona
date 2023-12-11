<?php

namespace App\Http\Actions\Admin\Delivery\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;
use App\Services\DeliveryPage\DeliveryPageService;

class ShowDeliveryEditPageAction extends BaseAction
{

    public function __invoke(
        ApplicationConfigService $applicationService,
        DeliveryPageService $deliveryService,
    )
    {
        return view('pages.admin.delivery.edit', [
            'availableLanguages' => $applicationService->getAvailableLanguages(),
            'deliveryConfig' => $deliveryService->getDeliveryConfig(),
        ]);
    }

}
