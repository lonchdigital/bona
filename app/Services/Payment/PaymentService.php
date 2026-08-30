<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Services\Base\BaseService;
use App\Services\Order\OrderAccessUrlService;
use App\Services\Pricing\PricingService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentService extends BaseService
{
    public function __construct(
        private readonly OrderAccessUrlService $orderAccessUrlService,
        private readonly PricingService $pricingService,
    ) {}

    public function payByCard(float $amount, int $orderId): string
    {
        $liqpay = new \LiqPay(config('liqpay.public_key'), config('liqpay.private_key'));

        $formData = [
            'action' => 'pay',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => trans('base.payment_for_order').$orderId,
            'order_id' => $orderId,
            'version' => 3,
            'language' => app()->getLocale(),
            'result_url' => $this->orderAccessUrlService->thankYou($orderId),
            'server_url' => route('payment.liqpay.callback'),
        ];

        Log::info('Build liqpay from with such data: '.json_encode($formData));

        return $liqpay->cnb_form($formData);
    }

    public function payByCardForm(float $amount, int $orderId): array
    {
        $formData = [
            'public_key' => config('liqpay.public_key'),
            'action' => 'pay',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => trans('base.payment_for_order').$orderId,
            'order_id' => $orderId,
            'version' => 3,
            'language' => app()->getLocale(),
            'result_url' => $this->orderAccessUrlService->thankYou($orderId),
            'server_url' => route('payment.liqpay.callback'),
        ];
        Log::info('Build liqpay from on Our WebSie: '.json_encode($formData));

        $jsonString = json_encode($formData);
        $data = base64_encode($jsonString);

        $signature = base64_encode(sha1(config('liqpay.private_key').$data.config('liqpay.private_key'), true));

        return ['data' => $data, 'signature' => $signature];
    }

    /**
     * Verify and decode a server-to-server LiqPay callback.
     *
     * The browser callback is intentionally never trusted: only LiqPay can
     * produce a signature containing the merchant's private key.
     */
    public function decodeLiqPayCallback(string $data, string $signature): array
    {
        $privateKey = (string) config('liqpay.private_key');

        if ($privateKey === '') {
            throw new InvalidArgumentException('LiqPay private key is not configured.');
        }

        $expectedSignature = base64_encode(sha1($privateKey.$data.$privateKey, true));

        if (! hash_equals($expectedSignature, $signature)) {
            throw new InvalidArgumentException('Invalid LiqPay callback signature.');
        }

        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid LiqPay callback data.');
        }

        $payload = json_decode($decoded, true);
        if (! is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid LiqPay callback payload.');
        }

        return $payload;
    }

    public function createPrivateBankPartialPaymentOrder(Order $order, int $payment_period, string $merchant_type): ?array
    {
        $client = new Client;
        $redirect_url = $this->orderAccessUrlService->thankYou($order);
        $response_url = route('store.checkout.partial.payment');
        $store_password = (string) config('payment.privatbank.password');
        $store_id = (string) config('payment.privatbank.store_id');
        if ($store_password === '' || $store_id === '') {
            Log::error('PrivatBank instalments are not configured.');

            return null;
        }

        $amount = round($this->pricingService->forOrder($order)['total'], 2);
        $signature = $this->makePartialPaymentSignature(
            $order, $amount, $payment_period, $merchant_type, $response_url, $redirect_url, $store_password, $store_id
        );

        $data = [
            'storeId' => $store_id,
            'orderId' => $order->id,
            'amount' => number_format($amount, 2, '.', ''),
            'partsCount' => $payment_period,
            'merchantType' => $merchant_type,
            'products' => [],
            'responseUrl' => $response_url,
            'redirectUrl' => $redirect_url,
            'signature' => $signature,
        ];
        foreach ($order->products as $product) {
            $count = $product->pivot->count;
            $data['products'][] = [
                'name' => $product->name,
                'count' => $count,
                'price' => number_format(round($product->pivot->price + $product->pivot->attributes_price, 2, PHP_ROUND_HALF_DOWN), 2, '.', ''),
            ];
        }
        try {
            $response = $client->post('https://payparts2.privatbank.ua/ipp/v2/payment/create', [
                'body' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $exception) {
            Log::error('Error during creating privatbank partial payment order: '.$exception->getMessage());

            return null;
        }
    }

    private function makePartialPaymentSignature(
        Order $order,
        float $amount,
        int $payment_period,
        string $merchant_type,
        string $response_url,
        string $redirect_url,
        string $store_password,
        string $store_id
    ): string {

        $product_str = '';
        /*$amount = 0;
        foreach ($order->products as $product) {
            $count = $product->pivot->count;
            $amount = $count * ($product->pivot->price + $product->pivot->attributes_price);
            $product_str .= ($product->name.$count.$this->withoutFloating($product->pivot->price + $product->pivot->attributes_price));
        }*/
        foreach ($order->products as $product) {
            $count = $product->pivot->count;
            $price = $product->pivot->price + $product->pivot->attributes_price;
            $product_str .= ($product->name.$count.$this->withoutFloating($price));
        }
        $str = base64_encode(sha1(
            $store_password
            .$store_id
            .$order->id
            .$this->withoutFloating($amount)
            .$payment_period
            .$merchant_type
            .$response_url
            .$redirect_url
            .$product_str
            .$store_password,
            1
        ));

        return $str;
    }

    /*private function withoutFloating(float $number): string
    {
        return (string)round($number, 2, PHP_ROUND_HALF_DOWN) * 100;
    }*/
    private function withoutFloating(float $number): string
    {
        return number_format(round($number * 100, 0, PHP_ROUND_HALF_DOWN), 0, '', '');
    }

    public function paypartByCardForm(float $amount, int $orderId): array
    {
        $formData = [
            'public_key' => config('liqpay.public_key'),
            'action' => 'pay',
            'paytypes' => 'paypart',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => trans('base.payment_for_order').$orderId,
            'order_id' => $orderId,
            'version' => 3,
            'language' => app()->getLocale(),
        ];
        Log::info('Build liqpay from on Our WebSie: '.json_encode($formData));

        $jsonString = json_encode($formData);
        $data = base64_encode($jsonString);

        $signature = base64_encode(sha1(config('liqpay.private_key').$data.config('liqpay.private_key'), true));

        return ['data' => $data, 'signature' => $signature];
    }
}
