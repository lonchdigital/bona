<?php

namespace App\Http\Actions\Store\Checkout\Pages;

use App\Models\Order;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Currency\CurrencyService;
use App\Models\ProductType;
use App\Services\Cart\CartService;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Http\Actions\Store\Cart\NeedCart;
use Illuminate\Support\Facades\Log;

class ShowCheckoutThankYouPageAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        Order $order,
        CartService $cartService,
        CurrencyService $currencyService,
    )
    {
//        $baseCurrency = $currencyService->getBaseCurrency();
        if ($order->payment_status_id === OrderPaymentStatusesDataClass::STATUS_UNPAID) {
            return view('pages.store.payment-failure');
        }
        /*try {
            $cart = $this->getCart($cartService);
            $cart->products()->sync([]);
            $cart->delete();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }*/

        return view('pages.store.checkout-thank-you', [
            'order' => $order,
            'baseCurrency' => $currencyService->getBaseCurrency(),
            'productType' => ProductType::first(),
        ]);
    }
}
