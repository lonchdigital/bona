<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Services\Base\BaseService;
use App\Services\Order\OrderAccessUrlService;
use App\Services\Payment\DTO\PaymentGatewayResult;
use App\Services\Pricing\PricingService;
use App\Support\Payment\InstallmentPaymentLines;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

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

    public function createPrivateBankPartialPaymentOrder(
        Order $order,
        int $paymentPeriod,
        string $merchantType,
    ): PaymentGatewayResult {
        $data = $this->createPrivateBankPartialPaymentPayload($order, $paymentPeriod, $merchantType);

        if ($data === null) {
            return PaymentGatewayResult::failure('PrivatBank instalments are not configured.');
        }

        try {
            $response = $this->paymentRequest()
                ->withBody(
                    json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'application/json',
                )
                ->post('https://payparts2.privatbank.ua/ipp/v2/payment/create');

            $payload = $response->json();
            $traceId = $response->header('Trace-Id') ?: $response->header('X-Request-Id');

            if (! $response->successful() || ! is_array($payload)) {
                $this->logGatewayFailure('PrivatBank', $response->status(), $traceId, $payload);

                return PaymentGatewayResult::failure(
                    is_array($payload) ? ($payload['message'] ?? $payload['errorMessage'] ?? null) : null,
                    $response->status(),
                    $traceId,
                    is_array($payload) ? $payload : [],
                );
            }

            if (($payload['state'] ?? null) !== 'SUCCESS' || blank($payload['token'] ?? null)) {
                $this->logGatewayFailure('PrivatBank', $response->status(), $traceId, $payload);

                return PaymentGatewayResult::failure(
                    $payload['message'] ?? $payload['errorMessage'] ?? null,
                    $response->status(),
                    $traceId,
                    $payload,
                );
            }

            return PaymentGatewayResult::success($payload, $response->status(), $traceId);
        } catch (Throwable $exception) {
            Log::error('PrivatBank order creation failed.', [
                'order_id' => $order->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return PaymentGatewayResult::failure($exception->getMessage());
        }
    }

    public function createPrivateBankPartialPaymentPayload(Order $order, int $paymentPeriod, string $merchantType): ?array
    {
        $redirect_url = $this->orderAccessUrlService->thankYou($order);
        $response_url = route('store.checkout.partial.payment');
        $store_password = (string) config('payment.privatbank.password');
        $store_id = (string) config('payment.privatbank.store_id');
        if ($store_password === '' || $store_id === '') {
            Log::error('PrivatBank instalments are not configured.');

            return null;
        }

        $summary = $this->pricingService->forOrder($order);
        $amount = round($summary['total'], 2);
        $products = $this->collectPrivatBankProducts($order, $summary);
        $signature = $this->makePartialPaymentSignature(
            $order,
            $amount,
            $paymentPeriod,
            $merchantType,
            $response_url,
            $redirect_url,
            $store_password,
            $store_id,
            $products,
        );

        $data = [
            'storeId' => $store_id,
            'orderId' => $order->id,
            'amount' => number_format($amount, 2, '.', ''),
            'partsCount' => $paymentPeriod,
            'merchantType' => $merchantType,
            'products' => $products,
            'responseUrl' => $response_url,
            'redirectUrl' => $redirect_url,
            'signature' => $signature,
        ];

        return $data;
    }

    private function makePartialPaymentSignature(
        Order $order,
        float $amount,
        int $payment_period,
        string $merchant_type,
        string $response_url,
        string $redirect_url,
        string $store_password,
        string $store_id,
        array $products,
    ): string {

        $product_str = '';
        foreach ($products as $product) {
            $product_str .= $product['name'].$product['count'].$this->withoutFloating((float) $product['price']);
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

    private function collectPrivatBankProducts(Order $order, array $summary): array
    {
        return collect(InstallmentPaymentLines::forOrder($order, $summary))
            ->map(fn (array $line) => [
                'name' => $line['name'],
                'count' => $line['count'],
                'price' => number_format($line['unit_in_cents'] / 100, 2, '.', ''),
            ])
            ->all();
    }

    /*private function withoutFloating(float $number): string
    {
        return (string)round($number, 2, PHP_ROUND_HALF_DOWN) * 100;
    }*/
    private function withoutFloating(float $number): string
    {
        return number_format(round($number * 100, 0, PHP_ROUND_HALF_DOWN), 0, '', '');
    }

    private function paymentRequest()
    {
        return Http::acceptJson()
            ->connectTimeout((float) config('payment.http.connect_timeout', 5))
            ->timeout((float) config('payment.http.timeout', 15))
            ->retry(
                max(1, (int) config('payment.http.attempts', 2)),
                max(0, (int) config('payment.http.retry_delay_ms', 200)),
                fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError()),
                throw: false,
            );
    }

    private function logGatewayFailure(string $gateway, int $statusCode, ?string $traceId, mixed $payload): void
    {
        Log::error($gateway.' order creation was refused.', [
            'status_code' => $statusCode,
            'trace_id' => $traceId,
            'response' => is_array($payload) ? $payload : null,
        ]);
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
