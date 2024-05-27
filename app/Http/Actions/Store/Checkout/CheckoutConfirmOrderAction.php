<?php

namespace App\Http\Actions\Store\Checkout;

use App\DataClasses\PaymentTypesDataClass;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Http\Requests\Store\Checkout\CheckoutConfirmOrderRequest;
use App\Services\Payment\PaymentService;

class CheckoutConfirmOrderAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        CheckoutConfirmOrderRequest $request,
        OrderService $orderService,
        CartService $cartService,
        PaymentService $paymentService,
    )
    {
        $cart = $this->getCart($cartService);

        $order = $orderService->createOrderByCart($cart, $request->toDTO(), $this->getAuthUser());


//        dd($order->payment_type_id, PaymentTypesDataClass::CARD_PAYMENT);

        if ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT) {
            return response()->redirectToRoute('store.payment.page', ['order' => $order->id]);
        } else {
            return response()->redirectToRoute('store.checkout.thank-you', ['order' => $order->id]);
        }


    }
}
