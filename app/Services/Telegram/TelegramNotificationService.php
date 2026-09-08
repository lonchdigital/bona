<?php

namespace App\Services\Telegram;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Jobs\SendTelegramNotification;
use App\Models\Order;
use App\Models\VisitRequest;
use App\Services\Currency\CurrencyService;
use App\Services\Pricing\PricingService;
use App\Support\Commerce\ProductBundle;
use App\Support\Commerce\ProductConfiguration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TelegramNotificationService
{
    public function __construct(
        private readonly PricingService $pricingService,
        private readonly CurrencyService $currencyService,
    ) {}

    public function isConfigured(): bool
    {
        return (bool) config('telegram.enabled')
            && trim((string) config('telegram.bot_token')) !== ''
            && trim((string) config('telegram.chat_id')) !== '';
    }

    public function notifyLead(
        VisitRequest $lead,
        ?string $description = null,
        ?string $sourceUrl = null,
        array $extra = [],
    ): void {
        if (! $this->isConfigured()) {
            return;
        }

        $lines = [
            '📩 НОВА ЗАЯВКА #'.$lead->id,
            '',
            'Форма: '.$this->value($lead->form_title, 'Заявка з сайту'),
            'Дата: '.$lead->created_at?->timezone('Europe/Kyiv')->format('d.m.Y H:i'),
            'Ім’я: '.$this->value($lead->name),
            'Телефон: '.$this->value($lead->phone),
        ];

        foreach ($extra as $label => $value) {
            if (! blank($value)) {
                $lines[] = trim((string) $label).': '.$this->value($value);
            }
        }

        if (! blank($description)) {
            $lines[] = '';
            $lines[] = 'Деталі:';
            $lines[] = $this->value($description);
        }

        if (! blank($sourceUrl)) {
            $lines[] = '';
            $lines[] = 'Сторінка: '.$this->value($sourceUrl);
        }

        $lines[] = 'Адмінка: '.route('admin.visit-request.details.page', $lead);

        $this->dispatch($lines);
    }

    public function notifyOrderCreated(Order $order, string $kind, ?string $sourceUrl = null): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        try {
            $order->loadMissing(['user', 'products.productType.attributes', 'products.colors', 'promoCode', 'region']);
            $summary = $this->pricingService->forOrder($order);
            $currency = $this->currencyService->getBaseCurrency()->name_short ?: 'грн';
            $paymentType = PaymentTypesDataClass::get($order->payment_type_id)['name'] ?? 'Уточнити з клієнтом';
            $paymentStatus = OrderPaymentStatusesDataClass::get($order->payment_status_id)['name'] ?? '—';
            $deliveryType = DeliveryTypesDataClass::get($order->delivery_type_id)['name'] ?? 'Уточнити з клієнтом';

            $lines = [
                '🛒 НОВЕ ЗАМОВЛЕННЯ #BD-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                '',
                'Тип: '.$kind,
                'Дата: '.$order->created_at?->timezone('Europe/Kyiv')->format('d.m.Y H:i'),
                'Оплата: '.$paymentType,
                'Статус оплати: '.$paymentStatus,
            ];

            if ($order->installment_period) {
                $lines[] = 'Провайдер: '.$this->paymentProvider($order);
                $lines[] = 'Кількість платежів: '.$order->installment_period;
                $lines[] = 'Щомісячно: '.$this->money(
                    (float) $summary['total'] / max(1, (int) $order->installment_period),
                    $currency,
                );
            }

            $lines = array_merge($lines, [
                '',
                'Клієнт: '.$this->value(trim(($order->user?->first_name ?? '').' '.($order->user?->last_name ?? ''))),
                'Телефон: '.$this->value($order->user?->phone ?: $order->custom_recipient_phone),
                'Email: '.$this->value($this->customerEmail($order)),
                'Отримання: '.$deliveryType,
            ]);

            if ((int) $order->recipient_type_id === RecipientTypesDataClass::RECIPIENT_CUSTOM) {
                $lines[] = 'Отримувач: '.$this->value(trim(
                    ((string) $order->custom_recipient_first_name).' '.((string) $order->custom_recipient_last_name)
                ));
                $lines[] = 'Телефон отримувача: '.$this->value($order->custom_recipient_phone);
                $lines[] = 'Email отримувача: '.$this->value($order->custom_recipient_email);
            }

            $address = $this->deliveryAddress($order);
            if ($address !== '') {
                $lines[] = 'Адреса: '.$address;
            }

            if (! blank($order->comment)) {
                $lines[] = 'Коментар: '.$this->value($order->comment);
            }

            $lines[] = '';
            $lines[] = 'Товари:';

            foreach (ProductBundle::group($order->products) as $groupIndex => $group) {
                $parent = $group['parent'];
                $prefix = ($groupIndex + 1).'. ';
                if ($group['is_bundle']) {
                    $lines[] = $prefix.'Комплект: '.$this->value($parent->name);
                    $lines = array_merge($lines, $this->productLines($parent, $currency, '   Двері'));

                    foreach ($group['items'] as $item) {
                        $category = ProductBundle::localizedCategory($item->pivot->bundle_category);
                        $lines = array_merge($lines, $this->productLines(
                            $item,
                            $currency,
                            '   '.($category !== '' ? $category : 'Комплектуюча'),
                        ));
                    }
                } else {
                    $lines = array_merge($lines, $this->productLines($parent, $currency, $prefix));
                }
            }

            $lines = array_merge($lines, [
                '',
                'Товари: '.$this->money($summary['products'], $currency),
                'Знижка: '.$this->money($summary['discount'], $currency),
                'Доставка: '.$this->money($summary['delivery'], $currency),
                'До сплати: '.$this->money($summary['total'], $currency),
            ]);

            if ($order->installment_period) {
                $lines[] = 'Внутрішня надбавка банку: '.$this->money($summary['installment_fee'], $currency);
            }

            if ($order->promoCode) {
                $lines[] = 'Промокод: '.$this->value($order->promoCode->code);
            }

            if (! blank($sourceUrl)) {
                $lines[] = 'Джерело: '.$this->value($sourceUrl);
            }

            $lines[] = 'Адмінка: '.route('admin.order.edit', ['order' => $order->id]);

            $this->dispatch($lines);
        } catch (Throwable $exception) {
            // Notification formatting must never roll back a successfully
            // assembled order. Do not log the message body or customer data.
            Log::error('Telegram order notification could not be prepared.', [
                'order_id' => $order->id,
                'exception' => $exception::class,
            ]);
        }
    }

    public function notifyPaymentEvent(
        Order $order,
        string $provider,
        string $state,
        ?string $details = null,
        ?string $traceId = null,
    ): void {
        if (! $this->isConfigured()) {
            return;
        }

        $heading = match ($state) {
            'success' => '✅ ОПЛАТА ПІДТВЕРДЖЕНА',
            'failure' => '⚠️ ПРОБЛЕМА З ОПЛАТОЮ',
            default => 'ℹ️ СТАТУС ОПЛАТИ',
        };

        $lines = [
            $heading,
            'Замовлення: #BD-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'Провайдер: '.$this->value($provider),
            'Стан: '.$this->value($state),
        ];

        if (! blank($details)) {
            $lines[] = 'Відповідь: '.$this->value($details);
        }

        if (! blank($traceId)) {
            $lines[] = 'Trace ID: '.$this->value($traceId);
        }

        $lines[] = 'Адмінка: '.route('admin.order.edit', ['order' => $order->id]);

        $this->dispatch($lines);
    }

    public function notifyIntegrationIssue(
        string $provider,
        string $operation,
        ?string $details = null,
        ?string $traceId = null,
        ?string $sourceUrl = null,
    ): void {
        if (! $this->isConfigured()) {
            return;
        }

        $lines = [
            '🚨 ПЛАТІЖНА ІНТЕГРАЦІЯ НЕ ВІДПОВІДАЄ',
            'Провайдер: '.$this->value($provider),
            'Операція: '.$this->value($operation),
        ];

        if (! blank($details)) {
            $lines[] = 'Відповідь: '.$this->value($details);
        }

        if (! blank($traceId)) {
            $lines[] = 'Trace ID: '.$this->value($traceId);
        }

        if (! blank($sourceUrl)) {
            $lines[] = 'Сторінка: '.$this->value($sourceUrl);
        }

        $this->dispatch($lines);
    }

    public function notifyIntegrationIssueOnce(
        string $deduplicationKey,
        string $provider,
        string $operation,
        ?string $details = null,
        ?string $traceId = null,
        ?string $sourceUrl = null,
    ): void {
        if (! $this->isConfigured()) {
            return;
        }

        $minutes = max(1, (int) config('payment.reconciliation.alert_cooldown_minutes', 360));
        $cacheKey = 'payment-alert:'.hash('sha256', $deduplicationKey);

        if (! Cache::add($cacheKey, true, now()->addMinutes($minutes))) {
            return;
        }

        $this->notifyIntegrationIssue($provider, $operation, $details, $traceId, $sourceUrl);
    }

    public function paymentProvider(Order $order): string
    {
        return match ((int) $order->payment_type_id) {
            PaymentTypesDataClass::CARD_PAYMENT => 'LiqPay',
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART => 'PrivatBank — Оплата частинами',
            PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK => 'monobank — Покупка частинами',
            default => PaymentTypesDataClass::get($order->payment_type_id)['name'] ?? 'Оплата',
        };
    }

    private function productLines(mixed $product, string $currency, string $label): array
    {
        $quantity = max(1, (int) $product->pivot->count);
        $unit = (float) $product->pivot->price + (float) ($product->pivot->attributes_price ?? 0);
        $lines = [
            $label.': '.$this->value($product->name),
            '      '.$quantity.' × '.$this->money($unit, $currency).' = '.$this->money($quantity * $unit, $currency),
        ];

        foreach (ProductConfiguration::for($product, $product->pivot->attributes) as $configuration) {
            $name = trim((string) $configuration['name']);
            $lines[] = '      '.($name !== '' ? $name.': ' : '').$this->value($configuration['label']);
        }

        $lines[] = '      '.route('store.product.page', ['productSlug' => $product->slug]);

        return $lines;
    }

    private function deliveryAddress(Order $order): string
    {
        if ((int) $order->delivery_type_id === DeliveryTypesDataClass::ADDRESS_DELIVERY) {
            return collect([
                $order->region?->name,
                $order->district,
                $order->city,
                $order->street,
                $order->building_number,
                $order->apartment_number ? 'кв. '.$order->apartment_number : null,
                $order->floor_number ? 'поверх '.$order->floor_number : null,
            ])->filter(fn ($value) => ! blank($value))->map(fn ($value) => $this->value($value))->implode(', ');
        }

        if ((int) $order->delivery_type_id === DeliveryTypesDataClass::NP_DELIVERY) {
            return collect([$order->np_city, $order->np_department])
                ->filter(fn ($value) => ! blank($value))
                ->map(fn ($value) => $this->value($value))
                ->implode(', ');
        }

        return collect([$order->sat_city, $order->sat_department])
            ->filter(fn ($value) => ! blank($value))
            ->map(fn ($value) => $this->value($value))
            ->implode(', ');
    }

    private function dispatch(array $lines): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $messages = $this->splitMessage(
            collect($lines)
                ->map(fn ($line) => rtrim((string) $line))
                ->implode("\n"),
        );

        $queueMessages = function () use ($messages): void {
            foreach ($messages as $message) {
                try {
                    SendTelegramNotification::dispatch($message);
                } catch (Throwable $exception) {
                    // A notification transport must never invalidate a saved
                    // order or lead. The queue worker will retry delivery once
                    // the job exists; an insertion failure is logged safely.
                    Log::error('Telegram notification could not be queued.', [
                        'exception' => $exception::class,
                        'message_fingerprint' => hash('sha256', $message),
                    ]);
                }
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($queueMessages);
        } else {
            $queueMessages();
        }
    }

    /** @return array<int, string> */
    private function splitMessage(string $message, int $limit = 3800): array
    {
        if (mb_strlen($message) <= $limit) {
            return [$message];
        }

        $chunks = [];
        $current = '';

        foreach (explode("\n", $message) as $line) {
            while (mb_strlen($line) > $limit) {
                if ($current !== '') {
                    $chunks[] = $current;
                    $current = '';
                }

                $chunks[] = mb_substr($line, 0, $limit);
                $line = mb_substr($line, $limit);
            }

            $candidate = $current === '' ? $line : $current."\n".$line;
            if (mb_strlen($candidate) > $limit) {
                $chunks[] = $current;
                $current = $line;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        $total = count($chunks);

        return collect($chunks)
            ->map(fn (string $chunk, int $index) => sprintf('[%d/%d]\n%s', $index + 1, $total, $chunk))
            ->all();
    }

    private function customerEmail(Order $order): ?string
    {
        $email = trim((string) $order->user?->email);

        return str_starts_with($email, 'one-click-') ? null : ($email ?: null);
    }

    private function value(mixed $value, string $fallback = '—'): string
    {
        if (is_array($value)) {
            $value = $value[app()->getLocale()]
                ?? $value['uk']
                ?? $value['ru']
                ?? reset($value)
                ?? '';
        }

        $value = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)) ?? '');

        return $value === '' ? $fallback : Str::limit($value, 500);
    }

    private function money(float|int|string $amount, string $currency): string
    {
        return number_format((float) $amount, 2, ',', ' ').' '.$currency;
    }
}
