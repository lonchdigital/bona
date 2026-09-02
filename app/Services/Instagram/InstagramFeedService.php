<?php

namespace App\Services\Instagram;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class InstagramFeedService
{
    public const CACHE_KEY = 'instagram_feed';

    public const STALE_CACHE_KEY = 'instagram_feed_stale';

    public function __construct(private readonly InstagramCredentialStore $credentials) {}

    public function getFeed(): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        $accessToken = $this->credentials->accessToken();
        $accountId = $this->credentials->accountId();

        if ($accessToken === '' || $accountId === '') {
            return $this->staleFeed();
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get($this->mediaUrl($accountId), [
                    'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count,children{media_type,media_url,thumbnail_url}',
                    'limit' => 12,
                    'access_token' => $accessToken,
                ]);

            if (! $response->successful()) {
                $this->logFailure($response);

                return $this->staleFeed();
            }

            $feed = collect($response->json('data', []))
                ->map(fn (array $media): ?array => $this->normalize($media))
                ->filter()
                ->take(12)
                ->values()
                ->all();

            if ($feed === []) {
                Log::warning('Instagram feed returned no displayable media.');

                return $this->staleFeed();
            }

            Cache::put(self::CACHE_KEY, $feed, now()->addMinutes(30));
            Cache::put(self::STALE_CACHE_KEY, $feed, now()->addDays(7));

            return $feed;
        } catch (Throwable $exception) {
            Log::warning('Instagram feed request failed.', [
                'exception' => $exception::class,
                'code' => $exception->getCode(),
            ]);

            return $this->staleFeed();
        }
    }

    private function normalize(array $media): ?array
    {
        $mediaType = (string) ($media['media_type'] ?? 'IMAGE');
        $mediaUrl = $media['media_url'] ?? null;
        $imageUrl = $mediaType === 'VIDEO'
            ? ($media['thumbnail_url'] ?? null)
            : $mediaUrl;

        if (! is_string($imageUrl) || $imageUrl === '' || empty($media['permalink'])) {
            return null;
        }

        return [
            'id' => (string) ($media['id'] ?? ''),
            'media_type' => $mediaType,
            'image_url' => $imageUrl,
            'content_url' => is_string($mediaUrl) ? $mediaUrl : $imageUrl,
            'permalink' => (string) $media['permalink'],
            'caption' => trim((string) ($media['caption'] ?? '')),
            'timestamp' => $media['timestamp'] ?? null,
            'like_count' => is_numeric($media['like_count'] ?? null)
                ? (int) $media['like_count']
                : null,
            'comments_count' => is_numeric($media['comments_count'] ?? null)
                ? (int) $media['comments_count']
                : null,
        ];
    }

    private function mediaUrl(string $accountId): string
    {
        $version = (string) config('services.instagram.graph_version', 'v26.0');

        return "https://graph.instagram.com/{$version}/{$accountId}/media";
    }

    private function staleFeed(): ?array
    {
        $feed = Cache::get(self::STALE_CACHE_KEY);

        return is_array($feed) && $feed !== [] ? $feed : null;
    }

    private function logFailure(Response $response): void
    {
        Log::warning('Instagram feed API rejected the request.', [
            'status' => $response->status(),
            'error_code' => $response->json('error.code'),
            'error_subcode' => $response->json('error.error_subcode'),
            'error_type' => $response->json('error.type'),
        ]);
    }
}
