<?php

namespace Tests\Feature;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Models\BlogArticle;
use App\Models\BlogArticleBlock;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SerpAgentLanguageIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_article_delivery_requires_an_explicit_locale_but_connectivity_check_does_not(): void
    {
        config()->set('serp-agent.secret', 'test-serp-secret');

        $this->withHeader('Authorization', 'Bearer test-serp-secret')
            ->postJson(route('api.serp-agent.articles.store'), [
                'title' => 'Статья без языка',
                'slug' => 'article-without-language',
                'content' => '<p>Содержимое статьи.</p>',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('locale');

        $this->withHeader('Authorization', 'Bearer test-serp-secret')
            ->postJson(route('api.serp-agent.articles.store'))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_serp_agent_stores_only_the_received_language(): void
    {
        $this->configureSerpAgent();

        $this->withHeader('Authorization', 'Bearer test-serp-secret')
            ->postJson(route('api.serp-agent.articles.store'), [
                'externalId' => 'serp-ru-1',
                'locale' => 'ru-RU',
                'title' => 'Как выбрать двери',
                'slug' => 'kak-vybrat-dveri',
                'content' => '<h2>Практические советы</h2><p>Текст только на русском языке.</p>',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $article = BlogArticle::where('external_id', 'serp-ru-1')->firstOrFail();
        $textBlock = $article->blocks()
            ->where('type_id', BlogArticleBlockTypesDataClass::TYPE_TEXT)
            ->firstOrFail();

        $this->assertSame(['ru' => 'Как выбрать двери'], $article->getTranslations('name'));
        $this->assertArrayHasKey('ru', $textBlock->content);
        $this->assertArrayNotHasKey('uk', $textBlock->content);
    }

    public function test_single_language_article_is_visible_only_in_its_storefront_and_sitemap_locale(): void
    {
        $article = $this->createArticle([
            'name' => ['ru' => 'Русская статья'],
            'preview_text' => ['ru' => 'Описание только на русском.'],
            'slug' => 'russkaya-statya',
            'meta_title' => ['ru' => 'Русская статья'],
            'meta_description' => ['ru' => 'Описание только на русском.'],
            'meta_keywords' => ['ru' => 'двери'],
        ]);

        BlogArticleBlock::create([
            'blog_article_id' => $article->id,
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_TEXT,
            'content' => ['ru' => '<p>Текст только на русском языке.</p>'],
        ]);

        $this->get(route('blog.main.page'))
            ->assertOk()
            ->assertDontSee('Русская статья');

        $this->get(route('blog.article.page', ['blogArticleSlug' => $article->slug]))
            ->assertNotFound();

        $this->get(route('localized.blog.main.page', ['lang' => 'ru']))
            ->assertOk()
            ->assertSee('Русская статья');

        $this->get(route('localized.blog.article.page', ['lang' => 'ru', 'blogArticleSlug' => $article->slug]))
            ->assertOk()
            ->assertSee('Текст только на русском языке.')
            ->assertSee('hreflang="ru-UA"', false)
            ->assertSee('hreflang="x-default"', false)
            ->assertDontSee('hreflang="uk-UA"', false)
            ->assertDontSee('property="og:locale:alternate"', false);

        $sitemapUrls = $article->toSitemapTag();

        $this->assertCount(1, $sitemapUrls);
        $this->assertStringContainsString('/ru/blog/russkaya-statya', $sitemapUrls[0]);
    }

    public function test_cleanup_migration_separates_only_exact_serp_agent_mirrors(): void
    {
        $russian = $this->createArticle([
            'external_source' => 'serp-agent',
            'name' => ['uk' => 'Как выбрать двери', 'ru' => 'Как выбрать двери'],
            'preview_text' => ['uk' => 'Практические советы для покупателей.', 'ru' => 'Практические советы для покупателей.'],
            'slug' => 'mirrored-russian',
        ]);
        $ukrainian = $this->createArticle([
            'external_source' => 'serp-agent',
            'name' => ['uk' => 'Як вибрати двері', 'ru' => 'Як вибрати двері'],
            'preview_text' => ['uk' => 'Практичні поради для покупців.', 'ru' => 'Практичні поради для покупців.'],
            'slug' => 'mirrored-ukrainian',
        ]);
        $translated = $this->createArticle([
            'external_source' => 'serp-agent',
            'name' => ['uk' => 'Як вибрати двері', 'ru' => 'Как выбрать двери'],
            'preview_text' => ['uk' => 'Поради.', 'ru' => 'Советы.'],
            'slug' => 'real-translations',
        ]);

        $russianBlock = BlogArticleBlock::create([
            'blog_article_id' => $russian->id,
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_TEXT,
            'content' => [
                'uk' => '<p>Полезный текст о выборе и эксплуатации дверей.</p>',
                'ru' => '<p>Полезный текст о выборе и эксплуатации дверей.</p>',
            ],
        ]);
        $ukrainianBlock = BlogArticleBlock::create([
            'blog_article_id' => $ukrainian->id,
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_TEXT,
            'content' => [
                'uk' => '<p>Корисний текст про вибір і використання дверей.</p>',
                'ru' => '<p>Корисний текст про вибір і використання дверей.</p>',
            ],
        ]);

        $migration = require database_path('migrations/2026_09_10_090000_separate_mirrored_serp_agent_articles.php');
        $migration->up();

        $this->assertSame(['ru' => 'Как выбрать двери'], $russian->fresh()->getTranslations('name'));
        $this->assertSame(['uk' => 'Як вибрати двері'], $ukrainian->fresh()->getTranslations('name'));
        $this->assertEqualsCanonicalizing(
            ['uk' => 'Як вибрати двері', 'ru' => 'Как выбрать двери'],
            $translated->fresh()->getTranslations('name'),
        );
        $this->assertArrayNotHasKey('uk', $russianBlock->fresh()->content);
        $this->assertArrayHasKey('ru', $russianBlock->fresh()->content);
        $this->assertArrayHasKey('uk', $ukrainianBlock->fresh()->content);
        $this->assertArrayNotHasKey('ru', $ukrainianBlock->fresh()->content);
    }

    public function test_blog_text_editor_exposes_an_html_source_toggle_only_for_article_blocks(): void
    {
        $editor = file_get_contents(resource_path('js/admin/components/MultiLanguageRichTextEditorComponent.vue'));
        $articleBlock = file_get_contents(resource_path('js/admin/components/blogArticleBlockComponents/MultiLanguageRichTextEditorBlockComponent.vue'));

        $this->assertStringContainsString('allowHtmlSource', $editor);
        $this->assertStringContainsString('toggleSourceMode', $editor);
        $this->assertStringContainsString('<textarea', $editor);
        $this->assertStringContainsString(':allow-html-source="true"', $articleBlock);
    }

    private function configureSerpAgent(): void
    {
        config()->set('serp-agent.secret', 'test-serp-secret');
        config()->set('serp-agent.default_hero_image', 'defaults/serp-cover.webp');

        Storage::fake(config('app.images_disk_default'));
        Storage::disk(config('app.images_disk_default'))->put('defaults/serp-cover.webp', 'image');

        DB::table('roles')->insertOrIgnore([
            'id' => Role::ADMIN_ROLE_ID,
            'role' => 'Admin',
            'role_slug' => 'admin',
        ]);

        $admin = User::factory()->create();
        $admin->update(['role_id' => Role::ADMIN_ROLE_ID]);
    }

    private function createArticle(array $overrides = []): BlogArticle
    {
        return BlogArticle::create(array_replace([
            'creator_id' => User::factory()->create()->id,
            'name' => ['uk' => 'Тестова стаття', 'ru' => 'Тестовая статья'],
            'preview_text' => ['uk' => 'Опис.', 'ru' => 'Описание.'],
            'slug' => 'article-'.uniqid(),
            'hero_image_path' => 'blog/test.webp',
            'meta_title' => ['uk' => '', 'ru' => ''],
            'meta_description' => ['uk' => '', 'ru' => ''],
            'meta_keywords' => ['uk' => '', 'ru' => ''],
        ], $overrides));
    }
}
