<?php

namespace App\Http\Actions\Admin\Currencies\Pages;

use App\Models\Currency;

class ShowCurrencyEditPageAction
{
    public function __invoke(Currency $currency)
    {
        return view('pages.admin.currencies.edit', [
            'currency' => $currency,
        ]);
    }
}
