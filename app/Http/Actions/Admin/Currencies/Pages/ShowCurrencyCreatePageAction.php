<?php

namespace App\Http\Actions\Admin\Currencies\Pages;

class ShowCurrencyCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.currencies.edit');
    }
}
