<?php

namespace App\Services\Instagram;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class InstagramTokenRefresher
{
    public const NOT_CONNECTED = 'not_connected';

    public const NOT_DUE = 'not_due';

    public const REFRESHED = 'refreshed';

    public function __construct(private readonly InstagramCredentialStore $credentials) {}

    public function refreshIfNeeded(bool $force = false): string
    {
        $accessToken = $this->credentials->accessToken();

        if ($accessToken === '') {
            return self::NOT_CONNECTED;
        }

        $expiresAt = $this->credentials->expiresAt();

        if (! $force && $expiresAt?->isAfter(now()->addDays(14))) {
            return self::NOT_DUE;
        }

        $response = Http::acceptJson()
            ->timeout(10)
            ->get('https://graph.instagram.com/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $accessToken,
            ])
            ->throw();

        $refreshedToken = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 5_184_000);

        if (! is_string($refreshedToken) || $refreshedToken === '') {
            throw new RuntimeException('Instagram did not return a refreshed access token.');
        }

        $this->credentials->updateAccessToken($refreshedToken, max($expiresIn, 1));
        Cache::forget(InstagramFeedService::CACHE_KEY);

        return self::REFRESHED;
    }
}
