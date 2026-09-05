<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class CheckoutAuthenticationTest extends TestCase
{
    use MakesShopData;
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

    public function test_checkout_modal_can_sign_in_without_loading_the_standalone_login_page(): void
    {
        $user = User::factory()->create(['email' => 'modal@example.com']);

        $this->postJson(route('auth.sign-in'), [
            'email' => $user->email,
            'password' => 'password',
            'redirect_to' => '/checkout',
            'checkout_draft' => json_encode([
                'delivery_type_id' => '4',
                'payment_type_id' => '2',
                'comment' => 'Зателефонуйте після 15:00',
                'email' => 'must-not-be-restored@example.com',
            ], JSON_THROW_ON_ERROR),
        ])
            ->assertOk()
            ->assertJsonPath('redirect_to', '/checkout');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('4', session()->getOldInput('delivery_type_id'));
        $this->assertSame('2', session()->getOldInput('payment_type_id'));
        $this->assertSame('Зателефонуйте після 15:00', session()->getOldInput('comment'));
        $this->assertNull(session()->getOldInput('email'));
    }

    public function test_checkout_modal_returns_an_inline_error_for_incorrect_credentials(): void
    {
        User::factory()->create(['email' => 'modal@example.com']);

        $this->postJson(route('auth.sign-in'), [
            'email' => 'modal@example.com',
            'password' => 'incorrect',
            'redirect_to' => '/checkout',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.password.0', trans('auth.credentials_are_incorrect'));

        $this->assertGuest();
    }

    public function test_guest_checkout_renders_an_accessible_inline_sign_in_dialog(): void
    {
        $this->seedCurrency();
        $product = $this->makeProduct();

        $this->keepCookies($this->postJson(
            route('store.cart.add-product', ['productSlug' => $product->slug]),
            ['product_count' => 1]
        ))->assertOk();

        $this->get(route('store.checkout.page'))
            ->assertOk()
            ->assertSee('data-checkout-auth-open', false)
            ->assertSee('data-checkout-auth-dialog', false)
            ->assertSee('aria-labelledby="checkout-auth-title"', false)
            ->assertSee('autocomplete="current-password"', false);
    }
}
