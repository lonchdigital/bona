<?php

namespace Tests\Feature;

use App\Models\ApplicationConfig;
use App\Models\CatalogMenuConfiguration;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use App\Services\CatalogMenu\CatalogMenuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $this->assertSame(
            '/product-category/'.$productType->slug.'/manufacturer/barausse',
            $configuration->columns[0]['items'][0]['url']['uk'],
        );
        $this->assertSame(
            '/ru/product-category/'.$productType->slug.'/manufacturer/barausse',
            $configuration->columns[0]['items'][0]['url']['ru'],
        );
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
            ->assertSee('/product-category/visible-doors/manufacturer/barausse', false)
            ->assertSee('class="bona-mainnav__direct"', false);
    }

    public function test_interior_doors_menu_uses_the_reference_style_cards_and_real_catalogue_links(): void
    {
        $interiorDoors = $this->productType([
            'slug' => 'interior-doors',
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'sort_order' => 1,
        ]);

        CatalogMenuConfiguration::query()->create([
            'product_type_id' => $interiorDoors->id,
            'is_visible' => true,
            'sort_order' => 0,
            'show_in_header' => false,
            'header_order' => 0,
            'cards' => [],
            'columns' => [],
        ]);
        app(CatalogMenuService::class)->forgetCache();

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('data-mega-tab="'.$interiorDoors->id.'"', false)
            ->assertSee('aria-selected="true"', false)
            ->assertSee('data-menu-style-card="modern"', false)
            ->assertSee('data-menu-style-card="classic"', false)
            ->assertSee('data-menu-style-card="neoclassic"', false)
            ->assertSee('data-menu-style-card="minimal"', false)
            ->assertSee('data-menu-style-card="hitech"', false)
            ->assertSee('Двері модерн')
            ->assertSee('Двері класика')
            ->assertSee('Двері неокласика')
            ->assertSee('Двері мінімалізм')
            ->assertSee('Двері хай-тек')
            ->assertSee('/product-category/interior-doors/filter/styl=modern', false)
            ->assertSee('/product-category/interior-doors/filter/styl=klassyka', false)
            ->assertSee('/product-category/interior-doors/filter/styl=neoklassyka', false)
            ->assertSee('/product-category/interior-doors/filter/styl=mynymalyzm', false)
            ->assertSee('/product/mizhkimnatni-dveri-glasso', false)
            ->assertSee('Двері модерн в інтер’єрі')
            ->assertSee('Двері хай-тек в інтер’єрі');

        $this->get(route('localized.store.home', ['lang' => 'ru']))
            ->assertOk()
            ->assertSee('Двери модерн')
            ->assertSee('Двери классика')
            ->assertSee('/ru/product-category/interior-doors/filter/styl=modern', false)
            ->assertSee('/ru/product/mizhkimnatni-dveri-glasso', false);
    }

    public function test_storefront_menu_uses_a_real_product_image_when_the_type_image_is_missing(): void
    {
        Storage::fake('public');
        config()->set('app.images_disk_default', 'public');

        $productType = $this->productType([
            'slug' => 'white-doors',
            'name' => ['uk' => 'Білі двері', 'ru' => 'Белые двери'],
            'image_path' => 'missing/type.webp',
            'sort_order' => 1,
        ]);
        $productImage = 'products/white-door-preview.webp';
        Storage::disk('public')->put($productImage, 'image');

        $this->makeProduct([
            'product_type_id' => $productType->id,
            'preview_image_path' => $productImage,
            'main_image_path' => 'missing/product-main.webp',
            'orders_count' => 8,
            // Legacy production data uses this flag inconsistently, while the
            // same products remain visible in storefront catalogue queries.
            'is_active' => false,
        ]);

        CatalogMenuConfiguration::query()->create([
            'product_type_id' => $productType->id,
            'is_visible' => true,
            'sort_order' => 0,
            'show_in_header' => true,
            'header_order' => 0,
            'cards' => [],
            'columns' => [],
        ]);
        app(CatalogMenuService::class)->forgetCache();

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($productImage), false)
            ->assertDontSee(Storage::disk('public')->url('missing/type.webp'), false);
    }

    public function test_header_promotes_door_handles_instead_of_the_accessories_type(): void
    {
        $accessories = $this->productType([
            'slug' => 'aksessuar',
            'name' => ['uk' => 'Аксесуари', 'ru' => 'Аксессуары'],
            'sort_order' => 1,
        ]);
        $doorHandles = $this->category($accessories->id, 'dverni-rucky', 'Дверні ручки');
        $doorHandles->setTranslations('name', [
            'uk' => 'Дверні ручки',
            'ru' => 'Дверные ручки',
        ])->save();

        CatalogMenuConfiguration::query()->create([
            'product_type_id' => $accessories->id,
            'is_visible' => true,
            'sort_order' => 0,
            'show_in_header' => true,
            'header_order' => 0,
            'cards' => [$doorHandles->id],
            'columns' => [],
        ]);

        app(CatalogMenuService::class)->forgetCache();

        $ukrainianLink = $this->directHeaderLinks($this->get(route('store.home'))->assertOk()->getContent())[0];
        $russianLink = $this->directHeaderLinks($this->get(route('localized.store.home', ['lang' => 'ru']))->assertOk()->getContent())[0];

        $this->assertSame('Дверні ручки', $ukrainianLink['label']);
        $this->assertSame('/product-category/aksessuar/category/dverni-rucky', $ukrainianLink['url']);
        $this->assertSame('Дверные ручки', $russianLink['label']);
        $this->assertSame('/ru/product-category/aksessuar/category/dverni-rucky', $russianLink['url']);
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

    public function test_admin_can_edit_bilingual_footer_menus_and_the_storefront_uses_them(): void
    {
        $payload = [
            'navigation' => [
                [
                    'label' => ['uk' => 'Навігація тест', 'ru' => 'Навигация тест'],
                    'url' => ['uk' => '/contacts', 'ru' => '/ru/contacts'],
                    'is_visible' => 1,
                    'sort_order' => 2,
                ],
                [
                    'label' => ['uk' => 'Прихований пункт', 'ru' => 'Скрытый пункт'],
                    'url' => ['uk' => '/hidden', 'ru' => '/ru/hidden'],
                    'is_visible' => 0,
                    'sort_order' => 1,
                ],
            ],
            'categories' => [[
                'label' => ['uk' => 'Категорія тест', 'ru' => 'Категория тест'],
                'url' => ['uk' => '/shop', 'ru' => '/ru/shop'],
                'is_visible' => 1,
                'sort_order' => 0,
            ]],
        ];

        $this->actingAs($this->admin())
            ->post(route('admin.catalog-menu.footer.update'), $payload)
            ->assertRedirect(route('admin.catalog-menu.page', ['tab' => 'footer']));

        $storedNavigation = ApplicationConfig::query()
            ->where('config_name', CatalogMenuService::FOOTER_NAVIGATION_CONFIG)
            ->firstOrFail()
            ->config_data;

        $this->assertSame(
            'Навігація тест',
            collect($storedNavigation)->firstWhere('is_visible', true)['label']['uk'],
        );

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Навігація тест')
            ->assertSee('href="/contacts"', false)
            ->assertSee('Категорія тест')
            ->assertDontSee('Прихований пункт');

        $this->get(route('localized.store.home', ['lang' => 'ru']))
            ->assertOk()
            ->assertSee('Навигация тест')
            ->assertSee('href="/ru/contacts"', false)
            ->assertSee('Категория тест')
            ->assertDontSee('Скрытый пункт');
    }

    public function test_footer_menu_rejects_unsafe_urls(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.catalog-menu.page', ['tab' => 'footer']))
            ->post(route('admin.catalog-menu.footer.update'), [
                'navigation' => [[
                    'label' => ['uk' => 'Небезпечний', 'ru' => 'Опасный'],
                    'url' => ['uk' => 'javascript:alert(1)', 'ru' => '/ru/contacts'],
                    'is_visible' => 1,
                    'sort_order' => 0,
                ]],
                'categories' => [],
            ])
            ->assertRedirect(route('admin.catalog-menu.page', ['tab' => 'footer']))
            ->assertSessionHasErrors('navigation.0.url.uk');
    }

    public function test_footer_menu_editor_is_available_as_a_separate_menu_settings_tab(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.catalog-menu.page', ['tab' => 'footer']))
            ->assertOk()
            ->assertSee(trans('admin.footer_menu_tab'))
            ->assertSee('data-menu-language-switch', false)
            ->assertSee('data-footer-menu-list', false)
            ->assertSee('data-menu-sort-order', false)
            ->assertSee('name="navigation[0][label][uk]"', false)
            ->assertSee('name="categories[0][label][ru]"', false)
            ->assertSee('data-footer-menu-add', false);
    }

    public function test_catalog_menu_overview_is_a_localized_drag_and_drop_builder(): void
    {
        $productType = $this->productType([
            'name' => ['uk' => 'Міжкімнатні двері', 'ru' => 'Межкомнатные двери'],
            'sort_order' => 1,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.catalog-menu.page'))
            ->assertOk()
            ->assertSee('data-menu-language-switch', false)
            ->assertSee('data-header-links-list', false)
            ->assertSee('data-catalog-types-list', false)
            ->assertSee('data-product-type-id="'.$productType->id.'"', false)
            ->assertSee('data-menu-drag-handle', false)
            ->assertSee('name="configurations['.$productType->id.'][sort_order]"', false)
            ->assertSee('name="configurations['.$productType->id.'][header_order]"', false)
            ->assertSee(trans('admin.catalog_menu_edit_content'))
            ->assertDontSee('type="number"', false);
    }

    public function test_catalog_menu_content_editor_exposes_visual_hierarchy_without_manual_order_fields(): void
    {
        $productType = $this->productType(['sort_order' => 1]);
        $category = $this->category($productType->id, 'classic', 'Класика');

        CatalogMenuConfiguration::query()->create([
            'product_type_id' => $productType->id,
            'is_visible' => true,
            'sort_order' => 0,
            'show_in_header' => false,
            'header_order' => 0,
            'cards' => [$category->id],
            'columns' => [[
                'title' => ['uk' => 'За стилем', 'ru' => 'По стилю'],
                'sort_order' => 0,
                'items' => [[
                    'category_id' => $category->id,
                    'label' => ['uk' => '', 'ru' => ''],
                    'url' => ['uk' => '', 'ru' => ''],
                    'sort_order' => 0,
                ]],
            ]],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.catalog-menu.edit.page', $productType))
            ->assertOk()
            ->assertSee('data-menu-language-switch', false)
            ->assertSee('data-visual-card-list', false)
            ->assertSee('data-columns-container', false)
            ->assertSee('data-items-container', false)
            ->assertSee('data-menu-category-select', false)
            ->assertSee('name="columns[0][title][uk]"', false)
            ->assertSee('name="columns[0][title][ru]"', false)
            ->assertDontSee('type="number"', false);
    }

    public function test_catalog_menu_overview_uses_the_saved_builder_order(): void
    {
        $laterType = $this->productType(['slug' => 'later-type', 'name' => 'Пізніше', 'sort_order' => 1]);
        $firstType = $this->productType(['slug' => 'first-type', 'name' => 'Спочатку', 'sort_order' => 2]);
        $unconfiguredType = $this->productType(['slug' => 'hidden-type', 'name' => 'Без налаштувань', 'sort_order' => 0]);

        CatalogMenuConfiguration::query()->create([
            'product_type_id' => $laterType->id,
            'is_visible' => true,
            'sort_order' => 8,
            'show_in_header' => false,
            'header_order' => 0,
            'cards' => [],
            'columns' => [],
        ]);
        CatalogMenuConfiguration::query()->create([
            'product_type_id' => $firstType->id,
            'is_visible' => true,
            'sort_order' => 1,
            'show_in_header' => false,
            'header_order' => 0,
            'cards' => [],
            'columns' => [],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.catalog-menu.page'))
            ->assertOk()
            ->assertSeeInOrder([
                'data-product-type-id="'.$firstType->id.'"',
                'data-product-type-id="'.$laterType->id.'"',
                'data-product-type-id="'.$unconfiguredType->id.'"',
            ], false);
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

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function directHeaderLinks(string $html): array
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);

        return collect(iterator_to_array($xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " bona-mainnav__direct ")]')))
            ->map(fn (\DOMElement $link) => [
                'label' => trim(preg_replace('/\s+/u', ' ', $link->textContent)),
                'url' => $link->getAttribute('href'),
            ])
            ->all();
    }
}
