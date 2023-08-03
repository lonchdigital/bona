<?php

namespace App\Http\Actions\StaticData;

use App\Services\Currency\CurrencyService;

class GetStaticDataScript
{
    public function __invoke(
        CurrencyService $currencyService,
    )
    {
        $contents = view('static-data.script')
            ->with([
                'baseCurrency' => $currencyService->getBaseCurrency()
            ]);
        return response($contents)
            ->header('Content-Type', 'application/javascript');
    }
}
