<?php

namespace Tests\Feature;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Jobs\ReportSerpAgentConversion;
use App\Jobs\SendTelegramNotification;
use App\Jobs\VerifySerpAgentConversion;
use App\Models\Order;
use App\Models\User;
use App\Services\Base\ServiceActionResult;
use App\Services\EmailService\OrderCountDoorsService;
use App\Services\EmailService\UserChooseDoorsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class SerpAgentConversionTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake([SendTelegramNotification::class]);
        Http::preventStrayRequests();
        config()->set([
            'serp-agent.secret' => 'test-secret',
            'serp-agent.conversions.enabled' => true,
            'serp-agent.conversions.project_id' => 'cmt2pz3ou0004b0i29eg5zca1',
            'domain.admin_notification_emails' => '',
            'telegram.enabled' => true,
            'telegram.bot_token' => 'test-token',
            'telegram.chat_id' => '-1234',
        ]);
        $this->fakeVerification(Http::response(['ok' => true, 'verified' => true]));
    }

    private function fakeVerification(mixed $callback): void
    {
        // Replacing the factory avoids accumulating stubs for the same URL.
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake([VerifySerpAgentConversion::ENDPOINT => $callback]);
    }

    private function lead(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Оксана Коваль',
            'phone' => '+38 (067) 111 22 33',
            'description' => 'Приватний коментар клієнта',
            'agree' => 1,
            'sa_event' => 'event-1234',
            'source_url' => 'https://bona-doors.com.ua/product/test-door',
        ], $overrides);
    }

    private function assertVerification(string $formId, ?string $event = 'event-1234', ?string $page = 'https://bona-doors.com.ua/product/test-door'): void
    {
        Http::assertSentCount(1);
        Http::assertSent(function ($request) use ($formId, $event, $page) {
            $this->assertSame(VerifySerpAgentConversion::ENDPOINT, $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertTrue($request->hasHeader('Content-Type', 'application/json'));
            $this->assertFalse($request->hasHeader('Authorization'));
            $this->assertSame([
                'projectId' => 'cmt2pz3ou0004b0i29eg5zca1',
                'eventKey' => $event,
                'formPlugin' => 'custom',
                'formId' => $formId,
                'pageUrl' => $page,
            ], json_decode($request->body(), true, 512, JSON_THROW_ON_ERROR));
            $timestamp = $request->header('X-SerpAgent-Timestamp')[0];
            $this->assertMatchesRegularExpression('/^[0-9]{10}$/', $timestamp);
            $this->assertLessThanOrEqual(5, abs(now()->timestamp - (int) $timestamp));
            $signature = rtrim(strtr(base64_encode(hash_hmac('sha256', $timestamp.'.'.$request->body(), 'test-secret', true)), '+/', '-_'), '=');
            $this->assertSame([$signature], $request->header('X-SerpAgent-Signature'));
            foreach (['Оксана', 'Коваль', '067', 'Приватний', 'email', 'description', 'phone', 'comment', 'externalId', 'test-secret'] as $private) {
                $this->assertStringNotContainsString($private, $request->body());
            }

            return true;
        });
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/v1/') || str_ends_with($request->url(), '/conversions'));
    }

    public function test_signature_matches_supplied_vector_without_sending_it(): void
    {
        $body = '{"projectId":"PROJECT","eventKey":null,"formPlugin":"custom","formId":"quote-form","pageUrl":null}';
        $this->assertSame('mVcfEnFwMH78blIYm2FNHRGjSzTVjkM5-3DJIvYASRY', VerifySerpAgentConversion::signature('test-secret', '1700000000', $body));
        Http::assertNothingSent();
    }

    public static function leadForms(): array
    {
        $cases = [];
        foreach (['', '/ru'] as $locale) {
            foreach (['measure', 'selection', 'consultation', 'showroom-visit'] as $form) {
                $cases[($locale ?: 'uk').'-'.$form] = [$locale, $form];
            }
        }

        return $cases;
    }

    #[DataProvider('leadForms')]
    public function test_accepted_lead_is_verified_once_in_both_languages(string $locale, string $form): void
    {
        $this->postJson($locale.'/user-choose-doors', $this->lead(['form_id' => $form]))
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('visit_requests', 1);
        Queue::assertPushed(SendTelegramNotification::class);
        $this->assertVerification($form);
    }

    #[DataProvider('locales')]
    public function test_order_request_is_saved_notified_and_verified(string $locale): void
    {
        $this->postJson($locale.'/order-count-doors', $this->lead([
            'form_id' => 'order-request',
            'current_product_title' => 'Тестові двері',
            'current_product_url' => 'https://bona-doors.com.ua/product/test-door',
        ]))->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('visit_requests', 1);
        Queue::assertPushed(SendTelegramNotification::class);
        $this->assertVerification('order-request');
    }

    public static function locales(): array
    {
        return ['uk' => [''], 'ru' => ['/ru']];
    }

    #[DataProvider('locales')]
    public function test_one_click_uses_verify_once_and_never_the_old_conversions_api(string $locale): void
    {
        $product = $this->makeProduct();
        $this->postJson($locale.'/product/'.$product->slug.'/one-click', $this->lead())
            ->assertOk()->assertJsonPath('data.success', true);
        $this->assertDatabaseCount('orders', 1);
        $this->assertVerification('one-click');
    }

    #[DataProvider('locales')]
    public function test_checkout_verifies_the_persisted_order_once(string $locale): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct(['price' => 4200]);
        $this->keepCookies($this->postJson(route('store.cart.add-product', ['productSlug' => $product->slug]), ['product_count' => 1]))->assertOk();
        $this->fakeVerification(function () {
            $this->assertSame(1, Order::count(), 'Verification must follow order persistence.');

            return Http::response(['ok' => true, 'verified' => true]);
        });
        $this->post($locale.'/checkout/confirm', $this->lead([
            'delivery_type_id' => DeliveryTypesDataClass::PICK_UP_DELIVERY,
            'payment_type_id' => PaymentTypesDataClass::CASH_PAYMENT,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'agreement' => 1,
            'first_name' => 'Оксана',
            'last_name' => 'Коваль',
            'email' => 'private@example.com',
            'phone' => '+38(067)111-22-33',
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('orders', 1);
        $this->assertVerification('checkout');
    }

    public static function metadata(): array
    {
        return [
            'no browser tracker' => [null, null, null, null],
            'referer fallback' => ['abc', null, 'https://bona-doors.com.ua/ru/contacts', 'https://bona-doors.com.ua/ru/contacts'],
            'external source' => ['abc', 'https://evil.example/a', 'https://bona-doors.com.ua/contacts', null],
            'host suffix spoof' => ['abc', 'https://bona-doors.com.ua.evil.example/a', null, null],
            'userinfo spoof' => ['abc', 'https://user:password@bona-doors.com.ua/a', null, null],
            'query PII and fragment stripped' => ['abc', 'https://bona-doors.com.ua/contacts?phone=0671112233&token=secret#name', null, 'https://bona-doors.com.ua/contacts'],
            'invalid event array' => [['phone' => '0671112233'], null, null, null],
            'PII is not an event ID' => ['private@example.com', null, null, null],
        ];
    }

    #[DataProvider('metadata')]
    public function test_optional_attribution_cannot_leak_form_values_or_break_a_lead(mixed $event, ?string $source, ?string $referer, ?string $expected): void
    {
        if ($referer) {
            $this->withHeader('Referer', $referer);
        }
        $this->postJson('/user-choose-doors', $this->lead(['sa_event' => $event, 'source_url' => $source, 'form_id' => 'private@example.com']))->assertOk();
        $this->assertVerification('consultation', is_string($event) && $event !== 'private@example.com' ? $event : null, $expected);
    }

    public function test_invalid_forms_do_not_report(): void
    {
        $this->postJson('/user-choose-doors', $this->lead(['name' => '']))->assertUnprocessable();
        $this->postJson('/order-count-doors', $this->lead(['name' => '']))->assertUnprocessable();
        $product = $this->makeProduct();
        $this->postJson('/product/'.$product->slug.'/one-click', $this->lead(['phone' => '']))->assertUnprocessable();
        $this->postJson('/checkout/confirm', [])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_failed_lead_acceptance_does_not_report(): void
    {
        $this->mock(UserChooseDoorsService::class)->shouldReceive('userChooseDoors')->once()->andReturn(ServiceActionResult::make(false, 'Unable to save'));
        $this->mock(OrderCountDoorsService::class)->shouldReceive('orderCountDoors')->once()->andReturn(ServiceActionResult::make(false, 'Unable to save'));
        $this->postJson('/user-choose-doors', $this->lead())->assertStatus(500)->assertJsonPath('success', false);
        $this->postJson('/order-count-doors', $this->lead())->assertStatus(500)->assertJsonPath('success', false);
        Http::assertNothingSent();
    }

    public static function remoteFailures(): array
    {
        return ['timeout' => ['timeout', 3], 'server error' => [500, 3], 'forbidden' => [403, 1], 'rate limited' => [429, 1], 'redirect' => [302, 1]];
    }

    #[DataProvider('remoteFailures')]
    public function test_remote_failure_never_breaks_a_lead_and_retries_only_network_or_5xx(string|int $failure, int $expectedAttempts): void
    {
        $attempts = 0;
        $bodies = [];
        $this->fakeVerification(function ($request, $options) use ($failure, &$attempts, &$bodies) {
            $attempts++;
            $bodies[] = $request->body();
            $this->assertSame(3, $options['timeout']);
            $this->assertSame(3, $options['connect_timeout']);
            $this->assertFalse($options['allow_redirects']);
            if ($failure === 'timeout') {
                throw new ConnectionException('Simulated timeout');
            }

            return Http::response('Failure', $failure);
        });
        $this->postJson('/user-choose-doors', $this->lead())->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('visit_requests', 1);
        $this->assertSame($expectedAttempts, $attempts);
        $this->assertCount(1, array_unique($bodies));
    }

    public function test_duplicate_is_acknowledged_without_retry(): void
    {
        $this->fakeVerification(Http::response(['ok' => true, 'duplicate' => true]));
        $this->postJson('/user-choose-doors', $this->lead())->assertOk();
        $this->assertVerification('consultation');
    }

    public function test_report_is_scheduled_after_response_even_without_a_queue_worker(): void
    {
        Bus::fake([VerifySerpAgentConversion::class]);
        $this->postJson('/user-choose-doors', $this->lead())->assertOk();
        $this->assertDatabaseCount('visit_requests', 1);
        Bus::assertDispatchedAfterResponse(VerifySerpAgentConversion::class);
        Http::assertNothingSent();
    }

    public function test_failure_to_schedule_does_not_change_form_success(): void
    {
        Bus::partialMock()->shouldReceive('dispatchAfterResponse')->once()->andThrow(new \RuntimeException('Cannot schedule'));
        $this->postJson('/user-choose-doors', $this->lead())->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('visit_requests', 1);
        Http::assertNothingSent();
    }

    public function test_missing_secret_disables_reporting_without_losing_the_lead(): void
    {
        config()->set('serp-agent.secret', '');
        $this->postJson('/user-choose-doors', $this->lead())->assertOk();
        $this->assertDatabaseCount('visit_requests', 1);
        Http::assertNothingSent();
    }

    public function test_old_queued_reports_are_consumed_without_creating_duplicate_conversions(): void
    {
        config()->set('serp-agent.conversions.api_key', 'old-test-key');
        (new ReportSerpAgentConversion('order_282'))->handle();
        Http::assertNothingSent();
    }

    public function test_readiness_check_never_sends_an_event_or_prints_a_secret(): void
    {
        $this->artisan('serp-agent:diagnose')
            ->expectsOutput('SERP Agent verification: READY (existing webhook secret; after-response; no network request).')
            ->doesntExpectOutputToContain('test-secret')
            ->assertExitCode(0);
        config()->set('serp-agent.secret', '');
        $this->artisan('serp-agent:diagnose')
            ->expectsOutput('SERP Agent verification: INACTIVE (check enabled flag, webhook secret and project ID).')
            ->assertExitCode(1);
        Http::assertNothingSent();
    }

    public function test_disabled_reporting_does_not_lose_a_lead(): void
    {
        config()->set('serp-agent.conversions.enabled', false);
        $this->postJson('/user-choose-doors', $this->lead())->assertOk();
        $this->assertDatabaseCount('visit_requests', 1);
        Http::assertNothingSent();
    }

    public function test_customer_review_is_not_a_conversion(): void
    {
        $this->postJson('/customer-review', $this->lead([
            'first_name' => 'Оксана', 'last_name' => 'Коваль', 'email' => 'private@example.com',
            'rating' => 5, 'review' => 'Дуже вдячна команді за вчасну доставку дверей.',
        ]))->assertCreated();
        $this->assertDatabaseCount('customer_reviews', 1);
        Http::assertNothingSent();
    }

    public function test_product_review_is_not_a_conversion(): void
    {
        $product = $this->makeProduct();
        $this->post('/product-review', $this->lead([
            'product_id' => $product->id, 'author_name' => 'Оксана', 'author_email' => 'private@example.com',
            'rating' => 5, 'review' => 'Дуже вдячна команді за вчасну доставку дверей.',
        ]))->assertRedirect();
        $this->assertDatabaseCount('product_reviews', 1);
        Http::assertNothingSent();
    }

    public function test_sign_in_is_not_a_conversion(): void
    {
        $user = User::factory()->create();
        $this->postJson(route('auth.sign-in'), $this->lead(['email' => $user->email, 'password' => 'password']))->assertOk();
        $this->assertAuthenticatedAs($user);
        Http::assertNothingSent();
    }

    public function test_registration_is_not_a_conversion(): void
    {
        DB::table('roles')->insertOrIgnore(['id' => 2, 'role' => 'User', 'role_slug' => 'user']);
        $this->post(route('auth.sign-up'), $this->lead([
            'first_name' => 'Оксана', 'last_name' => 'Коваль', 'email' => 'private@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123', 'phone' => '380671112233',
        ]))->assertOk();
        $this->assertDatabaseHas('users', ['email' => 'private@example.com']);
        Http::assertNothingSent();
    }
}
