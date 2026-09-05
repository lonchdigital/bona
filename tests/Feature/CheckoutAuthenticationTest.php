<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_returns_to_checkout_after_signing_in(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);

        $this->post(route('auth.sign-in'), [
            'email' => $user->email,
            'password' => 'password',
            'redirect_to' => '/checkout?payment_type_id=4',
        ])->assertRedirect('/checkout?payment_type_id=4');
    }

    public function test_sign_in_does_not_redirect_to_an_external_site(): void
    {
        $user = User::factory()->create(['email' => 'safe@example.com']);

        $this->post(route('auth.sign-in'), [
            'email' => $user->email,
            'password' => 'password',
            'redirect_to' => 'https://example.net/phishing',
        ])->assertRedirect(route('user.profile.orders.page'));
    }
}
