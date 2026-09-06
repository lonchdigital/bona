<?php

namespace Tests\Feature;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Models\Author;
use App\Models\BlogArticle;
use App\Models\BlogArticleBlock;
use App\Models\ProductText;
use App\Models\Role;
use App\Models\ServicesPageSections;
use App\Models\User;
use App\Support\Product\ProductPageDefaults;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class EditorialCommercePagesTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_blog_archive_renders_without_a_saved_page_configuration(): void
    {
        $this->get(route('blog.main.page'))
            ->assertOk()
            ->assertSee('bona-blog-index', false)
            ->assertSee('"@context":"https://schema.org"', false)
            ->assertSee('"@type":"Blog"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertDontSee('__contextArgs', false);
    }

    public function test_article_renders_managed_blocks_and_valid_faq_schema_in_both_languages(): void
    {
        $article = BlogArticle::create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => 'Як вибрати двері', 'ru' => 'Как выбрать двери'],
            'preview_text' => ['uk' => 'Практична вступна порада.', 'ru' => 'Практический вводный совет.'],
            'slug' => 'yak-vybraty-dveri',
            'hero_image_path' => 'blog/test.webp',
            'meta_title' => ['uk' => 'Як вибрати двері | Bona Doors', 'ru' => 'Как выбрать двери | Bona Doors'],
            'meta_description' => ['uk' => 'Поради про вибір дверей.', 'ru' => 'Советы по выбору дверей.'],
            'meta_keywords' => ['uk' => 'двері', 'ru' => 'двери'],
        ]);

        BlogArticleBlock::create([
            'blog_article_id' => $article->id,
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_TEXT,
            'content' => [
                'uk' => '<h2>Матеріал і конструкція</h2><p>Текст українською.</p><table><thead><tr><th>Критерій</th><th>Що перевірити</th></tr></thead><tbody><tr><td>Гарантія</td><td>Умови</td></tr></tbody></table><p><strong>Порада:</strong> Перевірте договір.</p><p><strong>Q: Скільки діє право на обмін?</strong><br><strong>А:</strong> Чотирнадцять днів.</p><section class="article-related"><h2>Читайте також</h2><ul><li><a href="/blog/kerovanyi-material">Керований матеріал</a></li></ul></section><section class="article-resources"><h2>Корисні ресурси</h2><ul><li><a href="/delivery-info">Умови доставки</a></li></ul></section>',
                'ru' => '<h2>Материал и конструкция</h2><p>Текст на русском.</p><p><strong>Совет:</strong> Проверьте договор.</p><p><strong>Q: Сколько действует право на обмен?</strong><br><strong>A:</strong> Четырнадцать дней.</p>',
            ],
        ]);
        BlogArticleBlock::create([
            'blog_article_id' => $article->id,
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS,
            'content' => ['questions' => [[
                'question' => ['uk' => 'Коли робити замір?', 'ru' => 'Когда делать замер?'],
                'answer' => ['uk' => 'Після чистової підлоги.', 'ru' => 'После чистового пола.'],
            ]]],
        ]);

        Author::create([
            'creator_id' => $this->author()->id,
            'slug' => 'oksana-honchar-test',
            'name' => ['uk' => 'Оксана Гончар', 'ru' => 'Оксана Гончар'],
        ]);

        $response = $this->get(route('blog.article.page', ['blogArticleSlug' => $article->slug]));

        $response
            ->assertOk()
            ->assertSee('bona-article-page', false)
            ->assertSee('bona-article-hero', false)
            ->assertSee('Bona Doors Editorial')
            ->assertSee('bona-article-sidebar', false)
            ->assertSee('bona-article-consultant', false)
            ->assertSee('bona-article-configurator', false)
            ->assertSee('bona-article-links', false)
            ->assertSee('bona-article-share', false)
            ->assertSee('Керований матеріал')
            ->assertSee('Умови доставки')
            ->assertSee('Матеріал і конструкція')
            ->assertSee('class="article-table"', false)
            ->assertSee('class="article-advice"', false)
            ->assertSee('class="article-qa"', false)
            ->assertSee('Питання')
            ->assertSee('Відповідь')
            ->assertDontSee('Q: Скільки діє право', false)
            ->assertSee('"@context":"https://schema.org"', false)
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('"@type":"WebPage"', false)
            ->assertSee('"wordCount":', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('Коли робити замір?')
            ->assertSeeInOrder([
                'bona-article-faq',
                'bona-article-links',
                'bona-article-share',
                'bona-article-sidebar',
                'bona-article-author',
            ], false)
            ->assertDontSee('__contextArgs', false);

        $this->assertSame(1, substr_count($response->getContent(), 'class="bona-article-sidebar"'));
        $this->assertSame(1, substr_count($response->getContent(), 'Керований матеріал'));

        $document = new \DOMDocument;
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " bona-article-sidebar ")]//*[contains(concat(" ", normalize-space(@class), " "), " bona-article-share ")]')->length);

        $articleSchema = collect($this->schemaDocuments($response->getContent()))
            ->first(fn (array $document) => isset($document['@graph'])
                && collect($document['@graph'])->contains(fn (array $node) => ($node['@type'] ?? null) === 'BlogPosting'));
        $this->assertNotNull($articleSchema);
        $this->assertTrue(collect($articleSchema['@graph'])->contains(
            fn (array $node) => ($node['@type'] ?? null) === 'FAQPage'
                && count($node['mainEntity'] ?? []) === 1
        ));

        $this->get(route('localized.blog.article.page', ['lang' => 'ru', 'blogArticleSlug' => $article->slug]))
            ->assertOk()
            ->assertSee('Материал и конструкция')
            ->assertSee('class="article-advice"', false)
            ->assertSee('class="article-qa"', false)
            ->assertSee('Вопрос')
            ->assertSee('Ответ')
            ->assertSee('Когда делать замер?')
            ->assertDontSee('Текст українською.');
    }

    public function test_legacy_articles_receive_an_editable_faq_without_duplicating_existing_content(): void
    {
        $slugs = [
            'znayomstvo-z-salonom-bonadoors',
            'yak-obraty-idealni-dveri',
            'yak-vybraty-dverni-ruchky',
            'mizhkimnatni-dveri-gid',
        ];

        foreach ($slugs as $slug) {
            BlogArticle::create([
                'creator_id' => $this->author()->id,
                'name' => ['uk' => $slug, 'ru' => $slug],
                'preview_text' => ['uk' => '', 'ru' => ''],
                'slug' => $slug,
                'hero_image_path' => 'blog/legacy-test.webp',
                'meta_title' => ['uk' => '', 'ru' => ''],
                'meta_description' => ['uk' => '', 'ru' => ''],
                'meta_keywords' => ['uk' => '', 'ru' => ''],
            ]);
        }

        $existingArticle = BlogArticle::where('slug', $slugs[0])->firstOrFail();
        BlogArticleBlock::create([
            'blog_article_id' => $existingArticle->id,
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS,
            'content' => ['questions' => [[
                'question' => ['uk' => 'Власне питання?', 'ru' => 'Собственный вопрос?'],
                'answer' => ['uk' => 'Власна відповідь.', 'ru' => 'Собственный ответ.'],
            ]]],
        ]);

        $migration = require database_path('migrations/2026_09_06_090000_backfill_faq_for_legacy_blog_articles.php');
        $migration->up();
        $migration->up();

        foreach ($slugs as $slug) {
            $article = BlogArticle::where('slug', $slug)->firstOrFail();
            $blocks = BlogArticleBlock::where('blog_article_id', $article->id)
                ->where('type_id', BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS)
                ->get();

            $this->assertCount(1, $blocks);
            $this->assertNotEmpty($blocks->first()->content['questions']);
            $this->assertNotEmpty($blocks->first()->content['questions'][0]['question']['uk']);
            $this->assertNotEmpty($blocks->first()->content['questions'][0]['question']['ru']);
        }
    }

    public function test_service_detail_uses_managed_copy_and_hides_an_empty_content_section(): void
    {
        $filled = ServicesPageSections::create([
            'slug' => 'montazh-dverei-test',
            'title' => ['uk' => 'Монтаж дверей', 'ru' => 'Монтаж дверей'],
            'description' => ['uk' => 'Акуратний монтаж.', 'ru' => 'Аккуратный монтаж.'],
            'intro' => ['uk' => 'Працюємо чисто й точно.', 'ru' => 'Работаем чисто и точно.'],
            'content' => ['uk' => '<h2>Що входить</h2><p>Монтаж і регулювання.</p>', 'ru' => '<h2>Что входит</h2><p>Монтаж и регулировка.</p>'],
            'button_text' => ['uk' => 'Замовити', 'ru' => 'Заказать'],
            'button_url' => '/contacts',
            'section_image_path' => 'assets/images/services/installation.webp',
            'meta_title' => ['uk' => 'Монтаж дверей | Bona Doors', 'ru' => 'Монтаж дверей | Bona Doors'],
            'meta_description' => ['uk' => 'Монтаж дверей в Одесі.', 'ru' => 'Монтаж дверей в Одессе.'],
            'sort_order' => 0,
        ]);

        $response = $this->get(route('store.service.page', ['serviceSlug' => $filled->slug]));
        $response
            ->assertOk()
            ->assertSee('bona-service-detail', false)
            ->assertSee('/assets/images/services/installation.webp', false)
            ->assertSee('href="/contacts"', false)
            ->assertSee('"@context":"https://schema.org"', false)
            ->assertSee('"@type":"Service"', false)
            ->assertSee('Монтаж і регулювання.')
            ->assertDontSee('__contextArgs', false);

        $empty = ServicesPageSections::create([
            'slug' => 'porozhnia-posluha',
            'title' => ['uk' => 'Порожня послуга', 'ru' => 'Пустая услуга'],
            'description' => ['uk' => '', 'ru' => ''],
            'intro' => ['uk' => '', 'ru' => ''],
            'content' => ['uk' => '', 'ru' => ''],
            'button_text' => ['uk' => 'Зв’язатися', 'ru' => 'Связаться'],
            'section_image_path' => '',
            'sort_order' => 1,
        ]);

        $this->get(route('store.service.page', ['serviceSlug' => $empty->slug]))
            ->assertOk()
            ->assertDontSee('bona-service-detail__content', false)
            ->assertDontSee('bona-service-detail__hero-media', false);
    }

    public function test_admin_can_edit_every_service_page_field_and_unsafe_links_are_rejected(): void
    {
        $section = ServicesPageSections::create([
            'slug' => 'stara-posluha',
            'title' => ['uk' => 'Стара послуга', 'ru' => 'Старая услуга'],
            'description' => ['uk' => 'Старий опис', 'ru' => 'Старое описание'],
            'button_text' => ['uk' => 'Замовити', 'ru' => 'Заказать'],
            'button_url' => '#dialog-call-measurer',
            'section_image_path' => 'assets/images/services/consultation.webp',
        ]);

        $payload = [
            'meta_title' => ['uk' => 'Послуги | Bona Doors', 'ru' => 'Услуги | Bona Doors'],
            'meta_description' => ['uk' => 'Опис послуг', 'ru' => 'Описание услуг'],
            'meta_keywords' => ['uk' => 'послуги', 'ru' => 'услуги'],
            'sections' => [[
                'id' => $section->id,
                'slug' => 'konsultatsiia-test',
                'title' => ['uk' => 'Консультація', 'ru' => 'Консультация'],
                'description' => ['uk' => 'Новий опис.', 'ru' => 'Новое описание.'],
                'intro' => ['uk' => 'Вступ українською.', 'ru' => 'Вступление на русском.'],
                'content' => ['uk' => '<h2>Етапи</h2><p>Підбір рішення.</p>', 'ru' => '<h2>Этапы</h2><p>Подбор решения.</p>'],
                'button_text' => ['uk' => 'Написати нам', 'ru' => 'Написать нам'],
                'button_url' => '/contacts',
                'meta_title' => ['uk' => 'Консультація | Bona Doors', 'ru' => 'Консультация | Bona Doors'],
                'meta_description' => ['uk' => 'Консультація щодо дверей.', 'ru' => 'Консультация по дверям.'],
                'meta_keywords' => ['uk' => 'консультація', 'ru' => 'консультация'],
                'meta_tags' => '',
            ]],
        ];

        $this->actingAs($this->admin())
            ->from(route('admin.services.edit.page'))
            ->post(route('admin.services.edit'), $payload)
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $section->refresh();
        $this->assertSame('konsultatsiia-test', $section->slug);
        $this->assertSame('Консультация', $section->getTranslation('title', 'ru'));
        $this->assertSame('Вступ українською.', $section->getTranslation('intro', 'uk'));
        $this->assertSame('/contacts', $section->button_url);

        data_set($payload, 'sections.0.button_url', '//malicious.example');

        $this->actingAs($this->admin())
            ->from(route('admin.services.edit.page'))
            ->post(route('admin.services.edit'), $payload)
            ->assertRedirect(route('admin.services.edit.page'))
            ->assertSessionHasErrors('sections.0.button_url');
    }

    public function test_product_page_keeps_commerce_hooks_schema_and_only_filled_flexible_blocks(): void
    {
        $this->seedCurrency();

        $product = $this->makeProduct([
            'slug' => 'editorial-test-door',
            'sku' => 'BD-SEO-01',
            'main_image_path' => 'products/test-main.webp',
            'preview_image_path' => 'products/test-preview.webp',
            'meta_title' => ['uk' => 'Тестові двері | Bona Doors', 'ru' => 'Тестовая дверь | Bona Doors'],
            'meta_description' => ['uk' => 'Опис тестових дверей.', 'ru' => 'Описание тестовой двери.'],
            'meta_keywords' => ['uk' => 'двері', 'ru' => 'двери'],
            'content_blocks' => [
                [
                    'id' => 'filled-block',
                    'type' => 'text',
                    'eyebrow' => ['uk' => 'Деталі', 'ru' => 'Детали'],
                    'title' => ['uk' => 'Продумана конструкція', 'ru' => 'Продуманная конструкция'],
                    'content' => ['uk' => '<p>Заповнений блок товару.</p>', 'ru' => '<p>Заполненный блок товара.</p>'],
                ],
                [
                    'id' => 'empty-block',
                    'type' => 'text',
                    'title' => ['uk' => '', 'ru' => ''],
                    'content' => ['uk' => '', 'ru' => ''],
                ],
            ],
        ]);

        ProductText::create([
            'product_id' => $product->id,
            'language' => 'uk',
            'short_content' => '<p>Короткий опис.</p>',
            'content' => '<h2>Про модель</h2><p>Повний опис.</p>',
        ]);

        $response = $this->get(route('store.product.page', ['productSlug' => $product->slug]));
        $response
            ->assertOk()
            ->assertSee('bona-product-page', false)
            ->assertSee('product-page product-v1', false)
            ->assertSee('class="product-hero"', false)
            ->assertSee('data-product-gallery', false)
            ->assertSee('class="product-buybox"', false)
            ->assertSee('class="product-services"', false)
            ->assertSee('class="product-details-grid"', false)
            ->assertSee('class="product-info-tabs"', false)
            ->assertSee('data-product-section-nav', false)
            ->assertSee('Про товар')
            ->assertSee('Придбати')
            ->assertDontSee('class="mobile-buybar"', false)
            ->assertSee('single-product-add-to-cart', false)
            ->assertSee('single-product-wish-list', false)
            ->assertSee('data-product-compare', false)
            ->assertSee('4.9')
            ->assertSee(trans('base.product_review_based_on', ['COUNT' => 3]))
            ->assertSee('"@context":"https://schema.org"', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"sku":"BD-SEO-01"', false)
            ->assertSee('"hasMerchantReturnPolicy":{"@id":', false)
            ->assertSee('"@type":"WebPage"', false)
            ->assertSee('Продумана конструкція')
            ->assertSee('Заповнений блок товару.')
            ->assertDontSee('empty-block')
            ->assertDontSee('class="bona-product-hero"', false)
            ->assertDontSee('"@type":"FAQPage"', false)
            ->assertDontSee('__contextArgs', false);

        $this->assertSame(1, substr_count($response->getContent(), 'bona-product-editorial--text'));

        $productSchema = collect($this->schemaDocuments($response->getContent()))
            ->first(fn (array $document) => ($document['@type'] ?? null) === 'Product');
        $this->assertNotNull($productSchema);
        $this->assertSame('BD-SEO-01', $productSchema['sku']);
        $this->assertArrayHasKey('hasMerchantReturnPolicy', $productSchema['offers']);
    }

    public function test_default_product_sections_render_from_admin_managed_content_and_keep_dynamic_instalments(): void
    {
        $this->seedCurrency();
        config()->set('payment.monobank.periods', [3, 4, 5]);
        config()->set('payment.privatbank.periods', [2, 3, 6]);
        $product = $this->makeProduct([
            'slug' => 'complete-reference-door',
            'price' => 6000,
            'content_blocks' => ProductPageDefaults::blocks(),
        ]);

        $this->get(route('store.product.page', ['productSlug' => $product->slug]))
            ->assertOk()
            ->assertSee('Відчуття в щоденному житті')
            ->assertSee('Повний комплект')
            ->assertSee('Від заміру до монтажу')
            ->assertSee('Без переплат за комфорт')
            ->assertSee('data-provider-example="mono"', false)
            ->assertSee('1 270,80 грн/міс.')
            ->assertSee('1 078 грн/міс.')
            ->assertSee('data-installment-terms-open', false)
            ->assertSee("data-periods='[3,4,5]'", false)
            ->assertSee("data-periods='[2,3,6]'", false)
            ->assertSee('/door-configurator', false);
    }

    public function test_door_configurator_placeholder_is_available_in_both_languages_with_schema(): void
    {
        $this->get(route('store.door-configurator.page'))
            ->assertOk()
            ->assertSee('bona-door-configurator', false)
            ->assertSee('Конфігуратор дверей')
            ->assertSee('"@type":"WebPage"', false);

        $this->get(route('localized.store.door-configurator.page', ['lang' => 'ru']))
            ->assertOk()
            ->assertSee('Конфигуратор дверей')
            ->assertSee('"inLanguage":"ru-UA"', false);
    }

    public function test_new_editorial_pages_define_mobile_breakpoints_and_admin_block_ordering(): void
    {
        $styles = file_get_contents(resource_path('scss/storefront/_editorial-commerce.scss'));
        $productEditor = file_get_contents(resource_path('js/admin/forms/ProductPageEditForm.vue'));
        $articleEditor = file_get_contents(resource_path('js/admin/containers/BlogArticleBlocksContainer.vue'));

        $this->assertStringContainsString('@media (max-width: 900px)', $styles);
        $this->assertStringContainsString('@media (max-width: 640px)', $styles);
        $this->assertStringContainsString('body.bona-article-body', $styles);
        $this->assertStringContainsString('.bona-article-page > .bona-content-breadcrumbs', $styles);
        $this->assertStringContainsString('margin: 0 auto;', $styles);
        $this->assertStringContainsString('padding-top: 0 !important;', $styles);
        $this->assertStringContainsString('ProductContentBlockComponent', $productEditor);
        $this->assertStringContainsString('moveBlock', $articleEditor);
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

    /** @return array<int, array<string, mixed>> */
    private function schemaDocuments(string $html): array
    {
        preg_match_all(
            '~<script type="application/ld\+json">(.*?)</script>~s',
            $html,
            $matches,
        );

        return collect($matches[1] ?? [])
            ->map(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR))
            ->all();
    }
}
