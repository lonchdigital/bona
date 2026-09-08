<?php

namespace Tests\Feature;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Jobs\SendTelegramNotification;
use App\Models\Order;
use App\Models\VisitRequest;
use App\Services\Telegram\TelegramNotificationService;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Queue::fake();
        config()->set('domain.admin_notification_emails', '');
        config()->set('telegram.enabled', true);
        config()->set('telegram.bot_token', 'telegram-test-token');
        config()->set('telegram.chat_id', '-1001234567890');
        config()->set('telegram.message_thread_id', null);
        config()->set('telegram.api_url', 'https://api.telegram.test');
    }

    public function test_a_consultation_form_is_saved_with_context_and_queued_for_telegram(): void
    {
        $source = 'http://localhost/services/consultation';

        $this->postJson(route('store.choose.doors'), [
            'title' => 'Необхідна консультація',
            'name' => 'Оксана Коваль',
            'phone' => '+38 (067) 111 22 33',
            'description' => 'Цікавлять приховані двері',
            'agree' => 1,
            'source_url' => $source,
        ])->assertOk();

        $lead = VisitRequest::firstOrFail();
        $this->assertSame('Цікавлять приховані двері', $lead->description);
        $this->assertSame($source, $lead->source_url);

        Queue::assertPushed(SendTelegramNotification::class, function (SendTelegramNotification $job) use ($lead, $source) {
            return str_contains($job->message, 'НОВА ЗАЯВКА #'.$lead->id)
                && str_contains($job->message, 'Оксана Коваль')
                && str_contains($job->message, '+38 (067) 111 22 33')
                && str_contains($job->message, 'Цікавлять приховані двері')
                && str_contains($job->message, $source);
        });
    }

    public function test_an_external_page_cannot_be_injected_as_a_lead_source(): void
    {
        $this->postJson(route('store.choose.doors'), [
            'title' => 'Консультація',
            'name' => 'Іван Коваль',
            'phone' => '+38 (067) 111 22 33',
            'agree' => 1,
            'source_url' => 'https://attacker.example/fake-source',
        ])->assertOk();

        $this->assertNull(VisitRequest::firstOrFail()->source_url);
        Queue::assertPushed(SendTelegramNotification::class, fn (SendTelegramNotification $job) => ! str_contains(
            $job->message,
            'attacker.example',
        ));
    }

    public function test_a_measurement_request_is_saved_and_queued_with_its_product_page(): void
    {
        $source = 'http://localhost/product/artporte-new-york';

        $this->postJson(route('store.order.count.doors'), [
            'title' => 'Виклик майстра',
            'name' => 'Андрій Коваль',
            'phone' => '+38 (067) 444 55 66',
            'agree' => 1,
            'current_product_title' => 'Міжкімнатні двері Artporte Нью-Йорк',
            'current_product_url' => $source,
            'source_url' => $source,
        ])->assertOk();

        $lead = VisitRequest::firstOrFail();
        $this->assertSame('Міжкімнатні двері Artporte Нью-Йорк', $lead->description);
        $this->assertSame($source, $lead->source_url);

        Queue::assertPushed(SendTelegramNotification::class, function (SendTelegramNotification $job) use ($lead, $source) {
            return str_contains($job->message, 'НОВА ЗАЯВКА #'.$lead->id)
                && str_contains($job->message, 'Виклик майстра')
                && str_contains($job->message, 'Андрій Коваль')
                && str_contains($job->message, 'Міжкімнатні двері Artporte Нью-Йорк')
                && str_contains($job->message, $source);
        });
    }

    public function test_a_legacy_measurement_form_keeps_its_product_page_as_the_source(): void
    {
        $source = 'http://localhost/product/artporte-new-york';

        $this->postJson(route('store.order.count.doors'), [
            'title' => 'Виклик майстра',
            'name' => 'Андрій Коваль',
            'phone' => '+38 (067) 444 55 66',
            'agree' => 1,
            'current_product_title' => 'Міжкімнатні двері Artporte Нью-Йорк',
            'current_product_url' => $source,
        ])->assertOk();

        $this->assertSame($source, VisitRequest::firstOrFail()->source_url);
        Queue::assertPushed(SendTelegramNotification::class, fn (SendTelegramNotification $job) => str_contains(
            $job->message,
            $source,
        ));
    }

    public function test_a_one_click_order_queues_the_product_customer_and_source_page(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct([
            'name' => ['uk' => 'Двері Artporte Нью-Йорк', 'ru' => 'Дверь Artporte Нью-Йорк'],
            'price' => 4640,
        ]);
        $source = 'http://localhost/product/'.$product->slug;

        $this->postJson(route('store.product.one-click-order', ['productSlug' => $product->slug]), [
            'name' => 'Марія',
            'phone' => '+38(050)999-88-77',
            'agree' => 1,
            'source_url' => $source,
        ])->assertOk();

        $order = Order::firstOrFail();
        Queue::assertPushed(SendTelegramNotification::class, function (SendTelegramNotification $job) use ($order, $product, $source) {
            return str_contains($job->message, 'НОВЕ ЗАМОВЛЕННЯ #BD-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT))
                && str_contains($job->message, 'Покупка в один клік')
                && str_contains($job->message, 'Марія')
                && str_contains($job->message, '+38(050)999-88-77')
                && str_contains($job->message, 'Двері Artporte Нью-Йорк')
                && str_contains($job->message, route('store.product.page', ['productSlug' => $product->slug]))
                && str_contains($job->message, $source)
                && ! str_contains($job->message, 'one-click-380');
        });
    }

    public function test_checkout_order_message_contains_payment_delivery_and_recipient_details(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct(['price' => 10_000]);
        $order = Order::create([
            'status_id' => OrderStatusesDataClass::STATUS_NEW,
            'user_id' => $this->author()->id,
            'delivery_type_id' => DeliveryTypesDataClass::ADDRESS_DELIVERY,
            'payment_type_id' => PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK,
            'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_CUSTOM,
            'custom_recipient_first_name' => 'Петро',
            'custom_recipient_last_name' => 'Коваль',
            'custom_recipient_phone' => '+38(067)222-33-44',
            'custom_recipient_email' => 'recipient@example.com',
            'city' => 'Одеса',
            'street' => 'Дерибасівська',
            'building_number' => '1',
            'installment_provider' => 'monobank',
            'installment_period' => 3,
            'installment_surcharge_percent' => 2.9,
            'installment_surcharge_amount' => 290,
        ]);
        $order->products()->attach($product->id, [
            'count' => 1,
            'price' => 10_000,
            'attributes_price' => 0,
        ]);

        app(TelegramNotificationService::class)->notifyOrderCreated(
            $order,
            'Оформлення на сайті',
            'http://localhost/checkout',
        );

        Queue::assertPushed(SendTelegramNotification::class, 1);
        $message = Queue::pushed(SendTelegramNotification::class)->first()->message;

        $this->assertStringContainsString('monobank', $message);
        $this->assertStringContainsString('Кількість платежів: 3', $message);
        $this->assertStringContainsString('Петро Коваль', $message);
        $this->assertStringContainsString('Дерибасівська', $message);
        $this->assertStringContainsString('До сплати:', $message);
        $this->assertStringContainsString('Внутрішня надбавка банку:', $message);
    }

    public function test_the_queue_job_is_encrypted_and_sends_to_the_configured_topic(): void
    {
        config()->set('telegram.message_thread_id', 17);
        Http::fake(['api.telegram.test/*' => Http::response(['ok' => true], 200)]);

        $job = new SendTelegramNotification('Тестове повідомлення');
        $this->assertInstanceOf(ShouldBeEncrypted::class, $job);
        $job->handle();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.telegram.test/bottelegram-test-token/sendMessage'
                && $request['chat_id'] === '-1001234567890'
                && $request['message_thread_id'] === 17
                && $request['text'] === 'Тестове повідомлення'
                && $request['link_preview_options']['is_disabled'] === true;
        });
    }

    public function test_telegram_test_command_does_not_print_or_log_the_token(): void
    {
        Http::fake(['api.telegram.test/*' => Http::response(['ok' => true], 200)]);

        $this->artisan('telegram:test')
            ->expectsOutput('Telegram test message was delivered.')
            ->assertSuccessful();
    }

    public function test_disabled_telegram_does_not_queue_any_personal_data(): void
    {
        config()->set('telegram.enabled', false);

        $this->postJson(route('store.choose.doors'), [
            'title' => 'Консультація',
            'name' => 'Іван Коваль',
            'phone' => '+38 (067) 111 22 33',
            'agree' => 1,
        ])->assertOk();

        Queue::assertNothingPushed();
    }
}
