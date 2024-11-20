<?php

namespace App\Services\Payment;

use App\Services\Base\BaseService;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PaymentService extends BaseService
{
    public function payByCard(float $amount, int $orderId): string
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

    public function payByCardForm(float $amount, int $orderId): array
    {
        $formData = [
            'public_key' => config('liqpay.public_key'),
            'action' => 'pay',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => trans('base.payment_for_order') . $orderId,
            'order_id' => $orderId,
            'version' => 3,
            'language' => app()->getLocale(),
        ];
        Log::info('Build liqpay from on Our WebSie: ' . json_encode($formData));

        $jsonString = json_encode($formData);
        $data = base64_encode($jsonString);

        $signature = base64_encode(sha1(config('liqpay.private_key') . $data . config('liqpay.private_key'), true));

        return ['data' => $data, 'signature' => $signature];
    }


    public function createPrivateBankPartialPaymentOrder(Order $order, int $payment_period, string $merchant_type): ?array
    {
        $client = new Client();
        $redirect_url = route('store.checkout.thank-you', ['order' => $order->id]);
        $response_url = route('store.checkout.partial.payment');
        $store_password = env('PRIVATBANK_PASSWORD');
        $store_id = env('PRIVATBANK_STORE_ID');
        $signature = $this->makePartialPaymentSignature(
            $order, $payment_period, $merchant_type, $response_url, $redirect_url, $store_password, $store_id
        );

        $data = [
            "storeId" => $store_id,
            "orderId" => $order->id,
            "amount" => 0,
            "partsCount" => $payment_period,
            "merchantType" => $merchant_type,
            "products" => [],
            "responseUrl" => $response_url,
            "redirectUrl" => $redirect_url,
            "signature" => $signature
        ];
        foreach ($order->products as $product) {
            $count = $product->pivot->count;
            $product_price = round( $count * ($product->pivot->price + $product->pivot->attributes_price), 2, PHP_ROUND_HALF_DOWN);
            $data['amount'] += $product_price;
            $data['products'][] = [
                "name" => $product->name,
                "count" => $count,
                'price' => number_format(round($product->pivot->price + $product->pivot->attributes_price, 2, PHP_ROUND_HALF_DOWN), 2, '.', '')
            ];
        }
        $data['amount'] = number_format($data['amount'], 2, '.', '');
        try {
            $response = $client->post('https://payparts2.privatbank.ua/ipp/v2/payment/create', [
                'body' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $exception) {
            Log::error('Error during creating privatbank partial payment order: ' . $exception->getMessage());
            return null;
        }
    }

    private function makePartialPaymentSignature(
        Order $order,
        int $payment_period,
        string $merchant_type,
        string $response_url,
        string $redirect_url,
        string $store_password,
        string $store_id
    ): string
    {

        $product_str = '';
        $amount = 0;
        foreach ($order->products as $product) {
            $count = $product->pivot->count;
            $amount = $count * ($product->pivot->price + $product->pivot->attributes_price);
            $product_str .= ($product->name.$count.$this->withoutFloating($product->pivot->price + $product->pivot->attributes_price));
        }
        $str =  base64_encode(sha1(
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

    // Mono Bank
    public function createMonoBankPartialPaymentOrder(Order $order, int $payment_period, string $merchant_type)
    {
        $client = new Client();
        $redirect_url = route('store.checkout.thank-you', ['order' => $order->id]);
        $response_url = route('store.checkout.partial.payment');
        $mono_bank_api_url = env('MONOBANK_API_URL');
        $mono_bank_client_secret = env('MONOBANK_CLIENT_SECRET');
        $mono_bank_client_store_id = env('MONOBANK_CLIENT_STORE_ID');

        /*$signature = $this->makeMonoBankPartialPaymentSignature(
            $order, $payment_period, $merchant_type, $response_url, $redirect_url, $mono_bank_client_secret
        );*/

        $data['amount'] = 0;
        $data['products'] = [];
        foreach ($order->products as $product) {
            $count = $product->pivot->count;
            $product_price = round( $count * ($product->pivot->price + $product->pivot->attributes_price), 2, PHP_ROUND_HALF_DOWN);
            $data['amount'] += $product_price;
            $data['products'][] = [
                "name" => $product->name,
                "count" => (integer) $count,
                'sum' => (integer) number_format(round($product->pivot->price + $product->pivot->attributes_price, 2, PHP_ROUND_HALF_DOWN), 2, '.', '')
            ];
        }
        $data['amount'] = number_format($data['amount'], 2, '.', '');


        $request_array = [
            "store_order_id" => (string) $order->id,
            "client_phone" => "+380951000001",
            "total_sum" => (integer) $data['amount'],
            "invoice" => [
                "date" => "2024-11-17",
                "number" => "1234-1234",
                "point_id" => 1234,
                "source" => "INTERNET",
            ],
            "available_programs" => [
                [
                    "available_parts_count" => [10],
                    "type" => "payment_installments",
                ]
            ],
            "products" => $data['products'],
            "result_callback" => $redirect_url
        ];

        $signature = $this->makeMonoBankPartialPaymentSignature(
            $request_array, $mono_bank_client_secret
        );


        try {
            // Send request to Monobank
            $response = $client->post($mono_bank_api_url, [
                'headers' => [
                    'store-id' => $mono_bank_client_store_id,
                    'signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($request_array, JSON_UNESCAPED_UNICODE),
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                $data = json_decode($response->getBody(), true);
                return $data;
            }

            \Log::error('Monobank API Error', [
                'StatusCode' => $response->getStatusCode(),
                'response' => $response->getBody()->getContents(),
                'headers' => $response->getHeaders(),
            ]);
            return null;

        } catch (\Exception $e) {

            \Log::error('Monobank API Exception2', [
                'message' => $e->getMessage(),
//                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }


    }

    private function makeMonoBankPartialPaymentSignature( array $request_array, string $mono_bank_client_secret): string
    {
        $request_string = json_encode($request_array, JSON_UNESCAPED_UNICODE);
        return base64_encode(hash_hmac("sha256", $request_string, $mono_bank_client_secret, true));
    }

    /*private function makeMonoBankPartialPaymentSignature(
        Order $order,
        int $payment_period,
        string $merchant_type,
        string $response_url,
        string $redirect_url,
        string $mono_bank_client_secret,
    ): string
    {

        $request_string = '{ "store_order_id": "'.$order->id.'",
          "client_phone": "+380500000000",
          "total_sum": 234.32,
          "invoice": {
            "date": "2024-11-17",
            "number": "1234-1234",
            "point_id": 1234,
            "source": "INTERNET"
          },
          "available_programs": [
            {
              "available_parts_count": [
                10
              ],
              "type": "product _1"
            }
          ],
          "products": [
            {
              "name": "Телевизор",
              "count": 3,
              "sum": 9999.99
            }
          ],
          "result_callback": "http://bona.local/checkout/"'.$order->id.'"/thank",
          "additional_params": {
            "nds": 123.45,
            "seller_phone": "+380500000001",
            "ext_initial_sum": 100.01
            }
        }';

        return base64_encode(hash_hmac("sha256", $request_string, $mono_bank_client_secret, true));
    }*/


    private function withoutFloating(float $number): string
    {
        return (string)round($number, 2, PHP_ROUND_HALF_DOWN) * 100;
    }

    public function paypartByCardForm(float $amount, int $orderId): array
    {
        $formData = [
            'public_key' => config('liqpay.public_key'),
            'action' => 'pay',
            'paytypes' => 'paypart',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => trans('base.payment_for_order') . $orderId,
            'order_id' => $orderId,
            'version' => 3,
            'language' => app()->getLocale(),
        ];
        Log::info('Build liqpay from on Our WebSie: ' . json_encode($formData));

        $jsonString = json_encode($formData);
        $data = base64_encode($jsonString);

        $signature = base64_encode(sha1(config('liqpay.private_key') . $data . config('liqpay.private_key'), true));

        return ['data' => $data, 'signature' => $signature];
    }
}
