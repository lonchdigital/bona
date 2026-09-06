<?php

namespace App\Http\Actions\Store\Payment;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PartialPaymentStatusDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Checkout\ConfirmPartialOrderRequest;
use App\Http\Resources\BaseActionResource;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Log;

class ConfirmPartialPaymentAction extends BaseAction
{
    public function __invoke(
        ConfirmPartialOrderRequest $request,
        OrderService $orderService
    ) {

        $order = Order::find($request->orderId);

        if (! $order) {
            // Signed, but naming an order we do not have. Answered plainly
            // rather than fataling on a null.
            Log::warning('PrivatBank callback for an order we do not have.', [
                'orderId' => $request->orderId,
            ]);

            return response('', 404);
        }

        if ((int) $order->payment_type_id !== PaymentTypesDataClass::CARD_PAYMENT_PAYPART) {
            return response('', 422);
        }

        if (in_array($request->paymentState, [PartialPaymentStatusDataClass::SUCCESS, PartialPaymentStatusDataClass::LOCKED], true)) {
            $result = $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);
        } elseif (
            in_array($request->paymentState, [PartialPaymentStatusDataClass::CANCELED, PartialPaymentStatusDataClass::FAIL], true)
            && (int) $order->payment_status_id !== OrderPaymentStatusesDataClass::STATUS_PAID
        ) {
            $result = $orderService->updateOrderPaymentStatusIdWithoutEmail($order, OrderPaymentStatusesDataClass::STATUS_DECLINED);
        }

        if (! isset($result)) {
            // A state we do not act on — neither paid nor failed. Nothing to
            // record, and nothing to crash over: $result used to be read here
            // whether or not either branch above had set it.
            Log::info('PrivatBank callback in a state we do not act on.', [
                'orderId' => $request->orderId,
                'paymentState' => $request->paymentState,
            ]);

            return response('', 204);
        }

        return BaseActionResource::make([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'redirect_to' => '',
        ]);
    }
}
