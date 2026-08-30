<?php

namespace App\Services\Order;

use App\Models\Order;
use Illuminate\Support\Facades\URL;

class OrderAccessUrlService
{
    public function thankYou(Order|int $order): string
    {
        return $this->signedRoute('store.checkout.thank-you', $order);
    }

    public function monoBankThankYou(Order|int $order): string
    {
        return $this->signedRoute('store.checkout.thank-you.mono-bank', $order);
    }

    public function liqPay(Order|int $order): string
    {
        return $this->signedRoute('store.payment.liq-pay.ordinary', $order);
    }

    private function signedRoute(string $name, Order|int $order): string
    {
        return URL::temporarySignedRoute(
            $name,
            now()->addDay(),
            ['order' => $order instanceof Order ? $order->getKey() : $order],
        );
    }
}
