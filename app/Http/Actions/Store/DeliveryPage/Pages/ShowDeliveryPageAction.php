<?php

namespace App\Http\Actions\Store\DeliveryPage\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\DeliveryPage\DeliveryPageService;
use Abordage\LastModified\Facades\LastModified;
use Illuminate\Support\Facades\Cache;


class ShowDeliveryPageAction extends BaseAction
{
    public function __invoke(
        DeliveryPageService      $deliveryPageService,
    )
    {
        /*if( Cache::has('posts') ) {
            $posts = Cache::get('posts');
        } else {
            $posts = Posts::all();
            Cache::put('posts', $posts);
        }*/

        $config = Cache::remember('deliveryPage', 3600, function () use ($deliveryPageService) {
            return $deliveryPageService->getDeliveryConfig();
        });

        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        LastModified::set($config->updated_at);

        return view('pages.store.delivery-page', [
            'deliveryConfig' => $config,
        ]);
    }
}
