<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\User;
use App\Services\Pricing\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_percentage_discount_can_target_products_and_limit_discounted_quantity(): void
    {
        $eligible = $this->makeProduct(['price' => 1000]);
        $other = $this->makeProduct(['price' => 500]);
        $promoCode = PromoCode::create([
            'code' => 'TWO-DOORS',
            'discount' => 50,
            'discount_type' => PromoCode::TYPE_PERCENT,
            'discount_value' => 50,
            'is_active' => true,
            'all_products' => false,
            'max_discounted_items' => 2,
            'minimum_order_amount' => 2000,
        ]);
        $promoCode->products()->sync([$eligible->id]);

        $cart = Cart::create(['token' => 'targeted-promo', 'promo_code_id' => $promoCode->id]);
        $cart->products()->attach($eligible->id, ['count' => 3, 'price' => 1000]);
        $cart->products()->attach($other->id, ['count' => 2, 'price' => 500]);

        $totals = app(PricingService::class)->forCart($cart);

        $this->assertSame(4000.0, $totals['products']);
        $this->assertSame(1000.0, $totals['discount']);
        $this->assertSame(3000.0, $totals['total']);
    }

    public function test_cart_accepts_codes_case_insensitively_and_can_remove_them(): void
    {
        $product = $this->makeProduct(['price' => 1500]);
        PromoCode::create([
            'code' => 'BONA-150',
            'discount' => 0,
            'discount_type' => PromoCode::TYPE_FIXED,
            'discount_value' => 150,
            'is_active' => true,
            'all_products' => true,
            'minimum_order_amount' => 0,
        ]);

        $this->keepCookies($this->postJson(route('store.cart.add-product', $product->slug), ['product_count' => 1]))->assertOk();

        $this->postJson(route('store.cart.add-promo-code'), ['code' => '  bona-150  '])
            ->assertOk()
            ->assertJsonPath('data.summary.discount', 150)
            ->assertJsonPath('data.promo_code.code', 'BONA-150');

        $this->deleteJson(route('store.cart.remove-promo-code'))
            ->assertOk()
            ->assertJsonPath('data.summary.discount', 0)
            ->assertJsonPath('data.promo_code', null);
    }

    public function test_expired_code_is_rejected_without_being_attached(): void
    {
        $product = $this->makeProduct();
        PromoCode::create([
            'code' => 'TOO-LATE',
            'discount' => 10,
            'discount_type' => PromoCode::TYPE_PERCENT,
            'discount_value' => 10,
            'is_active' => true,
            'all_products' => true,
            'minimum_order_amount' => 0,
            'expires_at' => now()->subMinute(),
        ]);

        $this->keepCookies($this->postJson(route('store.cart.add-product', $product->slug), ['product_count' => 1]))->assertOk();
        $this->postJson(route('store.cart.add-promo-code'), ['code' => 'TOO-LATE'])
            ->assertStatus(422)
            ->assertJsonPath('data.success', false);

        $this->assertNull(Cart::first()->promo_code_id);
    }

    public function test_applied_code_is_removed_when_cart_no_longer_meets_its_conditions(): void
    {
        $product = $this->makeProduct(['price' => 1000]);
        PromoCode::create([
            'code' => 'MINIMUM-1500',
            'discount' => 10,
            'discount_type' => PromoCode::TYPE_PERCENT,
            'discount_value' => 10,
            'is_active' => true,
            'all_products' => true,
            'minimum_order_amount' => 1500,
        ]);

        $this->keepCookies($this->postJson(route('store.cart.add-product', $product->slug), ['product_count' => 2]))->assertOk();
        $this->postJson(route('store.cart.add-promo-code'), ['code' => 'MINIMUM-1500'])
            ->assertOk()
            ->assertJsonPath('data.summary.discount', 200);

        $this->postJson(route('store.cart.change-product-count', $product->slug), ['product_count' => 1])
            ->assertOk()
            ->assertJsonPath('data.summary.discount', 0)
            ->assertJsonPath('data.promo_code', null);

        $this->assertNull(Cart::first()->fresh()->promo_code_id);
    }

    public function test_campaign_status_distinguishes_scheduled_expired_and_exhausted_codes(): void
    {
        $base = [
            'discount' => 10,
            'discount_type' => PromoCode::TYPE_PERCENT,
            'discount_value' => 10,
            'is_active' => true,
            'all_products' => true,
            'minimum_order_amount' => 0,
        ];

        $scheduled = PromoCode::create($base + ['code' => 'SCHEDULED', 'starts_at' => now()->addHour()]);
        $expired = PromoCode::create($base + ['code' => 'EXPIRED', 'expires_at' => now()->subHour()]);
        $exhausted = PromoCode::create($base + ['code' => 'EXHAUSTED', 'usage_limit' => 2, 'usage_count' => 2]);

        $this->assertSame('scheduled', $scheduled->statusKey());
        $this->assertSame('expired', $expired->statusKey());
        $this->assertSame('exhausted', $exhausted->statusKey());
    }

    public function test_admin_can_create_a_reusable_fixed_discount_for_selected_products(): void
    {
        $product = $this->makeProduct();

        $this->actingAs($this->admin())
            ->post(route('admin.promo-code.create'), [
                'code' => 'project-500',
                'discount_type' => PromoCode::TYPE_FIXED,
                'discount_value' => 500,
                'is_active' => 1,
                'minimum_order_amount' => 3000,
                'all_products' => 0,
                'product_ids' => [$product->id],
                'usage_limit' => 20,
                'max_discounted_items' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $promoCode = PromoCode::where('code', 'PROJECT-500')->firstOrFail();
        $this->assertSame(PromoCode::TYPE_FIXED, $promoCode->discount_type);
        $this->assertSame(500.0, $promoCode->effectiveDiscountValue());
        $this->assertSame(20, $promoCode->usage_limit);
        $this->assertSame([$product->id], $promoCode->products()->pluck('products.id')->all());
    }

    public function test_editing_a_campaign_keeps_its_usage_count_and_closes_a_reached_limit(): void
    {
        $promoCode = PromoCode::create([
            'code' => 'LIMITED',
            'discount' => 10,
            'discount_type' => PromoCode::TYPE_PERCENT,
            'discount_value' => 10,
            'is_active' => true,
            'all_products' => true,
            'minimum_order_amount' => 0,
            'usage_limit' => 10,
            'usage_count' => 3,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.promo-code.edit', $promoCode), [
                'code' => 'limited',
                'discount_type' => PromoCode::TYPE_PERCENT,
                'discount_value' => 12,
                'is_active' => 1,
                'minimum_order_amount' => 0,
                'all_products' => 1,
                'usage_limit' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $promoCode->refresh();

        $this->assertSame(3, $promoCode->usage_count);
        $this->assertSame(3, $promoCode->usage_limit);
        $this->assertTrue($promoCode->is_used);
    }

    private function admin(): User
    {
        DB::table('roles')->insertOrIgnore([
            'id' => Role::ADMIN_ROLE_ID,
            'role' => 'Admin',
            'role_slug' => 'admin',
        ]);

        $admin = User::factory()->create();
        $admin->update(['role_id' => Role::ADMIN_ROLE_ID]);

        return $admin;
    }
}
