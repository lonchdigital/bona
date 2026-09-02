<?php

namespace Tests\Feature;

use App\Models\ApplicationConfig;
use App\Models\Role;
use App\Models\User;
use App\Services\Instagram\InstagramCredentialStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class InstagramOAuthSecurityTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    private function admin(): User
    {
        DB::table('roles')->insertOrIgnore([
            'id' => Role::ADMIN_ROLE_ID,
            'role' => 'Admin',
            'role_slug' => 'admin',
        ]);

        $admin = $this->author();
        $admin->update(['role_id' => Role::ADMIN_ROLE_ID]);

        return $admin;
    }

    public function test_guests_cannot_start_instagram_oauth(): void
    {
        $this->get(route('admin.instagram.auth'))
            ->assertForbidden();
    }

    public function test_oauth_start_creates_a_state_nonce_for_an_admin(): void
    {
        config()->set('services.instagram.app_id', 'instagram-app-id');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('admin.instagram.auth'));

        $response->assertRedirectContains('instagram.com/oauth/authorize');
        $response->assertRedirectContains('scope=instagram_business_basic');
        $response->assertRedirectContains('force_reauth=true');
        $this->assertIsString(session('instagram_oauth_state'));
        $this->assertSame(64, strlen(session('instagram_oauth_state')));
    }

    public function test_oauth_callback_rejects_an_invalid_state(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->withSession(['instagram_oauth_state' => str_repeat('a', 64)])
            ->get(route('admin.instagram.callback', [
                'code' => 'authorization-code',
                'state' => str_repeat('b', 64),
            ]))
            ->assertForbidden();
    }

    public function test_oauth_callback_discovers_and_stores_the_connected_instagram_account(): void
    {
        config()->set('services.instagram.app_id', 'instagram-app-id');
        config()->set('services.instagram.app_secret', 'instagram-app-secret');
        $admin = $this->admin();
        $state = str_repeat('a', 64);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/v26.0/me')) {
                return Http::response([
                    'user_id' => 'instagram-account-id',
                    'username' => 'bona_doors',
                ]);
            }

            if (str_contains($request->url(), 'graph.instagram.com/access_token')) {
                return Http::response([
                    'access_token' => 'long-lived-token',
                    'expires_in' => 5_184_000,
                ]);
            }

            return Http::response([
                'data' => [[
                    'access_token' => 'short-lived-token',
                    'user_id' => 'instagram-scoped-id',
                    'permissions' => 'instagram_business_basic',
                ]],
            ]);
        });

        $this->actingAs($admin)
            ->withSession(['instagram_oauth_state' => $state])
            ->get(route('admin.instagram.callback', [
                'code' => 'authorization-code',
                'state' => $state,
            ]))
            ->assertRedirect(route('admin.application-config.edit.page'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('application_configs', ['config_name' => 'instagramBusinessAccountId']);
        $this->assertSame(
            'instagram-account-id',
            ApplicationConfig::where('config_name', 'instagramBusinessAccountId')->value('config_data'),
        );
        $this->assertSame(
            'long-lived-token',
            app(InstagramCredentialStore::class)->accessToken(),
        );
        $this->assertNotSame(
            'long-lived-token',
            ApplicationConfig::where('config_name', 'instagramAccessToken')->value('config_data'),
        );
        $this->assertDatabaseHas('application_configs', ['config_name' => 'instagramTokenExpiresAt']);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.instagram.com/oauth/access_token'
            && $request['client_id'] === 'instagram-app-id'
            && $request['grant_type'] === 'authorization_code');
    }

    public function test_oauth_callback_refuses_a_different_instagram_account(): void
    {
        config()->set('services.instagram.app_id', 'instagram-app-id');
        config()->set('services.instagram.app_secret', 'instagram-app-secret');
        ApplicationConfig::create([
            'config_name' => 'instagram',
            'config_data' => 'https://www.instagram.com/bona_doors/',
        ]);
        $admin = $this->admin();
        $state = str_repeat('a', 64);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/v26.0/me')) {
                return Http::response([
                    'user_id' => 'wrong-account-id',
                    'username' => 'wrong_account',
                ]);
            }

            if (str_contains($request->url(), 'graph.instagram.com/access_token')) {
                return Http::response(['access_token' => 'long-lived-token', 'expires_in' => 5_184_000]);
            }

            return Http::response(['access_token' => 'short-lived-token', 'user_id' => 'scoped-id']);
        });

        $this->actingAs($admin)
            ->withSession(['instagram_oauth_state' => $state])
            ->get(route('admin.instagram.callback', [
                'code' => 'authorization-code',
                'state' => $state,
            ]))
            ->assertRedirect(route('admin.application-config.edit.page'))
            ->assertSessionHasErrors('instagram');

        $this->assertDatabaseMissing('application_configs', ['config_name' => 'instagramAccessToken']);
    }
}
