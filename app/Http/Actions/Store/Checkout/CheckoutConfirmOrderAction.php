<?php

namespace App\Http\Actions\Store\Checkout;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Actions\Store\Cart\NeedCart;
use App\Http\Requests\Store\Checkout\CheckoutConfirmOrderRequest;
use App\Services\Base\ServiceActionResult;
use App\Services\Cart\CartService;
use App\Services\Order\OrderAccessUrlService;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentMonoBankService;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CheckoutConfirmOrderAction extends BaseAction
{
    use NeedCart;

    public function __invoke(
        CheckoutConfirmOrderRequest $request,
        OrderService $orderService,
        CartService $cartService,
        PaymentService $paymentService,
        PaymentMonoBankService $paymentMonoBankService,
        OrderAccessUrlService $orderAccessUrlService,
    ) {
        $authUser = $this->getAuthUser();
        $checkout = $request->toDTO();
        $phone = null;

        if ($checkout->paymentTypeId === PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK) {
            if (is_null($authUser)) {
                $phone = $checkout->phone;
            } else {
                $phone = $authUser->getAttribute('phone');
            }
            $phone = preg_replace('/[\s\-\(\)]/', '', (string) $phone);

            if ($phone === '') {
                return redirect()
                    ->back()
                    ->withErrors(['phone' => trans('base.checkout_payment_paypart_mono_bank_unavailable')])
                    ->withInput();
            }

            $validation = $paymentMonoBankService->validateClientMonoBankPhone($phone);
            if (! $validation->successful) {
                return redirect()
                    ->back()
                    ->withErrors(['payment_type_id' => trans('base.checkout_payment_service_temporarily_unavailable')])
                    ->withInput();
            }

            if (! ($validation->data['found'] ?? false)) {
                return redirect()
                    ->back()
                    ->withErrors(['phone' => trans('base.checkout_payment_paypart_mono_bank_unavailable')])
                    ->withInput();
            }
        }

        $cart = $this->getExistingCart($cartService);
        if (! $cart || ! $cart->products()->exists()) {
            throw ValidationException::withMessages([
                'cart' => trans('base.cart_is_empty'),
            ]);
        }

        $cartService->normalizeLegacyBundles($cart);
        $order = $orderService->createOrderByCart($cart, $checkout, $authUser);

        if ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT) {
            return redirect()->to($orderAccessUrlService->liqPay($order));
        } elseif ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT_PAYPART) {

            $merchant_type = PaymentTypesDataClass::get($order->payment_type_id)['internal_name'];
            $response = $paymentService->createPrivateBankPartialPaymentOrder(
                $order,
                (int) $order->installment_period,
                $merchant_type,
            );

            if ($response->successful) {
                $route = 'https://payparts2.privatbank.ua/ipp/v2/payment?token='.$response->data['token'];
            } else {
                $orderService->updateOrderPaymentStatusIdWithoutEmail(
                    $order,
                    OrderPaymentStatusesDataClass::STATUS_DECLINED,
                );
                Log::error('PrivatBank instalment checkout could not be started.', [
                    'order_id' => $order->id,
                    'status_code' => $response->statusCode,
                    'trace_id' => $response->traceId,
                ]);
                $route = $orderAccessUrlService->thankYou($order);
            }

        } elseif ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK) {

            $response = $paymentMonoBankService->createMonoBankPartialPaymentOrder(
                $order,
                $phone,
                (string) $order->installment_period,
            );
            if ($response->successful) {
                return redirect()->to($orderAccessUrlService->monoBankThankYou($order));
            }

            $orderService->updateOrderPaymentStatusIdWithoutEmail(
                $order,
                OrderPaymentStatusesDataClass::STATUS_DECLINED,
            );
            Log::error('Monobank instalment checkout could not be started.', [
                'order_id' => $order->id,
                'status_code' => $response->statusCode,
                'trace_id' => $response->traceId,
            ]);

            return redirect()->to($orderAccessUrlService->monoBankThankYou($order));

        } else {
            return redirect()->to($orderAccessUrlService->thankYou($order));
        }

        return $this->handleActionResult($route, $request, ServiceActionResult::make(true, 'success'));
    }
}
