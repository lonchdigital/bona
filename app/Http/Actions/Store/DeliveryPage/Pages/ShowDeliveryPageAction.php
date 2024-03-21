<?php

namespace App\Http\Actions\Store\DeliveryPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\DeliveryPage\DeliveryPageService;

class ShowDeliveryPageAction extends BaseAction
{
    public function __invoke(
        DeliveryPageService      $deliveryPageService,
    )
    {
        return view('pages.store.delivery-page', [
            'deliveryConfig' => $deliveryPageService->getDeliveryConfig(),
        ]);
    }
}
