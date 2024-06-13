<?php

namespace App\Http\Actions\Store\Payment;

use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PartialPaymentStatusDataClass;
use App\Http\Requests\Store\Checkout\ConfirmPartialOrderRequest;
use App\Http\Resources\BaseActionResource;
//use App\Jobs\ProcessPaymentSuccessful;
use App\Models\Order;
//use App\Models\PaymentFailure;
use App\Services\Base\ServiceActionResult;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Log;

class ConfirmPartialPaymentAction
{
    public function __invoke(
        Order $order,
        ConfirmPartialOrderRequest $request,
        OrderService $orderService
    )
    {
//        $order = Order::query()->find($request->orderId);
//        $order = Order::query()->find($request->orderId)->first();

        if (in_array($request->paymentState, [PartialPaymentStatusDataClass::SUCCESS, PartialPaymentStatusDataClass::LOCKED])) {

//            $result = $orderService->updateOrderPaymentStatusId($order, OrderPaymentStatusesDataClass::STATUS_PAID);
//                ProcessPaymentSuccessful::dispatchAfterResponse($order);

        } elseif (in_array($request->paymentState, [PartialPaymentStatusDataClass::CANCELED, PartialPaymentStatusDataClass::FAIL])) {

            $result = ServiceActionResult::make(true, 'Failed to make payment');
//                PaymentFailure::updateOrCreate(['order_id' => $order->id]);

        }

        return BaseActionResource::make([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
            'redirect_to' => '',
        ]);
    }
}
