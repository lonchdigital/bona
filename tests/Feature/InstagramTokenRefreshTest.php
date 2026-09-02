<?php

namespace Tests\Feature;

use App\Services\Instagram\InstagramCredentialStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InstagramTokenRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_refreshes_a_token_that_is_close_to_expiration(): void
    {
        app(InstagramCredentialStore::class)->store(
            'old-token',
            'instagram-account-id',
            'bona_doors',
            5 * 24 * 60 * 60,
        );

        Http::fake([
            'graph.instagram.com/refresh_access_token*' => Http::response([
                'access_token' => 'refreshed-token',
                'expires_in' => 5_184_000,
            ]),
        ]);

        $this->artisan('instagram:refresh-token')
            ->expectsOutput('Instagram access token refreshed.')
            ->assertSuccessful();

        $this->assertSame('refreshed-token', app(InstagramCredentialStore::class)->accessToken());
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'refresh_access_token')
            && $request['grant_type'] === 'ig_refresh_token');
    }

    public function test_it_does_not_refresh_a_new_token_early(): void
    {
        app(InstagramCredentialStore::class)->store(
            'current-token',
            'instagram-account-id',
            'bona_doors',
            50 * 24 * 60 * 60,
        );

        Http::fake();

        $this->artisan('instagram:refresh-token')
            ->expectsOutput('Instagram access token does not need refreshing yet.')
            ->assertSuccessful();

        Http::assertNothingSent();
    }
}
