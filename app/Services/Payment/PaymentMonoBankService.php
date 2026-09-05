<?php

namespace App\Services\Payment;

use App\DataClasses\MonoBankOrderStateStatusesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Models\Order;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Pricing\PricingService;
use App\Support\Payment\InstallmentPaymentLines;
use Carbon\Carbon;
use GuzzleHttp\Client;

class PaymentMonoBankService extends BaseService
{
    protected string $response_url;

    protected string $mono_bank_api_url;

    protected string $mono_bank_client_secret;

    protected string $mono_bank_client_store_id;

    protected string $mono_bank_point_id;

    /*
     * These were assigned straight from env() into typed string properties, so
     * an environment without the Monobank credentials could not build this
     * class at all — and checkout asks for it on every order, whatever payment
     * was chosen. One missing line in .env took the whole checkout down rather
     * than just instalments through Monobank.
     */
    public function __construct(
        private readonly PricingService $pricingService,
    ) {
        $this->response_url = route('store.checkout.partial.mono.bank.payment');
        $this->mono_bank_api_url = (string) config('payment.monobank.api_url');
        $this->mono_bank_client_secret = (string) config('payment.monobank.client_secret');
        $this->mono_bank_client_store_id = (string) config('payment.monobank.store_id');
        $this->mono_bank_point_id = (string) config('payment.monobank.point_id');
    }

    /**
     * Whether this shop is set up to offer Monobank instalments at all.
     */
    public function isConfigured(): bool
    {
        return $this->mono_bank_api_url !== ''
            && $this->mono_bank_client_secret !== ''
            && $this->mono_bank_client_store_id !== ''
            && $this->mono_bank_point_id !== '';
    }

    /**
     * Whether a callback really came from Monobank.
     *
     * They sign the body the same way we sign ours — HMAC-SHA256 with the
     * store secret — and send it in a header. Nothing checked it, so anyone
     * who could name an order was able to post to the callback and have it
     * marked paid.
     *
     * Compared with hash_equals so the comparison itself gives nothing away.
     */
    public function isCallbackAuthentic(string $rawBody, ?string $signature): bool
    {
        if (! $this->isConfigured() || ! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = base64_encode(
            hash_hmac('sha256', $rawBody, $this->mono_bank_client_secret, true)
        );

        return hash_equals($expected, $signature);
    }

    public function createMonoBankPartialPaymentOrder(Order $order, string $phone, string $period)
    {
        if (! $this->isConfigured()) {
            \Log::error('Monobank instalments are not configured.');

            return null;
        }

        $client = new Client;

        $request_array = $this->createOrderPayload($order, $phone, $period);
        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            // Send request to Monobank
            $response = $client->post($this->mono_bank_api_url.'/api/order/create', [
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

    public function createOrderPayload(Order $order, string $phone, string|int $period): array
    {
        $data = $this->collectAllProductsFromOrder($order);

        return [
            'store_order_id' => (string) $order->id,
            'client_phone' => $phone,
            'total_sum' => $data['amount'],
            'invoice' => [
                'date' => Carbon::now()->format('Y-m-d'),
                'number' => 'INV-'.$order->id,
                'point_id' => $this->mono_bank_point_id,
                'source' => 'INTERNET',
            ],
            'available_programs' => [
                [
                    'available_parts_count' => [(int) $period],
                    'type' => 'payment_installments',
                ],
            ],
            'products' => $data['products'],
            'result_callback' => $this->response_url,
        ];
    }

    public function validateClientMonoBankPhone(string $phone): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $client = new Client;

        $request_array = [
            'phone' => $phone,
        ];

        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            $response = $client->post($this->mono_bank_api_url.'/api/v2/client/validate', [
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

    public function rejectOrderMonoBank(Order $order): ServiceActionResult
    {
        $client = new Client;

        $request_array = [
            'order_id' => $order->mono_order_id,
        ];
        /*$request_array = [
            "order_id" => "123e4567-e89b-12d3-a456-426614174000"
        ];*/

        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            $response = $client->post($this->mono_bank_api_url.'/api/order/reject', [
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

                if (isset($data['order_sub_state']) && $data['order_sub_state'] == 'REJECTED_BY_STORE') {
                    $order->mono_order_state = MonoBankOrderStateStatusesDataClass::STATUS_REJECTED;
                    $order->save();

                    return ServiceActionResult::make(true, trans('admin.order_rejected'));
                } else {

                    if (isset($data['message'])) {
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

    public function confirmOrderMonoBank(Order $order): ServiceActionResult
    {
        $client = new Client;

        $request_array = [
            'order_id' => $order->mono_order_id,
        ];
        /*$request_array = [
            "order_id" => "123e4567-e89b-12d3-a456-426614174000"
        ];*/

        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            $response = $client->post($this->mono_bank_api_url.'/api/order/confirm', [
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

                if (isset($data['state']) && $data['state'] == 'SUCCESS') {
                    $order->mono_order_state = MonoBankOrderStateStatusesDataClass::STATUS_CONFIRMED;
                    $order->save();

                    return ServiceActionResult::make(true, trans('admin.order_confirmed_by_store'));
                } else {

                    if (isset($data['message'])) {
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

    public function returnOrderMonoBank(Order $order): ServiceActionResult
    {
        if (! $this->isConfigured()) {
            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));
        }

        $client = new Client;

        $amount = $this->pricingService->forOrder($order)['total'];

        $request_array = [
            'order_id' => $order->mono_order_id,
            'return_money_to_card' => true,
            'store_return_id' => $order->id,
            'sum' => round($amount, 2),
        ];

        $signature = $this->makeMonoBankPartialPaymentSignature($request_array, $this->mono_bank_client_secret);

        try {
            $response = $client->post($this->mono_bank_api_url.'/api/order/return', [
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

                if (isset($data['status']) && $data['status'] == 'OK') {
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

        return base64_encode(hash_hmac('sha256', $request_string, $mono_bank_client_secret, true));
    }

    private function collectAllProductsFromOrder(Order $order): array
    {
        $summary = $this->pricingService->forOrder($order);
        $data['products'] = collect(InstallmentPaymentLines::forOrder($order, $summary))
            ->map(fn (array $line) => [
                'name' => $line['name'],
                'count' => $line['count'],
                'sum' => (float) ($line['unit_in_cents'] / 100),
            ])
            ->all();

        $data['amount'] = round($summary['total'], 2);

        return $data;
    }
}
