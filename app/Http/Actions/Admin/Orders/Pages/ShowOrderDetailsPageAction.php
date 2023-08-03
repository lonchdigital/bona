<?php

namespace App\Http\Actions\Admin\Orders\Pages;

use App\Models\Order;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Currency\CurrencyService;
use App\Services\Order\OrderService;

class ShowOrderDetailsPageAction extends BaseAction
{
    public function __invoke(
        Order $order,
        OrderService $orderService,
        CurrencyService $currencyService,
    )
    {
        return view('pages.admin.orders.details', [
            'order' => $order,
            'orderSummaryDetailed' => $orderService->getOrderSummary($order),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
