<?php

namespace App\Http\Actions\Store\DeliveryPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\DeliveryPage\DeliveryPageService;
use Abordage\LastModified\Facades\LastModified;

class ShowDeliveryPageAction extends BaseAction
{
    public function __invoke(
        DeliveryPageService      $deliveryPageService,
    )
    {
        $config = $deliveryPageService->getDeliveryConfig();
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        LastModified::set($config->updated_at);

        return view('pages.store.delivery-page', [
            'deliveryConfig' => $config,
        ]);
    }
}
