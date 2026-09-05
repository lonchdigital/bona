<?php

namespace App\Http\Actions\Store\Checkout;

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

        if ($request->all()['payment_type_id'] == PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK) {
            if (is_null($authUser)) {
                $phone = $request->toDTO()->phone;
            } else {
                $phone = $authUser->getAttribute('phone');
            }
            $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

            $isValid = $paymentMonoBankService->validateClientMonoBankPhone($phone);
            if (! $isValid) {
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
        $order = $orderService->createOrderByCart($cart, $request->toDTO(), $authUser);

        if ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT) {
            return redirect()->to($orderAccessUrlService->liqPay($order));
        } elseif ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT_PAYPART) {

            $merchant_type = PaymentTypesDataClass::get($order->payment_type_id)['internal_name'];
            $response = $paymentService->createPrivateBankPartialPaymentOrder(
                $order,
                (int) $order->installment_period,
                $merchant_type,
            );

            if ($response !== null) {
                if ($response['state'] === 'SUCCESS') {
                    $route = 'https://payparts2.privatbank.ua/ipp/v2/payment?token='.$response['token'];
                } else {
                    $message = $response['message'] ?? ($response['errorMessage'] ?? 'Unknown error');
                    Log::error('Error during creating partial payment order: '.$message);
                    $route = $orderAccessUrlService->thankYou($order);
                }
            } else {
                $route = $orderAccessUrlService->thankYou($order);
            }

        } elseif ($order->payment_type_id === PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK) {

            $response = $paymentMonoBankService->createMonoBankPartialPaymentOrder(
                $order,
                $phone,
                (string) $order->installment_period,
            );
            if (! is_null($response)) {
                return redirect()->to($orderAccessUrlService->monoBankThankYou($order));
            } else {
                return redirect()
                    ->back()
                    ->withErrors(['unknown_error' => trans('base.something_went_wrong')])
                    ->withInput();
            }

        } else {
            return redirect()->to($orderAccessUrlService->thankYou($order));
        }

        return $this->handleActionResult($route, $request, ServiceActionResult::make(true, 'success'));
    }
}
