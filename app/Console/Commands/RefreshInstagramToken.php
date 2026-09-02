<?php

namespace App\Console\Commands;

use App\Services\Instagram\InstagramTokenRefresher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshInstagramToken extends Command
{
    protected $signature = 'instagram:refresh-token {--force : Refresh even when the token is not close to expiration}';

    protected $description = 'Refresh the connected Instagram long-lived access token';

    public function handle(InstagramTokenRefresher $refresher): int
    {
        try {
            $result = $refresher->refreshIfNeeded((bool) $this->option('force'));

            $this->info(match ($result) {
                InstagramTokenRefresher::REFRESHED => 'Instagram access token refreshed.',
                InstagramTokenRefresher::NOT_DUE => 'Instagram access token does not need refreshing yet.',
                default => 'Instagram is not connected; nothing to refresh.',
            });

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::warning('Instagram access token refresh failed.', [
                'exception' => $exception::class,
                'code' => $exception->getCode(),
            ]);
            $this->error('Instagram access token refresh failed.');

            return self::FAILURE;
        }
    }
}
