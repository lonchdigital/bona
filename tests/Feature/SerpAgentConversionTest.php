<?php

namespace Tests\Feature;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Jobs\ReportSerpAgentConversion;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Support\MakesShopData;
use Tests\TestCase;

/**
 * A submitted form is not an order. Serp Agent hears about the order itself,
 * from the server, without a buyer ever waiting for that call.
 */
class SerpAgentConversionTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    private function buy(string $slug, array $overrides = [])
    {
        return $this->postJson(
            route('store.product.one-click-order', ['productSlug' => $slug]),
            array_merge([
                'name' => 'Олег',
                'phone' => '+38(067)111-22-33',
                'agree' => 1,
            ], $overrides)
        );
    }

    public function test_a_one_click_order_is_reported_with_the_article_that_produced_it(): void
    {
        Queue::fake();
        $product = $this->makeProduct();

        $this->buy($product->slug, [
            'source_url' => url('/product/'.$product->slug),
            'sa_landing' => url('/blog/yak-obraty-mizhkimnatni-dveri'),
        ])->assertOk();

        $order = Order::firstOrFail();

        Queue::assertPushed(
            ReportSerpAgentConversion::class,
            function (ReportSerpAgentConversion $job) use ($order, $product) {
                return $job->externalId === 'order_'.$order->id
                    && $job->eventType === 'FORM_SUBMIT'
                    && $job->pageUrl === url('/product/'.$product->slug)
                    && $job->landingUrl === url('/blog/yak-obraty-mizhkimnatni-dveri')
                    && (bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', (string) $job->occurredAt);
            }
        );
    }

    public function test_a_landing_address_from_another_site_is_dropped_without_losing_the_order(): void
    {
        Queue::fake();
        $product = $this->makeProduct();

        // A field a visitor can edit must never decide whether an order is
        // taken; it only decides whether attribution is reported.
        $this->buy($product->slug, ['sa_landing' => 'https://example.com/landing'])->assertOk();

        $this->assertSame(1, Order::count());
        Queue::assertPushed(
            ReportSerpAgentConversion::class,
            fn (ReportSerpAgentConversion $job) => $job->landingUrl === null
        );
    }

    public function test_a_checkout_order_is_reported_with_the_article_that_produced_it(): void
    {
        Queue::fake();
        $this->seedCurrency();
        $product = $this->makeProduct(['price' => 4200]);

        $this->keepCookies($this->postJson(
            route('store.cart.add-product', ['productSlug' => $product->slug]),
            ['product_count' => 1]
        ))->assertOk();

        $this->keepCookies($this->post(route('store.checkout.confirm'), [
            'delivery_type_id' => DeliveryTypesDataClass::PICK_UP_DELIVERY,
            'payment_type_id' => PaymentTypesDataClass::CASH_PAYMENT,
            'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
            'agreement' => 1,
            'first_name' => 'Олег',
            'last_name' => 'Петренко',
            'phone' => '+38(067)111-22-33',
            'email' => 'oleh@example.com',
            'sa_landing' => url('/blog/plintus-mdf'),
        ]));

        $order = Order::firstOrFail();

        Queue::assertPushed(
            ReportSerpAgentConversion::class,
            fn (ReportSerpAgentConversion $job) => $job->externalId === 'order_'.$order->id
                && $job->landingUrl === url('/blog/plintus-mdf')
        );
    }

    public function test_an_order_is_booked_even_when_the_report_cannot_be_queued(): void
    {
        Bus::fake();
        $product = $this->makeProduct();

        $this->buy($product->slug)->assertOk();

        $this->assertSame(1, Order::count());
    }

    public function test_the_report_carries_identifiers_and_addresses_only(): void
    {
        Http::fake(['*' => Http::response(['recorded' => true, 'duplicate' => false])]);
        config()->set('serp-agent.conversions.api_key', 'sk_live_test');
        config()->set('serp-agent.conversions.project_id', 'cmt2pz3ou0004b0i29eg5zca1');

        (new ReportSerpAgentConversion(
            externalId: 'order_10482',
            pageUrl: 'https://bona-doors.com.ua/checkout',
            landingUrl: 'https://bona-doors.com.ua/blog/stattia',
            occurredAt: '2026-09-18T09:12:04Z',
        ))->handle();

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://serp-agent.com/v1/projects/cmt2pz3ou0004b0i29eg5zca1/conversions'
                && $request->hasHeader('Authorization', 'Bearer sk_live_test')
                && $body === [
                    'eventType' => 'FORM_SUBMIT',
                    'externalId' => 'order_10482',
                    'pageUrl' => 'https://bona-doors.com.ua/checkout',
                    'landingUrl' => 'https://bona-doors.com.ua/blog/stattia',
                    'occurredAt' => '2026-09-18T09:12:04Z',
                ];
        });
    }

    public function test_nothing_is_sent_without_an_api_key(): void
    {
        Http::fake();
        config()->set('serp-agent.conversions.api_key', '');

        (new ReportSerpAgentConversion(externalId: 'order_1'))->handle();

        Http::assertNothingSent();
    }

    public function test_a_signed_address_never_carries_its_signature(): void
    {
        Http::fake(['*' => Http::response(['recorded' => true])]);
        config()->set('serp-agent.conversions.api_key', 'sk_live_test');

        (new ReportSerpAgentConversion(
            externalId: 'order_7',
            pageUrl: 'https://bona-doors.com.ua/thank-you?order=7&signature=deadbeef&expires=999',
        ))->handle();

        Http::assertSent(function ($request) {
            return $request->data()['pageUrl'] === 'https://bona-doors.com.ua/thank-you?order=7';
        });
    }

    public function test_a_rejected_report_is_logged_and_never_reaches_the_buyer(): void
    {
        Http::fake(['*' => Http::response('nope', 500)]);
        config()->set('serp-agent.conversions.api_key', 'sk_live_test');

        $job = new ReportSerpAgentConversion(externalId: 'order_9');

        try {
            $job->handle();
            $this->fail('A rejected report must be retried by the queue.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('HTTP 500', $exception->getMessage());
        }

        // The final failure only writes a line; no exception leaves the queue.
        $job->failed($exception);
    }
}
