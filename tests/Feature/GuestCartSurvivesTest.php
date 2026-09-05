<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

/**
 * A cart has to outlive the request that created it.
 *
 * It is keyed on the session id, and Laravel issues a new session id on sign
 * in — so the cart a visitor filled before signing in is looked for under an
 * id that no longer exists.
 */
class GuestCartSurvivesTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    private function addToCart(string $slug, int $count = 1)
    {
        return $this->keepCookies($this->postJson(
            route('store.cart.add-product', ['productSlug' => $slug]),
            ['product_count' => $count]
        ));
    }

    public function test_the_same_visitor_keeps_one_cart_across_requests(): void
    {
        $product = $this->makeProduct();

        $this->addToCart($product->slug)->assertOk();
        $this->addToCart($product->slug)->assertOk();

        $this->assertSame(
            1,
            Cart::count(),
            'Той самий відвідувач має мати один кошик, а не новий на кожен запит.'
        );
    }

    public function test_a_cart_filled_before_signing_in_is_not_lost(): void
    {
        $product = $this->makeProduct();
        $user = $this->author();

        $this->addToCart($product->slug)->assertOk();

        $this->keepCookies($this->post(route('auth.sign-in'), [
            'email' => $user->email,
            'password' => 'password',
        ]));

        $this->assertSame(
            1,
            Cart::whereNotNull('user_id')->count(),
            'Після входу кошик, зібраний гостем, має належати цьому акаунту.'
        );
    }

    public function test_a_promo_applied_before_signing_in_moves_with_the_guest_cart(): void
    {
        $product = $this->makeProduct();
        $user = $this->author();
        $promo = PromoCode::create([
            'code' => 'WELCOME-10',
            'discount' => 10,
            'discount_type' => PromoCode::TYPE_PERCENT,
            'discount_value' => 10,
            'is_active' => true,
            'all_products' => true,
            'minimum_order_amount' => 0,
        ]);

        $this->addToCart($product->slug)->assertOk();
        $this->postJson(route('store.cart.add-promo-code'), ['code' => 'welcome-10'])->assertOk();

        $this->keepCookies($this->post(route('auth.sign-in'), [
            'email' => $user->email,
            'password' => 'password',
        ]));

        $userCart = Cart::where('user_id', $user->id)->firstOrFail();
        $this->assertSame($promo->id, $userCart->promo_code_id);
        $this->assertSame(1, $userCart->products()->count());
    }
}
