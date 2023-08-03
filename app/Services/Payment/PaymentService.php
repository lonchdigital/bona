<?php

namespace App\Services\Payment;

use App\Services\Base\BaseService;
use Illuminate\Support\Facades\Log;

class PaymentService extends BaseService
{
    public function payByCard(float $amount, int $orderId,): string
    {
        $liqpay = new \LiqPay(config('liqpay.public_key'), config('liqpay.private_key'));

        $formData = [
            'action' => 'pay',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => trans('base.payment_for_order') . $orderId,
            'order_id' => $orderId,
            'version' => 3,
            'language' => app()->getLocale(),
            'result_url' => route('store.checkout.thank-you', ['order' => $orderId]),
            'server_url' => route('payment.update-payment-status', ['order' => $orderId]),
        ];

        Log::info('Build liqpay from with such data: ' . json_encode($formData));

        return $liqpay->cnb_form($formData);
    }
}
