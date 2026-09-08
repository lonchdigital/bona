<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SendTelegramNotification implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 30;

    public function __construct(public readonly string $message) {}

    public function backoff(): array
    {
        return [15, 60, 300, 900];
    }

    public function handle(): void
    {
        $token = trim((string) config('telegram.bot_token'));
        $chatId = trim((string) config('telegram.chat_id'));

        if (! config('telegram.enabled') || $token === '' || $chatId === '') {
            return;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => mb_substr($this->message, 0, 4000),
            'link_preview_options' => ['is_disabled' => true],
        ];

        $threadId = filter_var(
            config('telegram.message_thread_id'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        if ($threadId !== false) {
            $payload['message_thread_id'] = $threadId;
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout((float) config('telegram.connect_timeout', 5))
                ->timeout((float) config('telegram.timeout', 10))
                ->retry(2, 250, fn (Throwable $exception): bool => $exception instanceof ConnectionException, throw: false)
                ->post(rtrim((string) config('telegram.api_url'), '/').'/bot'.$token.'/sendMessage', $payload);
        } catch (Throwable) {
            // HTTP client exceptions may contain the request URL. Telegram puts
            // the secret bot token in that URL, so never bubble the original
            // message into application or queue logs.
            throw new RuntimeException('Telegram API connection failed.');
        }

        if (! $response->successful() || $response->json('ok') !== true) {
            throw new RuntimeException(sprintf(
                'Telegram rejected a notification with HTTP %d: %s',
                $response->status(),
                mb_substr((string) ($response->json('description') ?: $response->body()), 0, 500),
            ));
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Telegram notification could not be delivered after all retries.', [
            'exception' => $exception ? $exception::class : null,
            'message_fingerprint' => hash('sha256', $this->message),
        ]);
    }
}
