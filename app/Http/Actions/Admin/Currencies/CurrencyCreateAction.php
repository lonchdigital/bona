<?php

namespace App\Http\Actions\Admin\Currencies;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Currency\CurrencyEditRequest;
use App\Services\Currency\CurrencyService;

class CurrencyCreateAction extends BaseAction
{
    public function __invoke(CurrencyEditRequest $request, CurrencyService $service)
    {
        $result = $service->createCurrency($request->toDTO());

        return $this->handleActionResult(route('admin.currency.list.page'), $request, $result);
    }
}
