<?php

namespace App\Http\Actions\Admin\Currencies\Pages;

use App\Services\Currency\CurrencyService;

class ShowCurrenciesListPageAction
{
    public function __invoke(CurrencyService $service)
    {
        return view('pages.admin.currencies.list', [
            'currenciesPaginated' => $service->getCurrenciesPaginated(),
        ]);
    }
}
