<?php

namespace Tests\Feature;

use App\Models\CatalogMenuConfiguration;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class CatalogMenuTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_admin_can_configure_navigation_cards_and_columns(): void
    {
        $admin = $this->admin();
        $productType = $this->productType(['sort_order' => 1]);
        $category = $this->category($productType->id, 'modern-doors', 'Сучасні двері');

        $this->actingAs($admin)
            ->post(route('admin.catalog-menu.update'), [
                'configurations' => [
                    $productType->id => [
                        'is_visible' => 1,
                        'sort_order' => 4,
                        'show_in_header' => 1,
                        'header_order' => 2,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.catalog-menu.page'));

        $this->actingAs($admin)
            ->post(route('admin.catalog-menu.edit', $productType), [
                'cards' => [
                    $category->id => ['enabled' => 1, 'sort_order' => 3],
                ],
                'columns' => [[
                    'title' => ['uk' => 'Виробник', 'ru' => 'Производитель'],
                    'sort_order' => 1,
                    'items' => [[
                        'category_id' => '',
                        'label' => ['uk' => 'Barausse', 'ru' => 'Barausse'],
                        'url' => ['uk' => '/brands/barausse', 'ru' => '/ru/brands/barausse'],
                        'sort_order' => 0,
                    ]],
                ]],
            ])
            ->assertRedirect(route('admin.catalog-menu.edit.page', $productType));

        $configuration = CatalogMenuConfiguration::query()
            ->whereBelongsTo($productType)
            ->firstOrFail();

        $this->assertTrue($configuration->is_visible);
        $this->assertTrue($configuration->show_in_header);
        $this->assertSame(4, $configuration->sort_order);
        $this->assertSame([$category->id], $configuration->cards);
        $this->assertSame('Виробник', $configuration->columns[0]['title']['uk']);
        $this->assertSame('Barausse', $configuration->columns[0]['items'][0]['label']['uk']);
    }

    public function test_storefront_renders_only_visible_configured_menu_types(): void
    {
        // A mega-menu entry is controlled by its own configuration. Its old
        // product-type sort order must not silently remove it from the header.
        $visibleType = $this->productType(['slug' => 'visible-doors', 'name' => 'Видимі двері', 'sort_order' => 0]);
        $hiddenType = $this->productType(['slug' => 'hidden-doors', 'name' => 'Прихований пункт', 'sort_order' => 2]);
        $category = $this->category($visibleType->id, 'modern-doors', 'Сучасні двері');

        CatalogMenuConfiguration::query()->create([
            'product_type_id' => $visibleType->id,
            'is_visible' => true,
            'sort_order' => 0,
            'show_in_header' => true,
            'header_order' => 0,
            'cards' => [$category->id],
            'columns' => [[
                'title' => ['uk' => 'Виробник', 'ru' => 'Производитель'],
                'sort_order' => 0,
                'items' => [[
                    'category_id' => null,
                    'label' => ['uk' => 'Barausse', 'ru' => 'Barausse'],
                    'url' => ['uk' => '/brands/barausse', 'ru' => '/ru/brands/barausse'],
                    'sort_order' => 0,
                ]],
            ]],
        ]);

        CatalogMenuConfiguration::query()->create([
            'product_type_id' => $hiddenType->id,
            'is_visible' => false,
            'sort_order' => 1,
            'show_in_header' => false,
            'header_order' => 1,
            'cards' => [],
            'columns' => [],
        ]);

        $response = $this->get(route('store.home'));

        $response
            ->assertOk()
            ->assertSee('data-mega-tab="'.$visibleType->id.'"', false)
            ->assertDontSee('data-mega-tab="'.$hiddenType->id.'"', false)
            ->assertSee('Сучасні двері')
            ->assertSee('Виробник')
            ->assertSee('Barausse')
            ->assertSee('class="bona-mainnav__direct"', false);
    }

    public function test_custom_menu_item_requires_both_label_and_url(): void
    {
        $productType = $this->productType(['sort_order' => 1]);

        $this->actingAs($this->admin())
            ->from(route('admin.catalog-menu.edit.page', $productType))
            ->post(route('admin.catalog-menu.edit', $productType), [
                'cards' => [],
                'columns' => [[
                    'title' => ['uk' => 'Виробник', 'ru' => 'Производитель'],
                    'sort_order' => 0,
                    'items' => [[
                        'category_id' => '',
                        'label' => ['uk' => 'Barausse', 'ru' => ''],
                        'url' => ['uk' => '', 'ru' => ''],
                        'sort_order' => 0,
                    ]],
                ]],
            ])
            ->assertRedirect(route('admin.catalog-menu.edit.page', $productType))
            ->assertSessionHasErrors('columns.0.items.0.label.uk');
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

    private function category(int $productTypeId, string $slug, string $name): Category
    {
        return Category::query()->create([
            'creator_id' => $this->author()->id,
            'product_type_id' => $productTypeId,
            'name' => ['uk' => $name, 'ru' => $name],
            'slug' => $slug,
            'image_path' => 'test/category.webp',
            'meta_title' => ['uk' => $name, 'ru' => $name],
            'meta_description' => ['uk' => $name, 'ru' => $name],
            'meta_keywords' => ['uk' => $name, 'ru' => $name],
        ]);
    }
}
