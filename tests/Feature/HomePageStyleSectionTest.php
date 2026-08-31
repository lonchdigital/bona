<?php

namespace Tests\Feature;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Models\Category;
use App\Models\HomePageConfig;
use App\Models\ProductField;
use App\Models\Role;
use App\Models\SeoText;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class HomePageStyleSectionTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_admin_can_save_product_types_and_nested_categories_for_homepage_cards(): void
    {
        $config = $this->homePageConfig();
        $productType = $this->productType([
            'slug' => 'accessories',
            'name' => ['uk' => 'Аксесуари', 'ru' => 'Аксессуары'],
        ]);
        $category = Category::query()->create([
            'creator_id' => $this->author()->id,
            'product_type_id' => $productType->id,
            'name' => ['uk' => 'Дверні ручки', 'ru' => 'Дверные ручки'],
            'slug' => 'door-handles',
            'image_path' => null,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.home-page.edit'), [
                'selected_product_types' => $productType->id.',category:'.$category->id,
                'selected_products_id' => '',
                'selected_best_sales_products_id' => '',
                'selected_brands_id' => '',
                'seo_title' => ['uk' => '', 'ru' => ''],
                'seo_text' => ['uk' => '', 'ru' => ''],
                'style_section' => ['enabled' => 0],
            ])
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertSame(
            [(string) $productType->id, 'category:'.$category->id],
            json_decode($config->fresh()->product_types, true),
        );
    }

    public function test_admin_can_manage_style_section_inside_home_page_form(): void
    {
        Storage::fake(config('app.images_disk_default'));
        $config = $this->homePageConfig();

        $response = $this->actingAs($this->admin())
            ->from(route('admin.home-page.edit.page'))
            ->post(route('admin.home-page.edit'), [
                'selected_product_types' => '',
                'selected_products_id' => '',
                'selected_best_sales_products_id' => '',
                'selected_brands_id' => '',
                'seo_title' => ['uk' => '', 'ru' => ''],
                'seo_text' => ['uk' => '', 'ru' => ''],
                'style_section' => [
                    'enabled' => 1,
                    'kicker' => ['uk' => 'Підбір за стилем', 'ru' => 'Подбор по стилю'],
                    'title' => ['uk' => 'Підберемо двері до вашого інтерʼєру', 'ru' => 'Подберем двери к вашему интерьеру'],
                    'description' => ['uk' => 'Оберіть стиль', 'ru' => 'Выберите стиль'],
                    'cta_label' => ['uk' => 'Отримати добірку', 'ru' => 'Получить подборку'],
                    'cta_url' => '#dialog-call-consultation',
                    'items' => [[
                        'name' => ['uk' => 'Мінімалізм', 'ru' => 'Минимализм'],
                        'image' => UploadedFile::fake()->image('minimalism.jpg', 1200, 900),
                    ]],
                ],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.success', true)
            ->assertJsonPath('data.redirect_to', '');

        $section = $config->fresh()->style_section;

        $this->assertTrue($section['enabled']);
        $this->assertSame('Підбір за стилем', $section['kicker']['uk']);
        $this->assertSame('Мінімалізм', $section['items'][0]['name']['uk']);
        Storage::disk(config('app.images_disk_default'))->assertExists($section['items'][0]['image_path']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('data-home-style-selector', false)
            ->assertSee('Підберемо двері до вашого інтерʼєру')
            ->assertSee('Мінімалізм');
    }

    public function test_style_section_rejects_an_unsafe_cta_url(): void
    {
        $this->homePageConfig();

        $this->actingAs($this->admin())
            ->from(route('admin.home-page.edit.page'))
            ->post(route('admin.home-page.edit'), [
                'selected_product_types' => '',
                'selected_products_id' => '',
                'selected_best_sales_products_id' => '',
                'selected_brands_id' => '',
                'seo_title' => ['uk' => '', 'ru' => ''],
                'seo_text' => ['uk' => '', 'ru' => ''],
                'style_section' => [
                    'enabled' => 0,
                    'cta_url' => 'javascript:alert(1)',
                ],
            ])
            ->assertRedirect(route('admin.home-page.edit.page'))
            ->assertSessionHasErrors('style_section.cta_url');
    }

    public function test_empty_editor_payload_does_not_erase_existing_homepage_seo_text(): void
    {
        $this->homePageConfig();
        SeoText::query()->create([
            'page_type' => config('constants.HOMEPAGE_TYPE'),
            'language' => 'uk',
            'title' => 'Важливий заголовок',
            'content' => '<p>Важливий текст головної сторінки.</p>',
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.home-page.edit'), [
                'selected_product_types' => '',
                'selected_products_id' => '',
                'selected_best_sales_products_id' => '',
                'selected_brands_id' => '',
                'seo_title' => ['uk' => '', 'ru' => ''],
                'seo_text' => ['uk' => '<p><br></p>', 'ru' => ''],
                'style_section' => ['enabled' => 0],
            ])
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $seoText = SeoText::query()
            ->where('page_type', config('constants.HOMEPAGE_TYPE'))
            ->where('language', 'uk')
            ->firstOrFail();

        $this->assertSame('Важливий заголовок', $seoText->title);
        $this->assertSame('<p>Важливий текст головної сторінки.</p>', $seoText->content);
    }

    private function homePageConfig(): HomePageConfig
    {
        $author = User::factory()->create();
        $field = ProductField::query()->create([
            'creator_id' => $author->id,
            'field_name' => ['uk' => 'Тест', 'ru' => 'Тест'],
            'slug' => 'test-field',
            'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
        ]);

        return HomePageConfig::query()->create([
            'slider_title' => ['uk' => '', 'ru' => ''],
            'slider_logo_image_path' => '',
            'product_field_id' => $field->id,
            'product_types' => '[]',
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
