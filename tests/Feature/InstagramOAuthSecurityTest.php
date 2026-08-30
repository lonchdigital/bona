<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

        $response->assertRedirectContains('facebook.com/v19.0/dialog/oauth');
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
}
