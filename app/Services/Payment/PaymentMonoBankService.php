<?php

namespace App\Services\Payment;

use App\DataClasses\MonoBankOrderStateStatusesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\Models\Order;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Payment\DTO\PaymentGatewayResult;
use App\Services\Pricing\PricingService;
use App\Support\Payment\InstallmentPaymentLines;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

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

    public function createMonoBankPartialPaymentOrder(Order $order, string $phone, string $period): PaymentGatewayResult
    {
        if (! $this->isConfigured()) {
            Log::error('Monobank instalments are not configured.');

            return PaymentGatewayResult::failure('Monobank instalments are not configured.');
        }

        $requestArray = $this->createOrderPayload($order, $phone, $period);

        $result = $this->sendSignedRequest('/api/order/create', $requestArray, 'create order');
        $orderId = $result->data['order_id'] ?? null;

        if (! $result->successful || ! is_string($orderId) || trim($orderId) === '') {
            if ($result->successful) {
                Log::error('Monobank returned a successful response without an order id.', [
                    'order_id' => $order->id,
                    'trace_id' => $result->traceId,
                ]);

                return PaymentGatewayResult::failure(
                    'Monobank response does not contain an order id.',
                    $result->statusCode,
                    $result->traceId,
                    $result->data,
                );
            }

            return $result;
        }

        $order->update([
            'mono_order_id' => $orderId,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS,
        ]);

        return $result;
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

    public function validateClientMonoBankPhone(string $phone): PaymentGatewayResult
    {
        if (! $this->isConfigured()) {
            return PaymentGatewayResult::failure('Monobank instalments are not configured.');
        }

        $requestArray = [
            'phone' => $phone,
        ];

        $result = $this->sendSignedRequest('/api/v2/client/validate', $requestArray, 'validate client');

        if ($result->successful && ! is_bool($result->data['found'] ?? null)) {
            Log::error('Monobank client validation response is malformed.', [
                'trace_id' => $result->traceId,
            ]);

            return PaymentGatewayResult::failure(
                'Monobank response does not contain client eligibility.',
                $result->statusCode,
                $result->traceId,
                $result->data,
            );
        }

        return $result;
    }

    public function rejectOrderMonoBank(Order $order): ServiceActionResult
    {
        if (! $this->isConfigured() || blank($order->mono_order_id)) {
            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));
        }

        $requestArray = [
            'order_id' => $order->mono_order_id,
        ];

        $result = $this->sendSignedRequest('/api/order/reject', $requestArray, 'reject order');
        if ($result->successful && ($result->data['order_sub_state'] ?? null) === 'REJECTED_BY_STORE') {
            $order->update(['mono_order_state' => MonoBankOrderStateStatusesDataClass::STATUS_REJECTED]);

            return ServiceActionResult::make(true, trans('admin.order_rejected'));
        }

        return ServiceActionResult::make(false, $result->message ?: trans('admin.something_went_wrong'));
    }

    public function confirmOrderMonoBank(Order $order): ServiceActionResult
    {
        if (! $this->isConfigured() || blank($order->mono_order_id)) {
            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));
        }

        $requestArray = [
            'order_id' => $order->mono_order_id,
        ];

        $result = $this->sendSignedRequest('/api/order/confirm', $requestArray, 'confirm order');
        if ($result->successful && ($result->data['state'] ?? null) === 'SUCCESS') {
            $order->update(['mono_order_state' => MonoBankOrderStateStatusesDataClass::STATUS_CONFIRMED]);

            return ServiceActionResult::make(true, trans('admin.order_confirmed_by_store'));
        }

        return ServiceActionResult::make(false, $result->message ?: trans('admin.something_went_wrong'));
    }

    public function returnOrderMonoBank(Order $order): ServiceActionResult
    {
        if (! $this->isConfigured()) {
            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));
        }

        $amount = $this->pricingService->forOrder($order)['total'];

        if (blank($order->mono_order_id)) {
            return ServiceActionResult::make(false, trans('admin.something_went_wrong'));
        }

        $requestArray = [
            'order_id' => $order->mono_order_id,
            'return_money_to_card' => true,
            'store_return_id' => $order->id,
            'sum' => round($amount, 2),
        ];

        $result = $this->sendSignedRequest('/api/order/return', $requestArray, 'return order');
        if ($result->successful && ($result->data['status'] ?? null) === 'OK') {
            $order->update(['mono_order_state' => MonoBankOrderStateStatusesDataClass::STATUS_RETURNED]);

            return ServiceActionResult::make(true, trans('admin.order_returned'));
        }

        return ServiceActionResult::make(false, $result->message ?: trans('admin.something_went_wrong'));
    }

    private function makeMonoBankPartialPaymentSignature(string $requestBody, string $monoBankClientSecret): string
    {
        return base64_encode(hash_hmac('sha256', $requestBody, $monoBankClientSecret, true));
    }

    private function sendSignedRequest(string $path, array $payload, string $operation): PaymentGatewayResult
    {
        try {
            $body = json_encode(
                $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            );
            $signature = $this->makeMonoBankPartialPaymentSignature($body, $this->mono_bank_client_secret);

            $response = Http::acceptJson()
                ->connectTimeout((float) config('payment.http.connect_timeout', 5))
                ->timeout((float) config('payment.http.timeout', 15))
                ->retry(
                    max(1, (int) config('payment.http.attempts', 2)),
                    max(0, (int) config('payment.http.retry_delay_ms', 200)),
                    fn (Throwable $exception): bool => $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && $exception->response->serverError()),
                    throw: false,
                )
                ->withHeaders([
                    'store-id' => $this->mono_bank_client_store_id,
                    'signature' => $signature,
                ])
                ->withBody($body, 'application/json')
                ->post(rtrim($this->mono_bank_api_url, '/').$path);

            $data = $response->json();
            $traceId = $response->header('Trace-Id') ?: $response->header('X-Request-Id');

            if (! $response->successful() || ! is_array($data)) {
                Log::error('Monobank API request failed.', [
                    'operation' => $operation,
                    'status_code' => $response->status(),
                    'trace_id' => $traceId,
                    'response' => is_array($data) ? $data : null,
                ]);

                return PaymentGatewayResult::failure(
                    is_array($data) ? ($data['message'] ?? null) : null,
                    $response->status(),
                    $traceId,
                    is_array($data) ? $data : [],
                );
            }

            return PaymentGatewayResult::success($data, $response->status(), $traceId);
        } catch (Throwable $exception) {
            Log::error('Monobank API request raised an exception.', [
                'operation' => $operation,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return PaymentGatewayResult::failure($exception->getMessage());
        }
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
