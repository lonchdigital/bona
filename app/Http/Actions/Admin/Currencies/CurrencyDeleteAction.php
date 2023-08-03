<?php

namespace App\Http\Actions\Admin\Currencies;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Currency;
use App\Services\Currency\CurrencyService;
use Illuminate\Http\Request;

class CurrencyDeleteAction extends BaseAction
{
    public function __invoke(Currency $currency, Request $request, CurrencyService $service)
    {
        $result = $service->deleteCurrency($currency);

        return $this->handleActionResult(route('admin.currency.list.page'), $request, $result);
    }
}
