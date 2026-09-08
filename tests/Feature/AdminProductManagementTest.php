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

    public function test_optional_filter_field_does_not_block_updating_an_unrelated_accessory(): void
    {
        $this->seedCurrency();

        $productType = $this->productType([
            'slug' => 'accessories-with-handle-filter',
            'name' => 'Аксесуари',
        ]);
        $manufacturerField = ProductField::create([
            'creator_id' => $this->author()->id,
            'field_name' => [
                'uk' => 'Виробник дверних ручок',
                'ru' => 'Производитель дверных ручек',
            ],
            'slug' => 'handle-manufacturer-'.$productType->id,
            'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
            'is_mandatory' => false,
        ]);
        $productType->fields()->attach($manufacturerField->id, [
            'show_as_filter' => true,
            'show_on_main_filters_list' => true,
            'filter_name' => json_encode([
                'uk' => 'Виробник дверних ручок',
                'ru' => 'Производитель дверных ручек',
            ], JSON_UNESCAPED_UNICODE),
        ]);
        $manufacturer = ProductFieldOption::create([
            'product_field_id' => $manufacturerField->id,
            'name' => ['uk' => 'Тестовий бренд', 'ru' => 'Тестовый бренд'],
            'slug' => 'test-handle-brand-'.$productType->id,
        ]);

        $handle = $this->makeProduct([
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Дверна ручка', 'ru' => 'Дверная ручка'],
            'custom_fields' => [(string) $manufacturerField->id => (string) $manufacturer->id],
        ]);
        $moulding = $this->makeProduct([
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Комплект коробу', 'ru' => 'Комплект короба'],
            'price' => 1000,
            'custom_fields' => null,
        ]);

        $response = $this->actingAs($this->admin())->post(route('admin.product.edit', [
            'productType' => $productType,
            'product' => $moulding,
        ]), [
            'name' => $moulding->getTranslations('name'),
            'slug' => $moulding->slug,
            'created_at' => $moulding->created_at->format('Y-m-d H:i:s'),
            'availability_status_id' => 1,
            'price' => 1250,
            'currency_id' => 1,
            'product_short_text' => ['uk' => '', 'ru' => ''],
            'product_text' => ['uk' => '', 'ru' => ''],
            'seo_title' => ['uk' => '', 'ru' => ''],
            'seo_text' => ['uk' => '', 'ru' => ''],
            'custom_field' => [
                $manufacturerField->id => [
                    'field_id' => $manufacturerField->id,
                    'value' => '',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.success', true)
            ->assertJsonPath('data.redirect_to', '');

        $this->assertSame(1250.0, (float) $moulding->fresh()->price);
        $this->assertSame([], $moulding->fresh()->custom_fields);
        $this->assertSame(
            (string) $manufacturer->id,
            $handle->fresh()->custom_fields[(string) $manufacturerField->id]
        );
        $this->assertTrue($productType->fields()->whereKey($manufacturerField->id)->exists());
    }

    public function test_existing_handle_manufacturer_field_is_made_optional_without_removing_its_filter(): void
    {
        $productType = $this->productType([
            'slug' => 'accessories-data-fix',
            'name' => 'Аксесуари',
        ]);
        $manufacturerField = ProductField::create([
            'creator_id' => $this->author()->id,
            'field_name' => [
                'uk' => 'Виробник дверних ручок',
                'ru' => 'Производитель дверных ручек',
            ],
            'slug' => 'handle-manufacturer-data-fix',
            'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
            'is_mandatory' => true,
        ]);
        $productType->fields()->attach($manufacturerField->id, [
            'show_as_filter' => true,
            'show_on_main_filters_list' => true,
        ]);

        $migration = require database_path(
            'migrations/2026_09_08_110000_make_door_handle_manufacturer_field_optional.php'
        );
        $migration->up();

        $this->assertFalse((bool) $manufacturerField->fresh()->is_mandatory);
        $this->assertDatabaseHas('product_field_product_type', [
            'product_type_id' => $productType->id,
            'product_field_id' => $manufacturerField->id,
            'show_as_filter' => true,
            'show_on_main_filters_list' => true,
        ]);
    }

    public function test_a_field_explicitly_marked_as_mandatory_still_blocks_an_empty_value(): void
    {
        $this->seedCurrency();

        $productType = $this->productType(['slug' => 'products-with-mandatory-field']);
        $mandatoryField = ProductField::create([
            'creator_id' => $this->author()->id,
            'field_name' => ['uk' => 'Обов’язкова ознака', 'ru' => 'Обязательный признак'],
            'slug' => 'mandatory-field-'.$productType->id,
            'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
            'is_mandatory' => true,
        ]);
        $productType->fields()->attach($mandatoryField->id);
        $product = $this->makeProduct(['product_type_id' => $productType->id]);

        $this->actingAs($this->admin())->postJson(route('admin.product.edit', [
            'productType' => $productType,
            'product' => $product,
        ]), [
            'name' => $product->getTranslations('name'),
            'slug' => $product->slug,
            'created_at' => $product->created_at->format('Y-m-d H:i:s'),
            'availability_status_id' => 1,
            'price' => 5000,
            'currency_id' => 1,
            'product_short_text' => ['uk' => '', 'ru' => ''],
            'product_text' => ['uk' => '', 'ru' => ''],
            'seo_title' => ['uk' => '', 'ru' => ''],
            'seo_text' => ['uk' => '', 'ru' => ''],
            'custom_field' => [
                $mandatoryField->id => [
                    'field_id' => $mandatoryField->id,
                    'value' => '',
                ],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('custom_field.'.$mandatoryField->id.'.value');
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
