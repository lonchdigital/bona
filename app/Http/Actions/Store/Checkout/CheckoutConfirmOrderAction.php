<?php

namespace App\Http\Actions\Store\Checkout;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Services\Base\ServiceActionResult;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Http\Requests\Store\Checkout\CheckoutConfirmOrderRequest;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Log;

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


        if ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT) {
            return response()->redirectToRoute('store.payment.liq-pay.ordinary', ['order' => $order->id]);
        } elseif ( $order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT_PAYPART ) {

            Log::error('ha ha: I am here 9');

//            return response()->redirectToRoute('store.payment.liq-pay.paypart', ['order' => $order->id]);

            $merchant_type = PaymentTypesDataClass::get($order->payment_type_id)['internal_name'];
            $response = $paymentService->createPrivateBankPartialPaymentOrder($order, $request->payment_period, $merchant_type);

            if ($response !== null) {
                if ($response['state'] === 'SUCCESS') {
                    $route = 'https://payparts2.privatbank.ua/ipp/v2/payment?token='.$response['token'];
                } else {
                    $message = $response['message'] ?? ($response['errorMessage'] ?? 'Unknown error');
                    Log::error('Error during creating partial payment order: '.$message);
                    $route = route('store.checkout.thank-you', ['order' => $order->id]);
                }
            } else {
                $route = route('store.checkout.thank-you', ['order' => $order->id]);
            }

        } else {
            return response()->redirectToRoute('store.checkout.thank-you', ['order' => $order->id]);
        }


        return $this->handleActionResult($route, $request, ServiceActionResult::make(true, 'success'));
    }
}
