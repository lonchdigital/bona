<?php

namespace Tests\Support;

use App\Models\Country;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use App\Services\Cart\GuestCartToken;
use App\Services\WishList\GuestWishListToken;
use Illuminate\Support\Facades\DB;

/**
 * The smallest shop a test can stand in.
 *
 * Almost every column these tables need is NOT NULL with no default, and the
 * project has no factories for them, so this fills them in once rather than in
 * every test. Anything a test actually cares about it passes in itself.
 */
trait MakesShopData
{
    private ?User $shopAuthor = null;

    protected function author(): User
    {
        return $this->shopAuthor ??= User::factory()->create();
    }

    protected function country(): Country
    {
        return Country::firstOrCreate(
            ['code' => 'UA'],
            [
                'name' => ['uk' => 'Україна', 'ru' => 'Украина'],
                'image_path' => 'test/ua.svg',
                'creator_id' => $this->author()->id,
            ]
        );
    }

    protected function productType(array $attributes = []): ProductType
    {
        return ProductType::firstOrCreate(
            ['slug' => $attributes['slug'] ?? 'test-doors'],
            array_merge([
                'name' => 'Тестові двері',
                'creator_id' => $this->author()->id,
                'image_path' => 'test/type.webp',
                'meta_title' => ['uk' => 'Тест', 'ru' => 'Тест'],
                'meta_description' => ['uk' => 'Тест', 'ru' => 'Тест'],
                'meta_keywords' => ['uk' => 'тест', 'ru' => 'тест'],
            ], $attributes)
        );
    }

    protected function makeProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'slug' => 'test-door-'.uniqid(),
            'creator_id' => $this->author()->id,
            'product_type_id' => $this->productType()->id,
            'country_id' => $this->country()->id,
            'name' => ['uk' => 'Тестові двері', 'ru' => 'Тестовая дверь'],
            'price' => 5000,
            'purchase_price_in_currency' => 3000,
            'availability_status_id' => 1,
            'is_active' => true,
        ], $attributes));
    }

    /**
     * Reference rows a seeder normally provides and RefreshDatabase removes.
     */
    protected function seedCurrency(): void
    {
        DB::table('currencies')->insertOrIgnore([
            'id' => 1,
            'creator_id' => $this->author()->id,
            'name' => json_encode(['uk' => 'Гривня', 'ru' => 'Гривна'], JSON_UNESCAPED_UNICODE),
            'name_short' => json_encode(['uk' => 'грн', 'ru' => 'грн'], JSON_UNESCAPED_UNICODE),
            'code' => 'UAH',
            'is_base' => 1,
            'rate' => 1,
        ]);
    }

    /**
     * A visitor with nothing carried over from the one before.
     */
    protected function asNewVisitor(): static
    {
        $this->flushSession();
        $this->app['request']->cookies->replace([]);

        // The client keeps sending whatever it was told to keep, so a genuinely
        // new visitor has to have that cleared as well.
        $this->defaultCookies = [];
        $this->unencryptedCookies = [];

        /*
         * These remember the token they issued during a request, because a
         * queued cookie cannot be read back from the request that set it. In
         * production every request gets a fresh container; in a test the same
         * one is reused, so a new visitor has to be given fresh instances.
         */
        $this->app->forgetInstance(GuestWishListToken::class);
        $this->app->forgetInstance(GuestCartToken::class);

        return $this;
    }
}
