<?php

namespace App\Http\Actions\UserProfile\Pages;

use App\Models\Order;
use App\Services\UserProfile\UserProfileService;
use App\Services\Currency\CurrencyService;

class UserOrdersPageAction
{
    public function __invoke(
        UserProfileService $service,
        CurrencyService $currencyService,
    )
    {
        $user = $service->getAuthUserData();
        $userOrders = Order::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('pages.user-profile.orders', [
            'user' => $user,
            'userOrders' => $userOrders,
            'baseCurrency' => $currencyService->getBaseCurrency(),
        ]);
    }
}
