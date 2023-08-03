<?php

namespace App\Http\Actions\Admin\Orders\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Order\OrderFilterRequest;
use App\Services\Currency\CurrencyService;
use App\Services\Order\OrderService;

class ShowOrdersListPageAction extends BaseAction
{
    public function __invoke(
        OrderFilterRequest $request,
        OrderService $orderService,
        CurrencyService $currencyService,
    )
    {
        $dto = $request->toDTO();

        return view('pages.admin.orders.list', [
            'searchData' => $dto,
            'ordersPaginated' => $orderService->getOrdersPaginated($dto),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
