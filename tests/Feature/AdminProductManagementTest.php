<?php

namespace Tests\Feature;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Models\Brand;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Models\Role;
use App\Models\User;
use App\Services\Product\DTO\FilterProductDTO;
use App\Services\Product\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_product_list_is_an_open_workspace_with_thumbnails_styles_sorting_and_thirty_rows_by_default(): void
    {
        $productType = $this->productType([
            'slug' => 'interior-doors-admin',
            'name' => 'Міжкімнатні двері',
            'has_brand' => true,
        ]);
        [$styleField, $classicStyle] = $this->styleField($productType);
        $brand = $this->brand('ArtPorte');

        foreach (range(1, 31) as $position) {
            $this->makeProduct([
                'product_type_id' => $productType->id,
                'brand_id' => $brand->id,
                'name' => ['uk' => 'Двері '.$position, 'ru' => 'Двери '.$position],
                'sku' => 'DOOR-'.$position,
                'sort_order' => $position,
                'custom_fields' => [(string) $styleField->id => (string) $classicStyle->id],
                'preview_image_path' => 'test/door-'.$position.'.webp',
                'main_image_path' => 'test/door-'.$position.'.webp',
            ]);
        }

        $response = $this->actingAs($this->admin())
            ->get(route('admin.product.list.page', $productType));

        $response->assertOk()
            ->assertSee('data-product-filters', false)
            ->assertSee('Назва або артикул')
            ->assertSee('Записів на сторінці')
            ->assertSee('<option value="30" selected>30</option>', false)
            ->assertSee('Фото')
            ->assertSee('Стиль')
            ->assertSee('Класика')
            ->assertSee('ArtPorte')
            ->assertSee('data-product-sortable', false)
            ->assertSee('draggable="true"', false)
            ->assertSee('sort=name', false)
            ->assertSee('sort=created_at', false)
            ->assertDontSee('<th>Автор</th>', false);

        $this->assertSame(30, substr_count($response->getContent(), 'data-product-row data-product-id='));
    }

    public function test_admin_can_filter_by_style_and_brand_and_sort_by_localized_name(): void
    {
        $productType = $this->productType([
            'slug' => 'filterable-admin-doors',
            'name' => 'Двері з фільтрами',
            'has_brand' => true,
        ]);
        [$styleField, $classicStyle, $modernStyle] = $this->styleField($productType, true);
        $targetBrand = $this->brand('Bona Filter Brand');
        $otherBrand = $this->brand('Bona Other Brand');

        $this->makeProduct([
            'product_type_id' => $productType->id,
            'brand_id' => $targetBrand->id,
            'name' => ['uk' => 'Альфа двері', 'ru' => 'Альфа двери'],
            'sku' => 'ALPHA',
            'custom_fields' => [(string) $styleField->id => (string) $classicStyle->id],
            'sort_order' => 2,
        ]);
        $this->makeProduct([
            'product_type_id' => $productType->id,
            'brand_id' => $targetBrand->id,
            'name' => ['uk' => 'Бета двері', 'ru' => 'Бета двери'],
            'sku' => 'BETA',
            'custom_fields' => [(string) $styleField->id => (string) $classicStyle->id],
            'sort_order' => 1,
        ]);
        $this->makeProduct([
            'product_type_id' => $productType->id,
            'brand_id' => $otherBrand->id,
            'name' => ['uk' => 'Модерн двері', 'ru' => 'Модерн двери'],
            'sku' => 'MODERN',
            'custom_fields' => [(string) $styleField->id => (string) $modernStyle->id],
            'sort_order' => 3,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.product.list.page', [
            'productType' => $productType->id,
            'brand_id' => $targetBrand->id,
            'style_option_id' => $classicStyle->id,
            'per_page' => 50,
            'sort' => 'name',
            'direction' => 'asc',
        ]));

        $response->assertOk()
            ->assertSee('Альфа двері')
            ->assertSee('Бета двері')
            ->assertDontSee('Модерн двері')
            ->assertSeeInOrder(['Альфа двері', 'Бета двері'])
            ->assertSee('<option value="50" selected>50</option>', false)
            ->assertSee('Ручний порядок');
    }

    public function test_reordering_a_filtered_subset_changes_the_default_storefront_order_without_moving_hidden_products(): void
    {
        $productType = $this->productType(['slug' => 'reorderable-doors']);
        $first = $this->makeProduct([
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Перші двері', 'ru' => 'Первые двери'],
            'sort_order' => 1,
        ]);
        $hidden = $this->makeProduct([
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Приховані між ними', 'ru' => 'Скрытые между ними'],
            'sort_order' => 2,
        ]);
        $third = $this->makeProduct([
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Треті двері', 'ru' => 'Третьи двери'],
            'sort_order' => 3,
        ]);

        $this->actingAs($this->admin())
            ->postJson(route('admin.product.reorder', $productType), [
                'product_ids' => [$third->id, $first->id],
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Порядок товарів збережено.',
            ]);

        $this->assertDatabaseHas('products', ['id' => $third->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('products', ['id' => $hidden->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('products', ['id' => $first->id, 'sort_order' => 3]);

        $storefrontProducts = app(ProductService::class)
            ->getProductsByTypePaginated($productType, new FilterProductDTO([]), 18, 1);

        $this->assertSame([$third->id, $hidden->id, $first->id], $storefrontProducts->pluck('id')->all());
    }

    public function test_reorder_rejects_a_product_from_another_product_type(): void
    {
        $productType = $this->productType(['slug' => 'secured-reorder']);
        $first = $this->makeProduct(['product_type_id' => $productType->id, 'sort_order' => 1]);
        $second = $this->makeProduct(['product_type_id' => $productType->id, 'sort_order' => 2]);
        $foreignType = $this->productType(['slug' => 'foreign-reorder']);
        $foreign = $this->makeProduct(['product_type_id' => $foreignType->id, 'sort_order' => 1]);

        $this->actingAs($this->admin())
            ->postJson(route('admin.product.reorder', $productType), [
                'product_ids' => [$second->id, $foreign->id],
            ])
            ->assertUnprocessable()
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('products', ['id' => $first->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('products', ['id' => $second->id, 'sort_order' => 2]);
    }

    private function styleField($productType, bool $withModern = false): array
    {
        $field = ProductField::create([
            'creator_id' => $this->author()->id,
            'field_name' => ['uk' => 'Стиль', 'ru' => 'Стиль'],
            'slug' => 'style-'.$productType->id,
            'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
        ]);
        $productType->fields()->attach($field->id, [
            'show_as_filter' => true,
            'show_on_main_filters_list' => true,
            'filter_name' => json_encode(['uk' => 'Стиль', 'ru' => 'Стиль'], JSON_UNESCAPED_UNICODE),
        ]);
        $classic = ProductFieldOption::create([
            'product_field_id' => $field->id,
            'name' => ['uk' => 'Класика', 'ru' => 'Классика'],
            'slug' => 'classic-'.$productType->id,
        ]);

        if (! $withModern) {
            return [$field, $classic];
        }

        $modern = ProductFieldOption::create([
            'product_field_id' => $field->id,
            'name' => ['uk' => 'Модерн', 'ru' => 'Модерн'],
            'slug' => 'modern-'.$productType->id,
        ]);

        return [$field, $classic, $modern];
    }

    private function brand(string $name): Brand
    {
        return Brand::create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => $name, 'ru' => $name],
            'slug' => str($name)->slug().'-'.uniqid(),
            'description' => ['uk' => $name, 'ru' => $name],
        ]);
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
