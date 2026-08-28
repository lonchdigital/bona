<?php

namespace App\Http\Actions\Store\Payment;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PartialPaymentStatusDataClass;
use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Checkout\ConfirmPartialOrderRequest;
use App\Http\Resources\BaseActionResource;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Services\Order\OrderService;

class ConfirmPartialPaymentAction extends BaseAction
{
    public function __invoke(
        ConfirmPartialOrderRequest $request,
        OrderService $orderService
    )
    {

        $order = Order::find($request->orderId);

        if (!$order) {
            // Signed, but naming an order we do not have. Answered plainly
            // rather than fataling on a null.
            Log::warning('PrivatBank callback for an order we do not have.', [
                'orderId' => $request->orderId,
            ]);

            return response('', 404);
        }

        if (in_array($request->paymentState, [PartialPaymentStatusDataClass::SUCCESS, PartialPaymentStatusDataClass::LOCKED])) {
            $result = $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);
        } elseif (in_array($request->paymentState, [PartialPaymentStatusDataClass::CANCELED, PartialPaymentStatusDataClass::FAIL])) {
            $result = $orderService->updateOrderPaymentStatusIdWithoutEmail($order, OrderPaymentStatusesDataClass::STATUS_UNPAID);
        }

        if (!isset($result)) {
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
