<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SearchQuery;
use App\Models\ServicesPageSections;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class StorefrontSearchTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_it_returns_grouped_limited_products_and_services(): void
    {
        foreach (range(1, 4) as $number) {
            $this->makeProduct([
                'name' => ['uk' => 'Тест двері '.$number, 'ru' => 'Тест дверь '.$number],
                'sku' => 'SEARCH-'.$number,
            ]);
        }

        foreach (range(1, 3) as $number) {
            ServicesPageSections::create([
                'slug' => 'test-posluha-'.$number,
                'title' => ['uk' => 'Тест послуга '.$number, 'ru' => 'Тест услуга '.$number],
                'description' => ['uk' => 'Опис послуги', 'ru' => 'Описание услуги'],
                'button_text' => ['uk' => 'Замовити', 'ru' => 'Заказать'],
                'button_url' => '#',
                'section_image_path' => 'test/service-'.$number.'.webp',
            ]);
        }

        $response = $this->postJson(route('store.product.search'), ['query' => 'Тест']);

        $response
            ->assertOk()
            ->assertJsonCount(4, 'data.products')
            ->assertJsonCount(2, 'data.services')
            ->assertJsonPath('data.totals.products', 4)
            ->assertJsonPath('data.totals.services', 3)
            ->assertJsonPath('data.services.0.link', '/services/test-posluha-1');
    }

    public function test_it_searches_the_same_legacy_products_that_the_catalog_displays(): void
    {
        $this->makeProduct([
            'name' => ['uk' => 'LegacyVisible двері', 'ru' => 'LegacyVisible дверь'],
            'sku' => 'LEGACY-VISIBLE',
            'is_active' => false,
        ]);

        $this->postJson(route('store.product.search'), ['query' => 'LegacyVisible'])
            ->assertOk()
            ->assertJsonPath('data.products.0.sku', 'LEGACY-VISIBLE');
    }

    public function test_it_rejects_queries_shorter_than_three_characters(): void
    {
        $this->postJson(route('store.product.search'), ['query' => 'те'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('query');
    }

    public function test_it_matches_ukrainian_word_endings_and_aggregates_search_analytics(): void
    {
        $panelType = $this->productType([
            'slug' => 'wall-panels',
            'name' => ['uk' => 'Стінові панелі', 'ru' => 'Стеновые панели'],
        ]);
        $this->makeProduct([
            'product_type_id' => $panelType->id,
            'name' => ['uk' => 'Стінова панель Wood', 'ru' => 'Стеновая панель Wood'],
            'sku' => 'PANEL-WOOD',
        ]);

        $this->postJson(route('store.product.search'), ['query' => 'панелі'])
            ->assertOk()
            ->assertJsonPath('data.products.0.sku', 'PANEL-WOOD')
            ->assertJsonPath('data.totals.products', 1)
            ->assertJsonPath('data.suggestions.0.title', 'Стінові панелі');

        $this->postJson(route('store.product.search'), ['query' => 'ПАНЕЛІ'])->assertOk();

        $search = SearchQuery::where('normalized_query', 'панелі')->firstOrFail();
        $this->assertSame(2, $search->search_count);
        $this->assertSame(1, $search->results_count);
    }

    public function test_admin_can_review_recorded_search_queries(): void
    {
        SearchQuery::create([
            'query' => 'ручки',
            'normalized_query' => 'ручки',
            'locale' => 'uk',
            'search_count' => 7,
            'results_count' => 12,
            'first_searched_at' => now()->subDay(),
            'last_searched_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.search-query.list.page'))
            ->assertOk()
            ->assertSee('ручки')
            ->assertSee('7');
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
