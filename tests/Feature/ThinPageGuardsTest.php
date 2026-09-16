<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Services\Product\ProductService;
use App\Services\Sitemap\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ThinPageGuardsTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_zero_price_titles_say_price_on_request_in_both_languages(): void
    {
        $product = $this->makeProduct(['price' => 0, 'name' => ['uk' => 'Loft', 'ru' => 'Loft']]);
        $service = app(ProductService::class);

        app()->setLocale('uk');
        $this->assertSame(
            'Розсувні двері Loft купити в Одесі (ціна за запитом) в інтернет магазині | Bona-Doors',
            $service->replaceTagsWithData('Розсувні двері %title% купити в Одесі по ціні %price% грн. в інтернет магазині | Bona-Doors', $product),
        );
        $this->assertSame(
            'Купити Loft в Одесі (ціна за запитом) в інтернет магазині',
            $service->replaceTagsWithData('Купити %title% в Одесі за найкращою ціною %price% грн. в інтернет магазині', $product),
        );

        app()->setLocale('ru');
        $this->assertSame(
            'Купить Loft в Одессе (цена по запросу) в интернет магазине',
            $service->replaceTagsWithData('Купить %title% в Одессе по лучшей цене %price% грн. в интернет магазине', $product),
        );

        $priced = $this->makeProduct(['price' => 3620]);
        $this->assertStringContainsString('3620', $service->replaceTagsWithData('по цене %price% грн.', $priced));
    }

    public function test_empty_category_stays_reachable_but_is_noindexed_and_left_out_of_the_sitemap(): void
    {
        $this->seedCurrency();
        $type = $this->productType(['slug' => 'aksessuar']);
        $empty = $this->category($type->id, 'plintus-estet');
        $filled = $this->category($type->id, 'zamok');
        $this->makeProduct(['product_type_id' => $type->id])->categories()->attach($filled->id);

        $this->get('/product-category/aksessuar/category/plintus-estet')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, follow')
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertDontSee('content="index, follow"', false);

        $this->get('/product-category/aksessuar/category/zamok')
            ->assertOk()
            ->assertHeaderMissing('X-Robots-Tag');

        $this->get('/product-category/aksessuar/category/zamok?page=99')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, follow');

        $sitemap = app(SitemapService::class)->buildSitemap()->render();
        $this->assertStringContainsString('/category/zamok', $sitemap);
        $this->assertStringNotContainsString('/category/plintus-estet', $sitemap);
        $this->assertNotNull($empty->fresh());
    }

    public function test_category_under_a_foreign_type_redirects_permanently_instead_of_404(): void
    {
        $this->seedCurrency();
        $accessories = $this->productType(['slug' => 'aksessuar']);
        $this->productType(['slug' => 'interior-doors', 'name' => 'Міжкімнатні двері']);
        $lock = $this->category($accessories->id, 'zamok');
        $this->makeProduct(['product_type_id' => $accessories->id])->categories()->attach($lock->id);

        $this->get('/product-category/interior-doors/category/zamok')
            ->assertStatus(301)
            ->assertRedirect(url('/product-category/aksessuar/category/zamok'));

        $this->get('/ru/product-category/interior-doors/category/zamok')
            ->assertStatus(301)
            ->assertRedirect(url('/ru/product-category/aksessuar/category/zamok'));
    }

    public function test_ukraine_typo_is_fixed_in_stored_meta_without_touching_other_text(): void
    {
        $product = $this->makeProduct([
            'meta_title' => ['uk' => 'Придбати двері Мілан в Украіні', 'ru' => 'Купить дверь Милан в Украине'],
        ]);

        (require database_path('migrations/2026_09_16_120000_fix_ukraine_typo_in_seo_meta.php'))->up();

        $product->refresh();
        $this->assertSame('Придбати двері Мілан в Україні', $product->getTranslation('meta_title', 'uk'));
        $this->assertSame('Купить дверь Милан в Украине', $product->getTranslation('meta_title', 'ru'));
    }

    private function category(int $productTypeId, string $slug): Category
    {
        return Category::query()->create([
            'creator_id' => $this->author()->id,
            'product_type_id' => $productTypeId,
            'name' => ['uk' => $slug, 'ru' => $slug],
            'slug' => $slug,
            'image_path' => 'test/category.webp',
            'meta_title' => ['uk' => '', 'ru' => ''],
            'meta_description' => ['uk' => '', 'ru' => ''],
            'meta_keywords' => ['uk' => '', 'ru' => ''],
        ]);
    }
}
