<?php

namespace App\Http\Actions\Admin\Currencies;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Currency\CurrencyEditRequest;
use App\Models\Currency;
use App\Services\Currency\CurrencyService;

class CurrencyEditAction extends BaseAction
{
    public function __invoke(Currency $currency, CurrencyEditRequest $request, CurrencyService $service)
    {
        $result = $service->editCurrency($currency, $request->toDTO());

        return $this->handleActionResult(route('admin.currency.list.page'), $request, $result);
    }
}
