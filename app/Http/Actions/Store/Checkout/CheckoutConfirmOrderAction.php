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
use App\Services\Payment\PaymentMonoBankService;
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
        PaymentMonoBankService $paymentMonoBankService,
    )
    {
        $authUser = $this->getAuthUser();

        if( $request->all()['payment_type_id'] == PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK ) {
            if(is_null($authUser)) {
                $phone = $request->toDTO()->phone;
            } else {
                $phone = $authUser->getAttribute('phone');
            }
            $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

            $isValid = $paymentMonoBankService->validateClientMonoBankPhone($phone);
            if (!$isValid) {
                return redirect()
                    ->back()
                    ->withErrors(['phone' => trans('base.checkout_payment_paypart_mono_bank_unavailable')])
                    ->withInput();
            }
        }

        $cart = $this->getCart($cartService);
        $order = $orderService->createOrderByCart($cart, $request->toDTO(), $authUser);


        if ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT) {
            return response()->redirectToRoute('store.payment.liq-pay.ordinary', ['order' => $order]);
        } elseif ( $order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT_PAYPART ) {

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

        } elseif( $order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK ) {

            $response = $paymentMonoBankService->createMonoBankPartialPaymentOrder($order, $phone);
            if (!is_null($response)) {
                return response()->redirectToRoute('store.checkout.thank-you.mono-bank', ['order' => $order]);
            } else {
                return redirect()
                    ->back()
                    ->withErrors(['unknown_error' => trans('base.something_went_wrong')])
                    ->withInput();
            }

        } else {
            return response()->redirectToRoute('store.checkout.thank-you', ['order' => $order->id]);
        }

        return $this->handleActionResult($route, $request, ServiceActionResult::make(true, 'success'));
    }
}
