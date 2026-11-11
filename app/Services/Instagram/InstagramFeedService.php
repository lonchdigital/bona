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

    private const MEDIA_URL_EXPIRY_GRACE_SECONDS = 3600;

    public function __construct(private readonly InstagramCredentialStore $credentials) {}

    public function getFeed(): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached) && $this->feedHasUsableMediaUrls($cached)) {
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
                ->filter(fn (?array $media): bool => $media !== null
                    && $this->mediaUrlIsUsable($media['image_url'] ?? null))
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

        if (! is_array($feed)) {
            return null;
        }

        $feed = array_values(array_filter(
            $feed,
            fn (mixed $media): bool => is_array($media)
                && $this->mediaUrlIsUsable($media['image_url'] ?? null),
        ));

        return $feed !== [] ? $feed : null;
    }

    private function feedHasUsableMediaUrls(array $feed): bool
    {
        return $feed !== [] && collect($feed)->every(fn (mixed $media): bool => is_array($media)
            && $this->mediaUrlIsUsable($media['image_url'] ?? null));
    }

    private function mediaUrlIsUsable(mixed $url): bool
    {
        if (! is_string($url) || $url === '') {
            return false;
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $expiresAt = $query['oe'] ?? null;

        // Other image hosts and older Instagram URL formats do not expose an
        // expiry parameter, so only reject URLs whose expiry is explicit.
        if (! is_string($expiresAt) || $expiresAt === '') {
            return true;
        }

        if (! ctype_xdigit($expiresAt)) {
            return false;
        }

        return hexdec($expiresAt) > now()->addSeconds(self::MEDIA_URL_EXPIRY_GRACE_SECONDS)->timestamp;
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
