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
            'graph.facebook.com/*' => Http::response([
                'data' => [
                    [
                        'id' => 'image-1',
                        'media_type' => 'IMAGE',
                        'media_url' => 'https://cdn.example.com/image.jpg',
                        'permalink' => 'https://www.instagram.com/p/image-1/',
                        'caption' => 'Нові двері Bona',
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
        $this->assertSame('VIDEO', $feed[1]['media_type']);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/v26.0/instagram-account-id/media'));
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
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['code' => 190]], 401)]);

        $this->assertSame($stale, app(InstagramFeedService::class)->getFeed());
    }
}
