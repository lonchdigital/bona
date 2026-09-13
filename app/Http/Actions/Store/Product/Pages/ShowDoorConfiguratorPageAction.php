<?php

namespace App\Http\Actions\Store\Product\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Product\DoorConfiguratorService;

class ShowDoorConfiguratorPageAction extends BaseAction
{
    public function __invoke(DoorConfiguratorService $configurator)
    {
        return view('pages.store.door-configurator', ['configuratorCatalog' => $configurator->catalog()]);
    }
}
