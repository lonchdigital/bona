<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\SeoText;
use App\Models\User;
use App\Services\ProductCategory\CategoryContentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class CategoryContentImporterTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_it_imports_category_copy_without_overwriting_manager_content(): void
    {
        $category = Category::query()->create([
            'slug' => 'zamok',
            'creator_id' => $this->author()->id,
            'product_type_id' => $this->productType(['slug' => 'aksessuar'])->id,
            'name' => ['uk' => 'Замок', 'ru' => 'Замок'],
            'meta_title' => ['uk' => '', 'ru' => 'Власний title'],
            'meta_description' => ['uk' => '', 'ru' => ''],
        ]);
        SeoText::query()->create([
            'page_type' => 'zamok',
            'language' => 'ru',
            'title' => 'Власний заголовок',
            'content' => '<p>Текст менеджера.</p>',
        ]);

        $importer = app(CategoryContentImporter::class);
        $path = database_path('content/categories/2026_09_22_accessory_categories.json');
        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(1, $first['categories']);
        $this->assertContains('mehanyzm', $first['missing']);
        $this->assertSame(0, $second['categories']);
        $this->assertSame('Власний title', $category->fresh()->getTranslation('meta_title', 'ru'));
        $this->assertStringContainsString('Дверні замки', $category->fresh()->getTranslation('meta_title', 'uk'));
        $this->assertSame(
            '<p>Текст менеджера.</p>',
            SeoText::query()->where(['page_type' => 'zamok', 'language' => 'ru'])->value('content'),
        );
        $this->assertStringContainsString(
            'магнітні',
            SeoText::query()->where(['page_type' => 'zamok', 'language' => 'uk'])->value('content'),
        );

        $this->seedCurrency();
        $product = $this->makeProduct(['product_type_id' => $category->product_type_id]);
        $product->categories()->attach($category);

        $this->get('/product-category/aksessuar/category/zamok')
            ->assertOk()
            ->assertSee('Дверні замки для міжкімнатних дверей')
            ->assertSee('магнітні моделі AGB');

        DB::table('roles')->insertOrIgnore([
            'id' => Role::ADMIN_ROLE_ID,
            'role' => 'Admin',
            'role_slug' => 'admin',
        ]);
        $admin = User::factory()->create(['role_id' => Role::ADMIN_ROLE_ID]);

        $this->actingAs($admin)
            ->get(route('admin.product-category.edit.page', [
                'productType' => $category->product_type_id,
                'productCategory' => $category->id,
            ]))
            ->assertOk()
            ->assertViewHas('seoData', fn (array $data) => str_contains(
                data_get($data, 'content.uk', ''),
                'магнітні моделі AGB',
            ));
    }
}
