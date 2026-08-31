<?php

namespace Tests\Feature;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Models\Category;
use App\Models\HomePageBestSalesProducts;
use App\Models\HomePageConfig;
use App\Models\HomePageNewProducts;
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

    public function test_homepage_editor_receives_existing_seo_copy_for_each_language(): void
    {
        $this->homePageConfig();

        SeoText::query()->insert([
            [
                'page_type' => config('constants.HOMEPAGE_TYPE'),
                'language' => 'uk',
                'title' => 'SEO-заголовок українською',
                'content' => '<p>SEO-текст українською для редактора.</p>',
            ],
            [
                'page_type' => config('constants.HOMEPAGE_TYPE'),
                'language' => 'ru',
                'title' => 'SEO-заголовок російською',
                'content' => '<p>SEO-текст російською для редактора.</p>',
            ],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.home-page.edit.page'))
            ->assertOk()
            ->assertViewHas('seoText', function (array $seoText): bool {
                return $seoText['title']['uk'] === 'SEO-заголовок українською'
                    && $seoText['title']['ru'] === 'SEO-заголовок російською'
                    && $seoText['content']['uk'] === '<p>SEO-текст українською для редактора.</p>'
                    && $seoText['content']['ru'] === '<p>SEO-текст російською для редактора.</p>';
            })
            ->assertSee(':seo-title=', false)
            ->assertSee(':seo-text=', false);
    }

    public function test_admin_can_edit_all_redesigned_homepage_content_sections(): void
    {
        Storage::fake(config('app.images_disk_default'));
        $config = $this->homePageConfig();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.home-page.edit'), [
                'selected_product_types' => '',
                'selected_products_id' => '',
                'selected_best_sales_products_id' => '',
                'selected_brands_id' => '',
                'seo_title' => ['uk' => '', 'ru' => ''],
                'seo_text' => ['uk' => '', 'ru' => ''],
                'style_section' => ['enabled' => 0],
                'content_sections' => [
                    'hero' => [
                        'enabled' => 1,
                        'eyebrow' => ['uk' => 'Шоурум в Одесі', 'ru' => 'Шоурум в Одессе'],
                        'secondary_label' => ['uk' => 'Наші послуги', 'ru' => 'Наши услуги'],
                        'secondary_url' => '/services',
                    ],
                    'catalog' => [
                        'enabled' => 1,
                        'kicker' => ['uk' => 'Каталог', 'ru' => 'Каталог'],
                        'title' => ['uk' => 'Оберіть тип дверей', 'ru' => 'Выберите тип дверей'],
                    ],
                    'popular' => [
                        'enabled' => 1,
                        'kicker' => ['uk' => 'Вибір клієнтів', 'ru' => 'Выбор клиентов'],
                        'title' => ['uk' => 'Популярні двері', 'ru' => 'Популярные двери'],
                        'link_label' => ['uk' => 'Весь каталог', 'ru' => 'Весь каталог'],
                        'link_url' => '/catalog',
                    ],
                    'numbers' => [
                        'enabled' => 1,
                        'kicker' => ['uk' => 'Про компанію', 'ru' => 'О компании'],
                        'title' => ['uk' => 'Bona у цифрах', 'ru' => 'Bona в цифрах'],
                        'items' => [[
                            'value' => '16',
                            'label' => ['uk' => 'років досвіду', 'ru' => 'лет опыта'],
                        ]],
                    ],
                    'ideas' => [
                        'enabled' => 1,
                        'kicker' => ['uk' => 'Натхнення', 'ru' => 'Вдохновение'],
                        'title' => ['uk' => 'Ідеї для дому', 'ru' => 'Идеи для дома'],
                        'items' => [[
                            'title' => ['uk' => 'Світла спальня', 'ru' => 'Светлая спальня'],
                            'text' => ['uk' => 'Приховані двері', 'ru' => 'Скрытые двери'],
                            'image' => UploadedFile::fake()->image('idea.jpg', 1200, 900),
                        ]],
                    ],
                    'steps' => [
                        'enabled' => 1,
                        'kicker' => ['uk' => 'Процес', 'ru' => 'Процесс'],
                        'title' => ['uk' => 'Як ми працюємо', 'ru' => 'Как мы работаем'],
                        'cta_label' => ['uk' => 'Викликати майстра', 'ru' => 'Вызвать мастера'],
                        'cta_url' => '#dialog-call-measurer',
                        'items' => [[
                            'number' => '01',
                            'title' => ['uk' => 'Заявка', 'ru' => 'Заявка'],
                            'text' => ['uk' => 'Залиште номер', 'ru' => 'Оставьте номер'],
                        ]],
                    ],
                    'works' => [
                        'enabled' => 1,
                        'kicker' => ['uk' => 'Портфоліо', 'ru' => 'Портфолио'],
                        'title' => ['uk' => 'Реалізовані проєкти', 'ru' => 'Реализованные проекты'],
                        'link_label' => ['uk' => 'Усі проєкти', 'ru' => 'Все проекты'],
                        'link_url' => '/works',
                        'items' => [[
                            'title' => ['uk' => 'Квартира в Одесі', 'ru' => 'Квартира в Одессе'],
                            'text' => ['uk' => 'Пʼять дверей', 'ru' => 'Пять дверей'],
                            'url' => '/works/apartment',
                            'image' => UploadedFile::fake()->image('work.jpg', 1200, 900),
                        ]],
                    ],
                    'reviews' => ['enabled' => 1, 'kicker' => ['uk' => 'Google', 'ru' => 'Google'], 'title' => ['uk' => 'Відгуки', 'ru' => 'Отзывы'], 'link_label' => ['uk' => 'Усі відгуки', 'ru' => 'Все отзывы'], 'link_url' => 'https://example.com/reviews'],
                    'instagram' => ['enabled' => 1, 'kicker' => ['uk' => '@bona_doors', 'ru' => '@bona_doors'], 'title' => ['uk' => 'Instagram', 'ru' => 'Instagram'], 'link_label' => ['uk' => 'Підписатися', 'ru' => 'Подписаться'], 'link_url' => 'https://instagram.com/bona_doors'],
                    'blog' => ['enabled' => 1, 'kicker' => ['uk' => 'Корисне', 'ru' => 'Полезное'], 'title' => ['uk' => 'Статті', 'ru' => 'Статьи'], 'link_label' => ['uk' => 'Всі статті', 'ru' => 'Все статьи'], 'link_url' => '/blog'],
                    'faq' => ['enabled' => 1, 'kicker' => ['uk' => 'Відповіді', 'ru' => 'Ответы'], 'title' => ['uk' => 'Питання', 'ru' => 'Вопросы']],
                    'partners' => ['enabled' => 1, 'kicker' => ['uk' => 'Бренди', 'ru' => 'Бренды'], 'title' => ['uk' => 'Партнери', 'ru' => 'Партнеры']],
                    'seo' => ['enabled' => 1],
                ],
            ]);

        $response->assertOk()->assertJsonPath('data.success', true);

        $sections = $config->fresh()->content_sections;
        $this->assertSame('Bona у цифрах', $sections['numbers']['title']['uk']);
        $this->assertSame('16', $sections['numbers']['items'][0]['value']);
        $this->assertSame('Світла спальня', $sections['ideas']['items'][0]['title']['uk']);
        $this->assertSame('/works/apartment', $sections['works']['items'][0]['url']);
        Storage::disk(config('app.images_disk_default'))->assertExists($sections['ideas']['items'][0]['image_path']);
        Storage::disk(config('app.images_disk_default'))->assertExists($sections['works']['items'][0]['image_path']);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Bona у цифрах')
            ->assertSee('Світла спальня')
            ->assertSee('Реалізовані проєкти');
    }

    public function test_homepage_content_rejects_unsafe_links(): void
    {
        $this->homePageConfig();

        $this->actingAs($this->admin())
            ->from(route('admin.home-page.edit.page'))
            ->post(route('admin.home-page.edit'), [
                'selected_product_types' => '',
                'selected_products_id' => '',
                'selected_best_sales_products_id' => '',
                'selected_brands_id' => '',
                'style_section' => ['enabled' => 0],
                'content_sections' => [
                    'popular' => [
                        'enabled' => 1,
                        'link_url' => 'javascript:alert(1)',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.home-page.edit.page'))
            ->assertSessionHasErrors('content_sections.popular.link_url');
    }

    public function test_homepage_content_rejects_protocol_relative_links(): void
    {
        $this->homePageConfig();

        $this->actingAs($this->admin())
            ->from(route('admin.home-page.edit.page'))
            ->post(route('admin.home-page.edit'), [
                'selected_product_types' => '',
                'selected_products_id' => '',
                'selected_best_sales_products_id' => '',
                'selected_brands_id' => '',
                'style_section' => ['enabled' => 0],
                'content_sections' => [
                    'popular' => [
                        'enabled' => 1,
                        'link_url' => '//malicious.example/path',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.home-page.edit.page'))
            ->assertSessionHasErrors('content_sections.popular.link_url');
    }

    public function test_legacy_popular_products_are_shown_and_migrated_without_loss(): void
    {
        $this->homePageConfig();
        $product = $this->makeProduct();
        HomePageNewProducts::query()->create(['product_id' => $product->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.home-page.edit.page'))
            ->assertOk()
            ->assertSee(':selected-best-sales-products=', false)
            ->assertSee('&quot;product_id&quot;:'.$product->id, false);

        $this->actingAs($this->admin())
            ->post(route('admin.home-page.edit'), [
                'selected_product_types' => '',
                'selected_products_id' => '',
                'selected_best_sales_products_id' => (string) $product->id,
                'selected_brands_id' => '',
                'style_section' => ['enabled' => 0],
            ])
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertDatabaseMissing(HomePageNewProducts::class, ['product_id' => $product->id]);
        $this->assertDatabaseHas(HomePageBestSalesProducts::class, ['product_id' => $product->id]);
    }

    public function test_homepage_editor_receives_every_content_section(): void
    {
        $this->homePageConfig();

        $response = $this->actingAs($this->admin())->get(route('admin.home-page.edit.page'));

        $response->assertOk()->assertSee(':content-sections=', false);

        foreach (['hero', 'catalog', 'popular', 'numbers', 'ideas', 'steps', 'works', 'reviews', 'instagram', 'blog', 'faq', 'partners', 'seo'] as $section) {
            $response->assertSee('&quot;'.$section.'&quot;:', false);
        }
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
