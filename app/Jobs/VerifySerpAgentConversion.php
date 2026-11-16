<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class VerifySerpAgentConversion
{
    use Dispatchable;

    public const ENDPOINT = 'https://app.serp-agent.com/api/conversion-events/verify';

    public function __construct(
        public readonly ?string $eventKey,
        public readonly string $formId,
        public readonly ?string $pageUrl,
    ) {}

    public static function signature(string $secret, string $timestamp, string $body): string
    {
        return rtrim(strtr(base64_encode(hash_hmac('sha256', $timestamp.'.'.$body, $secret, true)), '+/', '-_'), '=');
    }

    public function handle(): void
    {
        try {
            $secret = (string) config('serp-agent.secret');
            $project = (string) config('serp-agent.conversions.project_id');
            if (! config('serp-agent.conversions.enabled') || $secret === '' || $project === '') {
                return;
            }

            // Serialize once: the HMAC covers the exact bytes sent on every retry.
            $body = json_encode([
                'projectId' => $project,
                'eventKey' => $this->eventKey,
                'formPlugin' => 'custom',
                'formId' => $this->formId,
                'pageUrl' => $this->pageUrl,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $timestamp = (string) now()->timestamp;

            $response = Http::acceptJson()
                ->withBody($body, 'application/json')
                ->withHeaders([
                    'X-SerpAgent-Timestamp' => $timestamp,
                    'X-SerpAgent-Signature' => self::signature($secret, $timestamp, $body),
                ])
                ->connectTimeout(3)
                ->timeout(3)
                ->withoutRedirecting()
                ->retry(3, 250, fn (Throwable $error): bool => $error instanceof ConnectionException
                    || ($error instanceof RequestException && $error->response->serverError()), throw: false)
                ->send('POST', self::ENDPOINT);

            if ($response->status() !== 200 || ! $response->json('ok')
                || (! $response->json('verified') && ! $response->json('duplicate'))) {
                Log::warning('Serp Agent verification was not acknowledged.', ['status' => $response->status()]);
            }
        } catch (Throwable) {
            // Never log request/response bodies, credentials or buyer data.
            Log::warning('Serp Agent verification could not be delivered.');
        }
    }
}
