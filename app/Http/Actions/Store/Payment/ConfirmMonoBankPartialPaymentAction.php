<?php

namespace App\Http\Actions\Store\Payment;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Checkout\ConfirmMonoBankPartialOrderRequest;
use App\Models\Order;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentMonoBankService;
use Illuminate\Support\Facades\Log;

class ConfirmMonoBankPartialPaymentAction extends BaseAction
{
    public function __invoke(
        ConfirmMonoBankPartialOrderRequest $request,
        OrderService $orderService,
        PaymentMonoBankService $paymentMonoBankService,
    ) {
        /*
         * This endpoint moves an order to paid and is open to the internet.
         * Nothing used to establish that the request came from Monobank, so
         * anyone able to name an order could post here and be believed.
         */
        if (! $paymentMonoBankService->isCallbackAuthentic($request->getContent(), $request->header('signature'))) {
            Log::warning('Monobank callback refused: signature did not match.', [
                'order_id' => $request->input('order_id'),
            ]);

            return response('', 403);
        }

        $order = Order::where('mono_order_id', $request->order_id)->first();

        if (! $order) {
            // Nothing to move. Answered plainly so the bank stops retrying.
            Log::warning('Monobank callback for an order we do not have.', [
                'order_id' => $request->input('order_id'),
            ]);

            return response('', 404);
        }

        if ((int) $order->payment_type_id !== PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK) {
            return response('', 422);
        }

        // CLIENT_APPROVED_PUSH
        if ($request->state === 'IN_PROCESS' && $request->order_sub_state === 'WAITING_FOR_STORE_CONFIRM') {
            $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);
        } elseif ($request->state === 'FAIL' && (int) $order->payment_status_id !== OrderPaymentStatusesDataClass::STATUS_PAID) {
            $failureStatus = match ($request->order_sub_state) {
                'REJECTED_BY_CLIENT' => OrderPaymentStatusesDataClass::REJECTED_BY_CLIENT,
                'CLIENT_PUSH_TIMEOUT' => OrderPaymentStatusesDataClass::CLIENT_PUSH_TIMEOUT,
                default => OrderPaymentStatusesDataClass::STATUS_DECLINED,
            };

            $orderService->updateOrderPaymentStatusIdWithoutEmail($order, $failureStatus);

            Log::notice('Monobank instalment application failed.', [
                'order_id' => $order->id,
                'order_sub_state' => $request->order_sub_state,
            ]);
        } else {
            Log::info('Monobank callback in a state we do not act on.', [
                'order_id' => $order->id,
                'state' => $request->state,
                'order_sub_state' => $request->order_sub_state,
            ]);
        }

        return '';
    }
}
