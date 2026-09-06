<?php

namespace Tests\Feature;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Models\Cart;
use App\Models\Order;
use App\Services\Order\OrderAccessUrlService;
use App\Services\Payment\PaymentMonoBankService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class PaymentGatewayFlowTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        config()->set('domain.admin_notification_emails', '');
        config()->set('payment.http.attempts', 1);
        config()->set('payment.monobank', [
            'api_url' => 'https://mono.test',
            'client_secret' => 'mono-secret',
            'store_id' => 'mono-store',
            'point_id' => 'point-1',
            'minimum_period' => 3,
            'periods' => [3, 4],
            'installment_surcharges' => [3 => 2.9, 4 => 4.1],
        ]);
    }

    public function test_monobank_requests_are_signed_over_the_exact_body_and_a_valid_order_is_recorded(): void
    {
        $this->seedCurrency();
        $order = $this->order(PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK, 'monobank', 3, 2.9);

        Http::fake(function (Request $request) {
            $this->assertSame('mono-store', $request->header('store-id')[0] ?? null);
            $this->assertSame(
                base64_encode(hash_hmac('sha256', $request->body(), 'mono-secret', true)),
                $request->header('signature')[0] ?? null,
            );

            if (str_ends_with($request->url(), '/api/v2/client/validate')) {
                return Http::response(['found' => true], 200, ['Trace-Id' => 'trace-validation']);
            }

            return Http::response(['order_id' => 'mono-order-123'], 201, ['Trace-Id' => 'trace-create']);
        });

        $service = app(PaymentMonoBankService::class);
        $validation = $service->validateClientMonoBankPhone('+380501234567');
        $creation = $service->createMonoBankPartialPaymentOrder($order, '+380501234567', '3');

        $this->assertTrue($validation->successful);
        $this->assertTrue($validation->data['found']);
        $this->assertTrue($creation->successful);
        $this->assertSame('trace-create', $creation->traceId);
        $this->assertSame('mono-order-123', $order->fresh()->mono_order_id);
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS, (int) $order->fresh()->payment_status_id);
        Http::assertSentCount(2);
    }

    public function test_a_monobank_validation_outage_does_not_consume_the_cart_or_create_an_order(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();
        Http::fake(['mono.test/*' => Http::response(['message' => 'temporarily unavailable'], 503)]);

        $this->addToCart($product->slug)->assertOk();
        $response = $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
            'mono_payment_period' => 3,
        ]);

        $response->assertSessionHasErrors('payment_type_id');
        $this->assertSame(0, Order::count());
        $this->assertSame(1, Cart::count());
    }

    public function test_a_failed_monobank_application_is_saved_as_declined_and_shows_a_clear_failure_page(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();
        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/api/v2/client/validate')) {
                return Http::response(['found' => true], 200);
            }

            return Http::response(['message' => 'application refused'], 422, ['Trace-Id' => 'mono-refusal']);
        });

        $this->addToCart($product->slug)->assertOk();
        $response = $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
            'mono_payment_period' => 3,
            'email' => 'mono-failure@example.com',
        ]);

        $order = Order::firstOrFail();
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_DECLINED, (int) $order->payment_status_id);
        $this->assertSame(0, Cart::count());
        $response->assertRedirect();

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertViewIs('pages.store.payment-failure')
            ->assertSee('#BD-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_a_failed_privatbank_application_is_saved_as_declined_and_never_looks_successful(): void
    {
        $this->seedCurrency();
        config()->set('payment.privatbank.store_id', 'privat-store');
        config()->set('payment.privatbank.password', 'privat-secret');
        $product = $this->makeProduct();
        Http::fake([
            'payparts2.privatbank.ua/*' => Http::response([
                'state' => 'FAIL',
                'message' => 'application refused',
            ], 200),
        ]);

        $this->addToCart($product->slug)->assertOk();
        $response = $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
            'payment_period' => 2,
            'email' => 'privat-failure@example.com',
        ]);

        $order = Order::firstOrFail();
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_DECLINED, (int) $order->payment_status_id);
        $response->assertOk()->assertJsonPath('data.success', true);
        $failureUrl = $response->json('data.redirect_to');
        $this->assertNotEmpty($failureUrl);

        $this->get($failureUrl)
            ->assertOk()
            ->assertViewIs('pages.store.payment-failure');
    }

    public function test_a_valid_privatbank_application_redirects_to_the_bank_and_keeps_the_order_pending(): void
    {
        $this->seedCurrency();
        config()->set('payment.privatbank.store_id', 'privat-store');
        config()->set('payment.privatbank.password', 'privat-secret');
        $product = $this->makeProduct();
        Http::fake([
            'payparts2.privatbank.ua/*' => Http::response([
                'state' => 'SUCCESS',
                'token' => 'privat-token-123',
            ], 200, ['Trace-Id' => 'privat-trace']),
        ]);

        $this->addToCart($product->slug)->assertOk();
        $response = $this->confirm([
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART,
            'payment_period' => 2,
            'email' => 'privat-success@example.com',
        ]);

        $order = Order::firstOrFail();
        $this->assertSame(OrderPaymentStatusesDataClass::STATUS_PAYPART, (int) $order->payment_status_id);
        $response->assertOk()
            ->assertJsonPath('data.success', true)
            ->assertJsonPath(
                'data.redirect_to',
                'https://payparts2.privatbank.ua/ipp/v2/payment?token=privat-token-123',
            );
    }

    public function test_monobank_retries_a_server_failure_once_and_then_accepts_a_valid_response(): void
    {
        config()->set('payment.http.attempts', 2);
        config()->set('payment.http.retry_delay_ms', 0);
        Http::fakeSequence('mono.test/*')
            ->push(['message' => 'temporary failure'], 503)
            ->push(['found' => true], 200);

        $result = app(PaymentMonoBankService::class)
            ->validateClientMonoBankPhone('+380501234567');

        $this->assertTrue($result->successful);
        $this->assertTrue($result->data['found']);
        Http::assertSentCount(2);
    }

    public function test_monobank_does_not_retry_a_client_rejection(): void
    {
        config()->set('payment.http.attempts', 2);
        config()->set('payment.http.retry_delay_ms', 0);
        Http::fake(['mono.test/*' => Http::response(['message' => 'not eligible'], 422)]);

        $result = app(PaymentMonoBankService::class)
            ->validateClientMonoBankPhone('+380501234567');

        $this->assertFalse($result->successful);
        $this->assertSame(422, $result->statusCode);
        Http::assertSentCount(1);
    }

    public function test_installment_rate_is_kept_out_of_customer_confirmation_views(): void
    {
        $this->seedCurrency();
        $order = $this->order(PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK, 'monobank', 3, 2.9);
        $order->update(['payment_status_id' => OrderPaymentStatusesDataClass::STATUS_PAID]);

        $response = $this->get(app(OrderAccessUrlService::class)->monoBankThankYou($order));

        $response->assertOk()
            ->assertSee(trans('base.checkout_payment_period_label'))
            ->assertDontSee('2,9%')
            ->assertDontSee(trans('base.installment_surcharge'));

        $emailTemplate = file_get_contents(resource_path('views/emails/success-order.blade.php'));
        $this->assertStringNotContainsString('installment_surcharge', $emailTemplate);
        $this->assertStringNotContainsString('installment_rate', $emailTemplate);
    }

    private function addToCart(string $slug)
    {
        return $this->keepCookies($this->postJson(
            route('store.cart.add-product', ['productSlug' => $slug]),
            ['product_count' => 1],
        ));
    }

    private function confirm(array $overrides = [])
    {
        return $this->keepCookies($this->post(route('store.checkout.confirm'), array_merge([
            'delivery_type_id' => DeliveryTypesDataClass::PICK_UP_DELIVERY,
            'payment_type_id' => PaymentTypesDataClass::CASH_PAYMENT,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'agreement' => 1,
            'first_name' => 'Олена',
            'last_name' => 'Коваль',
            'phone' => '+38(067)111-22-33',
            'email' => 'gateway-flow@example.com',
        ], $overrides)));
    }

    private function order(int $paymentType, string $provider, int $period, float $rate): Order
    {
        $product = $this->makeProduct(['price' => 10_000]);
        $order = Order::create([
            'status_id' => OrderStatusesDataClass::STATUS_NEW,
            'user_id' => $this->author()->id,
            'delivery_type_id' => DeliveryTypesDataClass::PICK_UP_DELIVERY,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'payment_type_id' => $paymentType,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_UNPAID,
            'installment_provider' => $provider,
            'installment_period' => $period,
            'installment_surcharge_percent' => $rate,
            'installment_surcharge_amount' => 290,
        ]);
        $order->products()->attach($product->id, [
            'count' => 1,
            'price' => 10_000,
            'attributes_price' => 0,
        ]);

        return $order;
    }
}
