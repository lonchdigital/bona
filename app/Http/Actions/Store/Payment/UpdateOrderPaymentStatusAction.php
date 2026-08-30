<?php

namespace App\Http\Actions\Store\Payment;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class UpdateOrderPaymentStatusAction extends BaseAction
{
    public function __invoke(
        Request $request,
        OrderService $orderService,
        PaymentService $paymentService,
    ) {
        $validated = $request->validate([
            'data' => ['required', 'string'],
            'signature' => ['required', 'string'],
        ]);

        try {
            $payload = $paymentService->decodeLiqPayCallback(
                $validated['data'],
                $validated['signature'],
            );
        } catch (InvalidArgumentException) {
            abort(403, 'Invalid LiqPay callback.');
        }

        foreach (['order_id', 'status', 'amount', 'currency'] as $requiredField) {
            if (! array_key_exists($requiredField, $payload)) {
                abort(422, 'Incomplete LiqPay callback.');
            }
        }

        if (isset($payload['public_key']) && ! hash_equals((string) config('liqpay.public_key'), (string) $payload['public_key'])) {
            abort(403, 'Invalid LiqPay merchant.');
        }

        $order = Order::query()->findOrFail($payload['order_id']);

        if ((int) $order->payment_type_id !== PaymentTypesDataClass::CARD_PAYMENT) {
            abort(422, 'The order does not use LiqPay.');
        }

        $expectedAmountInCents = (int) round($orderService->getOrderSummary($order)['total'] * 100);
        $receivedAmountInCents = is_numeric($payload['amount'])
            ? (int) round((float) $payload['amount'] * 100)
            : -1;

        if ($payload['currency'] !== 'UAH' || $receivedAmountInCents !== $expectedAmountInCents) {
            abort(422, 'LiqPay callback amount does not match the order.');
        }

        $successfulStatuses = ['success'];
        if (! app()->environment('production')) {
            $successfulStatuses[] = 'sandbox';
        }

        if (in_array($payload['status'], $successfulStatuses, true)) {
            $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);
        } elseif (
            in_array($payload['status'], ['failure', 'error'], true)
            && (int) $order->payment_status_id !== OrderPaymentStatusesDataClass::STATUS_PAID
        ) {
            $orderService->updateOrderPaymentStatusIdWithoutEmail($order, OrderPaymentStatusesDataClass::STATUS_DECLINED);
        }

        return response('ok');
    }
}
