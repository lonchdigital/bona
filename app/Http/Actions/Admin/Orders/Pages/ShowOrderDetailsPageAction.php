<?php

namespace App\Http\Actions\Admin\Orders\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Order;
use App\Services\Currency\CurrencyService;
use App\Services\Order\OrderService;
use App\Support\Commerce\ProductBundle;

class ShowOrderDetailsPageAction extends BaseAction
{
    public function __invoke(
        Order $order,
        OrderService $orderService,
        CurrencyService $currencyService,
    ) {
        $order->loadMissing([
            'products.colors',
            'products.productType.attributes',
        ]);

        return view('pages.admin.orders.details', [
            'order' => $order,
            'orderProductGroups' => ProductBundle::group($order->products),
            'orderSummaryDetailed' => $orderService->getOrderSummary($order),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
