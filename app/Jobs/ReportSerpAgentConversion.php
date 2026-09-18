<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Tells Serp Agent that an order was created.
 *
 * The browser tracker only sees a form leave the page; whether it became an
 * order is known on the server alone. The report carries identifiers and
 * addresses — never a name, a phone number, an e-mail or a comment — and a
 * repeat of the same order id is answered with "duplicate", so a retry cannot
 * inflate the numbers. Nothing here may delay or fail an order: the job is
 * queued, and a project without an API key reports nothing at all.
 */
class ReportSerpAgentConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 20;

    /** Signed links (payment, e-mail) must not leave their signature here. */
    private const SENSITIVE_QUERY_PARAMETERS = ['signature', 'expires', 'token', 'hash'];

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
        $key = trim((string) config('serp-agent.conversions.api_key'));
        $project = trim((string) config('serp-agent.conversions.project_id'));

        if ($key === '' || $project === '') {
            return;
        }

        $payload = array_filter([
            'eventType' => $this->eventType,
            'externalId' => $this->externalId,
            'pageUrl' => $this->safeUrl($this->pageUrl),
            'landingUrl' => $this->safeUrl($this->landingUrl),
            'occurredAt' => $this->occurredAt,
        ], fn ($value) => $value !== null && $value !== '');

        try {
            $response = Http::acceptJson()
                ->withToken($key)
                ->connectTimeout(5)
                ->timeout(10)
                ->retry(2, 250, fn (Throwable $exception): bool => $exception instanceof ConnectionException, throw: false)
                ->post(
                    rtrim((string) config('serp-agent.conversions.base_url'), '/').'/v1/projects/'.$project.'/conversions',
                    $payload,
                );
        } catch (Throwable) {
            // The client's message repeats the request, and the request is
            // authorized with the API key.
            throw new RuntimeException('Serp Agent conversions API connection failed.');
        }

        if ($response->successful()) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Serp Agent rejected a conversion with HTTP %d: %s',
            $response->status(),
            mb_substr((string) $response->body(), 0, 300),
        ));
    }

    /**
     * An order goes through whatever Serp Agent answers, so the last attempt
     * leaves a line in the log and nothing else.
     */
    public function failed(?Throwable $exception): void
    {
        Log::warning('A conversion could not be reported to Serp Agent.', [
            'external_id' => $this->externalId,
            'reason' => $exception?->getMessage(),
        ]);
    }

    private function safeUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $clean = array_diff_key($query, array_flip(self::SENSITIVE_QUERY_PARAMETERS));

        if ($clean === $query) {
            return mb_substr($url, 0, 2048);
        }

        $rebuilt = strtok($url, '?');

        return mb_substr($clean === [] ? $rebuilt : $rebuilt.'?'.http_build_query($clean), 0, 2048);
    }
}
