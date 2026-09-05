<?php

namespace App\Http\Actions\Store\Product\Pages;

use App\Http\Actions\Admin\BaseAction;

class ShowDoorConfiguratorPageAction extends BaseAction
{
    public function __invoke()
    {
        return view('pages.store.door-configurator');
    }
}
