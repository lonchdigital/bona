<?php

namespace Tests\Feature;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Jobs\SendTelegramNotification;
use App\Models\Order;
use App\Services\Payment\PaymentReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('domain.admin_notification_emails', '');
        config()->set('telegram.enabled', false);
        config()->set('payment.http.attempts', 1);
        config()->set('payment.reconciliation.lookback_hours', 72);
        config()->set('payment.reconciliation.batch_size', 50);
        $this->seedCurrency();
    }

    public function test_liqpay_shop_blocked_becomes_a_visible_declined_payment(): void
    {
        Queue::fake();
        config()->set('telegram.enabled', true);
        config()->set('telegram.bot_token', 'telegram-test-token');
        config()->set('telegram.chat_id', '-1001234567890');
        config()->set('liqpay.public_key', 'sandbox_public');
        config()->set('liqpay.private_key', 'sandbox_private');
        $order = $this->order(PaymentTypesDataClass::CARD_PAYMENT, OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS);

        Http::fake(['https://www.liqpay.ua/*' => Http::response([
            'status' => 'error',
            'err_code' => 'shop_blocked',
            'err_description' => 'Магазин заблоковано',
        ], 200)]);

        $report = app(PaymentReconciliationService::class)->reconcile();

        $this->assertSame(['checked' => 1, 'updated' => 1, 'failed' => 0], $report);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_DECLINED, (int) $order->fresh()->payment_status_id);
        Queue::assertPushed(SendTelegramNotification::class, fn (SendTelegramNotification $job) => str_contains(
            $job->message,
            'shop_blocked: Магазин заблоковано',
        ));
    }

    public function test_liqpay_payment_not_found_is_not_misreported_as_a_bank_decline(): void
    {
        config()->set('liqpay.public_key', 'sandbox_public');
        config()->set('liqpay.private_key', 'sandbox_private');
        $order = $this->order(PaymentTypesDataClass::CARD_PAYMENT, OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS);

        Http::fake(['https://www.liqpay.ua/*' => Http::response([
            'status' => 'error',
            'err_code' => 'payment_not_found',
        ], 200)]);

        $report = app(PaymentReconciliationService::class)->reconcile();

        $this->assertSame(['checked' => 1, 'updated' => 0, 'failed' => 0], $report);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS, (int) $order->fresh()->payment_status_id);
    }

    public function test_liqpay_status_cannot_mark_a_different_amount_as_paid(): void
    {
        config()->set('liqpay.public_key', 'sandbox_public');
        config()->set('liqpay.private_key', 'sandbox_private');
        $order = $this->order(PaymentTypesDataClass::CARD_PAYMENT, OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS);

        Http::fake(['https://www.liqpay.ua/*' => Http::response([
            'public_key' => 'sandbox_public',
            'order_id' => (string) $order->id,
            'status' => 'success',
            'amount' => 1,
            'currency' => 'UAH',
        ], 200)]);

        $report = app(PaymentReconciliationService::class)->reconcile();

        $this->assertSame(['checked' => 1, 'updated' => 0, 'failed' => 1], $report);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS, (int) $order->fresh()->payment_status_id);
    }

    public function test_liqpay_polling_recovers_a_valid_missed_success_callback(): void
    {
        config()->set('liqpay.public_key', 'sandbox_public');
        config()->set('liqpay.private_key', 'sandbox_private');
        $order = $this->order(PaymentTypesDataClass::CARD_PAYMENT, OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS);

        Http::fake(['https://www.liqpay.ua/*' => Http::response([
            'public_key' => 'sandbox_public',
            'order_id' => (string) $order->id,
            'status' => 'success',
            'amount' => 10_000,
            'currency' => 'UAH',
            'payment_id' => 123456789,
        ], 200)]);

        $report = app(PaymentReconciliationService::class)->reconcile();

        $this->assertSame(['checked' => 1, 'updated' => 1, 'failed' => 0], $report);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAID, (int) $order->fresh()->payment_status_id);
    }

    public function test_monobank_polling_is_signed_and_recovers_a_missed_success_callback(): void
    {
        config()->set('payment.monobank', [
            'api_url' => 'https://mono.test',
            'client_secret' => 'mono-secret',
            'store_id' => 'mono-store',
            'point_id' => 'point-1',
            'periods' => [3],
            'installment_surcharges' => [3 => 2.9],
        ]);
        $order = $this->order(
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
            OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS,
            ['mono_order_id' => 'mono-order-123'],
        );

        Http::fake(function (Request $request) {
            $this->assertSame('https://mono.test/api/order/state', $request->url());
            $this->assertSame('mono-store', $request->header('store-id')[0] ?? null);
            $this->assertSame(
                base64_encode(hash_hmac('sha256', $request->body(), 'mono-secret', true)),
                $request->header('signature')[0] ?? null,
            );

            return Http::response([
                'state' => 'IN_PROCESS',
                'order_sub_state' => 'WAITING_FOR_STORE_CONFIRM',
            ], 200, ['Trace-Id' => 'mono-trace']);
        });

        $report = app(PaymentReconciliationService::class)->reconcile();

        $this->assertSame(['checked' => 1, 'updated' => 1, 'failed' => 0], $report);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAID, (int) $order->fresh()->payment_status_id);
    }

    public function test_privatbank_polling_authenticates_the_response_before_recovering_status(): void
    {
        config()->set('payment.privatbank.store_id', 'privat-store');
        config()->set('payment.privatbank.password', 'privat-secret');
        $order = $this->order(
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
            OrderPaymentStatusesDataClass::STATUS_PAYPART,
        );

        Http::fake(function (Request $request) use ($order) {
            $payload = [
                'state' => 'SUCCESS',
                'storeId' => 'privat-store',
                'orderId' => (string) $order->id,
                'paymentState' => 'SUCCESS',
                'message' => 'OK',
            ];
            $payload['signature'] = base64_encode(sha1(
                'privat-secret'
                .$payload['state']
                .$payload['storeId']
                .$payload['orderId']
                .$payload['paymentState']
                .$payload['message']
                .'privat-secret',
                true,
            ));

            return Http::response($payload, 200, ['Trace-Id' => 'privat-trace']);
        });

        $report = app(PaymentReconciliationService::class)->reconcile();

        $this->assertSame(['checked' => 1, 'updated' => 1, 'failed' => 0], $report);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAID, (int) $order->fresh()->payment_status_id);
    }

    public function test_an_unavailable_gateway_is_reported_but_does_not_change_the_order(): void
    {
        config()->set('liqpay.public_key', 'sandbox_public');
        config()->set('liqpay.private_key', 'sandbox_private');
        $order = $this->order(PaymentTypesDataClass::CARD_PAYMENT, OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS);

        Http::fake(['https://www.liqpay.ua/*' => Http::response(['message' => 'unavailable'], 503)]);

        $report = app(PaymentReconciliationService::class)->reconcile();

        $this->assertSame(['checked' => 1, 'updated' => 0, 'failed' => 1], $report);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS, (int) $order->fresh()->payment_status_id);
    }

    public function test_payment_diagnostics_fail_closed_without_credentials_and_pass_when_complete(): void
    {
        config()->set('liqpay.public_key', '');
        config()->set('liqpay.private_key', '');
        config()->set('payment.privatbank.store_id', '');
        config()->set('payment.privatbank.password', '');
        config()->set('payment.monobank.client_secret', '');

        $this->artisan('payments:diagnose --strict')->assertFailed();

        config()->set('liqpay.public_key', 'live-public');
        config()->set('liqpay.private_key', 'live-private');
        config()->set('payment.privatbank.store_id', 'privat-store');
        config()->set('payment.privatbank.password', 'privat-secret');
        config()->set('payment.monobank', [
            'api_url' => 'https://u2.monobank.com.ua',
            'client_secret' => 'mono-secret',
            'store_id' => 'mono-store',
            'point_id' => 'point-1',
            'periods' => [3],
            'installment_surcharges' => [3 => 2.9],
        ]);

        $this->artisan('payments:diagnose --strict')->assertSuccessful();
    }

    private function order(int $paymentType, int $paymentStatus, array $attributes = []): Order
    {
        $product = $this->makeProduct(['price' => 10_000]);
        $order = Order::create(array_merge([
            'status_id' => OrderStatusesDataClass::STATUS_NEW,
            'user_id' => $this->author()->id,
            'delivery_type_id' => DeliveryTypesDataClass::PICK_UP_DELIVERY,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'payment_type_id' => $paymentType,
            'payment_status_id' => $paymentStatus,
        ], $attributes));
        $order->products()->attach($product->id, [
            'count' => 1,
            'price' => 10_000,
            'attributes_price' => 0,
        ]);

        return $order;
    }
}
