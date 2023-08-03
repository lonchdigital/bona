<?php

namespace App\Http\Actions\Store\Cart\Pages;

use App\Http\Actions\Admin\BaseAction;

class ShowCartPageAction extends BaseAction
{
    public function __invoke()
    {
        return view('pages.store.cart');
    }
}
