<?php

namespace App\Http\Actions\UserProfile\Pages;

use App\Models\Order;
use App\Services\Currency\CurrencyService;
use App\Services\UserProfile\UserProfileService;
use App\Support\Commerce\ProductBundle;

class UserOrdersPageAction
{
    public function __invoke(
        UserProfileService $service,
        CurrencyService $currencyService,
    ) {
        $user = $service->getAuthUserData();
        $userOrders = Order::where('user_id', $user->id)
            ->with([
                'products.colors',
                'products.productType.attributes',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.user-profile.orders', [
            'user' => $user,
            'userOrders' => $userOrders,
            'orderProductGroups' => $userOrders->mapWithKeys(fn (Order $order) => [
                $order->id => ProductBundle::group($order->products),
            ]),
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
