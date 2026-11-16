<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Retired compatibility job for messages left in the queue by older releases.
 * Old reports have no sa_event and cannot be reconciled with browser events.
 * Consume them without sending a second conversion; new submissions use /verify.
 */
class ReportSerpAgentConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 20;

    public function __construct(
        public readonly string $externalId,
        public readonly ?string $pageUrl = null,
        public readonly ?string $landingUrl = null,
        public readonly ?string $occurredAt = null,
        public readonly string $eventType = 'FORM_SUBMIT',
    ) {}

    public function backoff(): array
    {
        return [30, 300];
    }

    public static function forOrder(int $orderId, ?string $pageUrl, ?string $landingUrl, ?Carbon $createdAt = null): self
    {
        return new self(
            externalId: 'order_'.$orderId,
            pageUrl: $pageUrl,
            landingUrl: $landingUrl,
            occurredAt: ($createdAt ?? Carbon::now())->utc()->format('Y-m-d\TH:i:s\Z'),
        );
    }

    public function handle(): void
    {
        // Deliberately no network call, including when an old queued job is retried.
    }
}
