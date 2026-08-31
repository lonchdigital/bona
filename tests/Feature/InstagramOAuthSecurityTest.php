<?php

namespace Tests\Feature;

use App\Models\ApplicationConfig;
use App\Models\Role;
use App\Models\User;
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
        config()->set('services.facebook.client_id', 'facebook-app-id');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('admin.instagram.auth'));

        $response->assertRedirectContains('facebook.com/v26.0/dialog/oauth');
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
        config()->set('services.facebook.client_id', 'facebook-app-id');
        config()->set('services.facebook.client_secret', 'facebook-app-secret');
        $admin = $this->admin();
        $state = str_repeat('a', 64);

        Http::fake(function (Request $request) {
            if (str_contains($request->url(), '/me/accounts')) {
                return Http::response([
                    'data' => [[
                        'id' => 'page-id',
                        'access_token' => 'page-access-token',
                        'instagram_business_account' => [
                            'id' => 'instagram-account-id',
                            'username' => 'bona_doors',
                        ],
                    ]],
                ]);
            }

            if (str_contains($request->url(), 'grant_type=fb_exchange_token')) {
                return Http::response(['access_token' => 'long-lived-token']);
            }

            return Http::response(['access_token' => 'short-lived-token']);
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
            'page-access-token',
            ApplicationConfig::where('config_name', 'instagramAccessToken')->value('config_data'),
        );
    }
}
