<?php

namespace Tests\Feature;

use App\Models\ApplicationConfig;
use App\Services\Instagram\InstagramFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InstagramFeedServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget(InstagramFeedService::CACHE_KEY);
        Cache::forget(InstagramFeedService::STALE_CACHE_KEY);

        ApplicationConfig::create([
            'config_name' => 'instagramAccessToken',
            'config_data' => 'access-token',
        ]);
        ApplicationConfig::create([
            'config_name' => 'instagramBusinessAccountId',
            'config_data' => 'instagram-account-id',
        ]);
    }

    public function test_it_normalizes_images_and_video_thumbnails_for_the_homepage(): void
    {
        Http::fake([
            'graph.instagram.com/*' => Http::response([
                'data' => [
                    [
                        'id' => 'image-1',
                        'media_type' => 'IMAGE',
                        'media_url' => 'https://cdn.example.com/image.jpg',
                        'permalink' => 'https://www.instagram.com/p/image-1/',
                        'caption' => 'Нові двері Bona',
                        'like_count' => 42,
                        'comments_count' => 3,
                    ],
                    [
                        'id' => 'video-1',
                        'media_type' => 'VIDEO',
                        'media_url' => 'https://cdn.example.com/video.mp4',
                        'thumbnail_url' => 'https://cdn.example.com/video.jpg',
                        'permalink' => 'https://www.instagram.com/reel/video-1/',
                    ],
                ],
            ]),
        ]);

        $feed = app(InstagramFeedService::class)->getFeed();

        $this->assertCount(2, $feed);
        $this->assertSame('https://cdn.example.com/image.jpg', $feed[0]['image_url']);
        $this->assertSame('https://cdn.example.com/video.jpg', $feed[1]['image_url']);
        $this->assertSame('https://cdn.example.com/video.mp4', $feed[1]['content_url']);
        $this->assertSame('VIDEO', $feed[1]['media_type']);
        $this->assertSame(42, $feed[0]['like_count']);
        $this->assertSame(3, $feed[0]['comments_count']);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/v26.0/instagram-account-id/media')
            && str_contains((string) $request->data()['fields'], 'like_count,comments_count'));
    }

    public function test_it_uses_the_last_successful_feed_when_instagram_is_temporarily_unavailable(): void
    {
        $stale = [[
            'id' => 'stale-1',
            'media_type' => 'IMAGE',
            'image_url' => 'https://cdn.example.com/stale.jpg',
            'permalink' => 'https://www.instagram.com/p/stale-1/',
            'caption' => '',
            'timestamp' => null,
        ]];
        Cache::put(InstagramFeedService::STALE_CACHE_KEY, $stale, now()->addDay());
        Http::fake(['graph.instagram.com/*' => Http::response(['error' => ['code' => 190]], 401)]);

        $this->assertSame($stale, app(InstagramFeedService::class)->getFeed());
    }

    public function test_it_returns_the_latest_twelve_posts_for_the_slider(): void
    {
        Http::fake([
            'graph.instagram.com/*' => Http::response([
                'data' => collect(range(1, 14))->map(fn (int $index): array => [
                    'id' => "image-{$index}",
                    'media_type' => 'IMAGE',
                    'media_url' => "https://cdn.example.com/image-{$index}.jpg",
                    'permalink' => "https://www.instagram.com/p/image-{$index}/",
                ])->all(),
            ]),
        ]);

        $feed = app(InstagramFeedService::class)->getFeed();

        $this->assertCount(12, $feed);
        $this->assertSame('image-1', $feed[0]['id']);
        $this->assertSame('image-12', $feed[11]['id']);
    }

    public function test_it_refreshes_cached_images_before_their_signed_urls_expire(): void
    {
        $this->freezeTime();
        $expiringUrl = 'https://cdn.example.com/image.jpg?oe='.dechex(now()->addMinutes(30)->timestamp);
        $freshUrl = 'https://cdn.example.com/image.jpg?oe='.dechex(now()->addDay()->timestamp);
        Cache::put(InstagramFeedService::CACHE_KEY, [[
            'id' => 'image-1',
            'image_url' => $expiringUrl,
        ]], now()->addHour());
        Http::fake(['graph.instagram.com/*' => Http::response(['data' => [[
            'id' => 'image-1',
            'media_type' => 'IMAGE',
            'media_url' => $freshUrl,
            'permalink' => 'https://www.instagram.com/p/image-1/',
        ]]])]);

        $service = app(InstagramFeedService::class);
        $feed = $service->getFeed();

        $this->assertSame($freshUrl, $feed[0]['image_url']);
        $this->assertSame($feed, Cache::get(InstagramFeedService::STALE_CACHE_KEY));
        $this->assertSame($feed, $service->getFeed());
        Http::assertSentCount(1);
    }

    public function test_an_api_outage_does_not_restore_expired_images_from_the_stale_feed(): void
    {
        $this->freezeTime();
        $expired = ['id' => 'expired', 'image_url' => 'https://cdn.example.com/old.jpg?oe='.dechex(now()->subMinute()->timestamp)];
        $valid = ['id' => 'valid', 'image_url' => 'https://cdn.example.com/new.jpg?oe='.dechex(now()->addDay()->timestamp)];
        Cache::put(InstagramFeedService::STALE_CACHE_KEY, [$expired, $valid], now()->addDays(7));
        Http::fake(['graph.instagram.com/*' => Http::response([], 503)]);

        $service = app(InstagramFeedService::class);
        $this->assertSame([$valid], $service->getFeed());

        $this->travel(2)->days();
        $this->assertNull($service->getFeed());
    }
}
