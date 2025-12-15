<?php

namespace App\Services\Payment;

use App\DataClasses\MonoBankOrderStateStatusesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use GuzzleHttp\Client;

class PaymentMonoBankService extends BaseService
{
    protected string $response_url;
    protected string $mono_bank_api_url;
    protected string $mono_bank_client_secret;
    protected string $mono_bank_client_store_id;

    public function __construct()
    {
        $this->response_url = route('store.checkout.partial.mono.bank.payment');
        $this->mono_bank_api_url = env('MONOBANK_API_URL');
        $this->mono_bank_client_secret = env('MONOBANK_CLIENT_SECRET');
        $this->mono_bank_client_store_id = env('MONOBANK_CLIENT_STORE_ID');
    }

    public function createMonoBankPartialPaymentOrder(Order $order, string $phone, string $period)
    {
        $client = new Client();

        $data = $this->collectAllProductsFromOrder($order);
        $data['amount'] = number_format($data['amount'], 2, '.', '');
        $currentDate = Carbon::now()->format('Y-m-d');

        $request_array = [
            "store_order_id" => (string) $order->id,
            "client_phone" => $phone,
            "total_sum" => (integer) $data['amount'],
            "invoice" => [
                "date" => $currentDate,
                "number" => "1234-1234",
                "point_id" => 1234,
                "source" => "INTERNET",
            ],
            "available_programs" => [
                [
                    "available_parts_count" => [$period],
                    "type" => "payment_installments",
                ]
            ],
            "products" => $data['products'],
            "result_callback" => $this->response_url
        ];
        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            // Send request to Monobank
            $response = $client->post($this->mono_bank_api_url . '/api/order/create', [
                'headers' => [
                    'store-id' => $this->mono_bank_client_store_id,
                    'signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($request_array, JSON_UNESCAPED_UNICODE),
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                $data = json_decode($response->getBody(), true);

                $order->mono_order_id = $data['order_id'];
                $order->payment_status_id = OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS;
                $order->save();

                return $data;
            }

            \Log::error('Monobank API Error', [
                'StatusCode' => $response->getStatusCode(),
                'response' => $response->getBody()->getContents(),
                'headers' => $response->getHeaders(),
            ]);
            return null;

        } catch (\Exception $e) {

            \Log::error('Monobank API Exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }

    }

    public function validateClientMonoBankPhone(string $phone): bool
    {
        $client = new Client();

        $request_array = [
            "phone" => $phone,
        ];

        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            $response = $client->post($this->mono_bank_api_url . '/api/client/validate', [
                'headers' => [
                    'store-id' => $this->mono_bank_client_store_id,
                    'signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($request_array, JSON_UNESCAPED_UNICODE),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['found'];

        } catch (\Exception $e) {

            \Log::error('Monobank API PHONE Exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }

    }

    public function rejectOrderMonoBank(Order $order) : ServiceActionResult
    {
        $client = new Client();

        $request_array = [
            "order_id" => $order->mono_order_id,
        ];
        /*$request_array = [
            "order_id" => "123e4567-e89b-12d3-a456-426614174000"
        ];*/

        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            $response = $client->post($this->mono_bank_api_url . '/api/order/reject', [
                'headers' => [
                    'store-id' => $this->mono_bank_client_store_id,
                    'signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($request_array, JSON_UNESCAPED_UNICODE),
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                $data = json_decode($response->getBody()->getContents(), true);

                if( isset($data['order_sub_state']) && $data['order_sub_state'] == 'REJECTED_BY_STORE' ) {
                    $order->mono_order_state = MonoBankOrderStateStatusesDataClass::STATUS_REJECTED;
                    $order->save();

                    return ServiceActionResult::make(true, trans('admin.order_rejected'));
                } else {

                    if(isset($data['message'])) {
                        return ServiceActionResult::make(false, $data['message']);
                    }

                }
            }

            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));

        } catch (\Exception $e) {

            \Log::error('Monobank API reject Exception', [
                'message' => $e->getMessage(),
            ]);

            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));
        }


    }

    public function confirmOrderMonoBank(Order $order) : ServiceActionResult
    {
        $client = new Client();

        $request_array = [
            "order_id" => $order->mono_order_id,
        ];
        /*$request_array = [
            "order_id" => "123e4567-e89b-12d3-a456-426614174000"
        ];*/

        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            $response = $client->post($this->mono_bank_api_url . '/api/order/confirm', [
                'headers' => [
                    'store-id' => $this->mono_bank_client_store_id,
                    'signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($request_array, JSON_UNESCAPED_UNICODE),
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                $data = json_decode($response->getBody()->getContents(), true);

                if( isset($data['state']) && $data['state'] == 'SUCCESS' ) {
                    $order->mono_order_state = MonoBankOrderStateStatusesDataClass::STATUS_CONFIRMED;
                    $order->save();

                    return ServiceActionResult::make(true, trans('admin.order_confirmed_by_store'));
                } else {

                    if(isset($data['message'])) {
                        return ServiceActionResult::make(false, $data['message']);
                    }

                }
            }

            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));

        } catch (\Exception $e) {

            \Log::error('Monobank API confirm Exception', [
                'message' => $e->getMessage(),
            ]);

            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));
        }

    }

    public function returnOrderMonoBank(Order $order) : ServiceActionResult
    {
        $client = new Client();

        $data = $this->getTotalSumOrder($order);
//        $data['amount'] = (integer) number_format($data['amount'], 2, '.', '');
        $data['amount'] = number_format($data['amount'], 2, '.', '');
//        $totalSum = $this->convertToCents($data['amount']);

        $request_array = [
            "order_id" => $order->mono_order_id,
            "return_money_to_card" => true,
            "store_return_id" => $order->id,
            "sum" => (integer) $data['amount']
        ];

        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            $response = $client->post($this->mono_bank_api_url . '/api/order/return', [
                'headers' => [
                    'store-id' => $this->mono_bank_client_store_id,
                    'signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($request_array, JSON_UNESCAPED_UNICODE),
            ]);


            if (in_array($response->getStatusCode(), [200])) {
                $data = json_decode($response->getBody()->getContents(), true);

                if( isset($data['status']) && $data['status'] == 'OK' ) {
                    $order->mono_order_state = MonoBankOrderStateStatusesDataClass::STATUS_RETURNED;
                    $order->save();

                    return ServiceActionResult::make(true, trans('admin.order_returned'));
                }
            }

            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));

        } catch (\Exception $e) {

            \Log::error('Monobank API return Exception', [
                'message' => $e->getMessage(),
            ]);

            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));
        }

    }


    private function makeMonoBankPartialPaymentSignature(array $request_array, string $mono_bank_client_secret): string
    {
        $request_string = json_encode($request_array, JSON_UNESCAPED_UNICODE);
        return base64_encode(hash_hmac("sha256", $request_string, $mono_bank_client_secret, true));
    }

    private function collectAllProductsFromOrder(Order $order) : array
    {
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

        return $data;
    }

    private function getTotalSumOrder(Order $order) : array
    {
        $data['amount'] = 0;

        foreach ($order->products as $product) {
            $count = $product->pivot->count;
            $product_price = round( $count * ($product->pivot->price + $product->pivot->attributes_price), 2, PHP_ROUND_HALF_DOWN);
            $data['amount'] += $product_price;
        }

        return $data;
    }

    private function convertToCents(float $amount): int
    {
        return (int)round($amount * 100);
    }

}
