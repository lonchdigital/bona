<?php

namespace App\Http\Actions\Store\DeliveryPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\DeliveryConfig;
use App\Services\DeliveryPage\DeliveryPageService;
use Abordage\LastModified\Facades\LastModified;

class ShowDeliveryPageAction extends BaseAction
{
    public function __invoke(
        DeliveryPageService      $deliveryPageService,
    )
    {
        LastModified::set(DeliveryConfig::first()->updated_at);

        return view('pages.store.delivery-page', [
            'deliveryConfig' => $deliveryPageService->getDeliveryConfig(),
        ]);
    }
}
