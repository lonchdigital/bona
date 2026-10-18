<?php

namespace Tests\Feature;

use App\Jobs\RegenerateSitemapJob;
use App\Models\BlogArticle;
use App\Models\BlogArticleRetirement;
use App\Models\Role;
use App\Models\User;
use App\Services\Sitemap\SitemapService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The "article.retire" event: one language version of a Serp Agent article
 * leaves the site and its address answers 301 (or 410) from then on.
 */
class SerpAgentArticleRetirementTest extends TestCase
{
    use RefreshDatabase;

    private const UK_SLUG = 'dveri-amega-ohliad-brendu';

    private const RU_SLUG = 'dveri-amega-obzor-brenda';

    /** What the target page answers when the site checks it is alive. */
    private int $targetStatus = 200;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('serp-agent.secret', 'test-serp-secret');
        config()->set('serp-agent.default_hero_image', 'defaults/serp-cover.webp');

        Storage::fake(config('app.images_disk_default'));
        Storage::disk(config('app.images_disk_default'))->put('defaults/serp-cover.webp', 'image');

        DB::table('roles')->insertOrIgnore(['id' => Role::ADMIN_ROLE_ID, 'role' => 'Admin', 'role_slug' => 'admin']);
        User::factory()->create()->update(['role_id' => Role::ADMIN_ROLE_ID]);

        Bus::fake([RegenerateSitemapJob::class]);
        Http::preventStrayRequests();
        Http::fake(fn () => Http::response('', $this->targetStatus));
    }

    public function test_a_test_delivery_checks_everything_and_changes_nothing(): void
    {
        $this->publishPair();

        $this->retire(['test' => true])
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'status' => 'test',
                'url' => url('/blog/'.self::UK_SLUG),
                'redirectTo' => url('/product-category/interior-doors'),
            ]);

        $this->assertSame(0, BlogArticleRetirement::count());
        $this->get('/blog/'.self::UK_SLUG)->assertOk();
        Bus::assertNotDispatchedSync(RegenerateSitemapJob::class);
    }

    public function test_a_retired_version_redirects_permanently_and_leaves_every_listing(): void
    {
        $this->publishPair();

        $this->retire()
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'status' => 'redirected',
                'url' => url('/blog/'.self::UK_SLUG),
                'redirectTo' => url('/product-category/interior-doors'),
            ]);

        $this->get('/blog/'.self::UK_SLUG)
            ->assertStatus(301)
            ->assertRedirect(url('/product-category/interior-doors'));
        $this->getRaw('/blog/'.self::UK_SLUG.'/')
            ->assertStatus(301)
            ->assertRedirect(url('/product-category/interior-doors'));
        $this->get('/blog/article/'.self::UK_SLUG)
            ->assertRedirect(url('/product-category/interior-doors'));

        $this->get(route('blog.main.page'))->assertOk()->assertDontSee('Двері Amega: огляд бренду');
        $this->get(route('store.home'))->assertOk()->assertDontSee('/blog/'.self::UK_SLUG, false);

        $sitemap = app(SitemapService::class)->buildSitemap()->render();
        $this->assertStringNotContainsString('/blog/'.self::UK_SLUG, $sitemap);
        $this->assertStringContainsString('/ru/blog/'.self::RU_SLUG, $sitemap);

        // The Russian translation stays, without a language link to the retired version.
        $this->get('/ru/blog/'.self::RU_SLUG)
            ->assertOk()
            ->assertSee('hreflang="ru-UA"', false)
            ->assertDontSee('hreflang="uk-UA"', false)
            ->assertDontSee('/blog/'.self::UK_SLUG.'"', false);

        // Taken off the site, not deleted.
        $this->assertSame('Двері Amega: огляд бренду', BlogArticle::firstOrFail()->getTranslation('name', 'uk'));
        Bus::assertDispatchedSync(RegenerateSitemapJob::class);
    }

    public function test_without_a_target_the_old_address_is_gone(): void
    {
        $this->publishPair();

        $this->retire(['redirectTo' => null])
            ->assertOk()
            ->assertJsonPath('status', 'gone')
            ->assertJsonPath('redirectTo', null);

        $this->get('/blog/'.self::UK_SLUG)->assertStatus(410);
        $this->getRaw('/blog/'.self::UK_SLUG.'/')->assertStatus(410);
    }

    public function test_a_repeated_delivery_or_an_already_retired_version_changes_nothing(): void
    {
        $this->publishPair();
        $this->retire()->assertOk()->assertJsonPath('status', 'redirected');

        $this->retire()->assertOk()->assertJsonPath('status', 'already');
        $this->retire(['deliveryId' => 'retire:another', 'redirectTo' => url('/blog')])
            ->assertOk()
            ->assertJsonPath('status', 'already')
            ->assertJsonPath('redirectTo', url('/product-category/interior-doors'));

        $this->assertSame(1, BlogArticleRetirement::count());
    }

    public function test_a_wrong_key_is_refused(): void
    {
        $this->publishPair();

        $this->withHeader('Authorization', 'Bearer wrong')
            ->postJson(route('api.serp-agent.articles.store'), $this->payload())
            ->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->assertSame(0, BlogArticleRetirement::count());
    }

    public function test_only_articles_published_through_serp_agent_can_be_retired(): void
    {
        $this->retire(['slug' => 'no-such-article', 'url' => url('/blog/no-such-article')])
            ->assertNotFound()
            ->assertJsonPath('success', false);

        BlogArticle::create([
            'creator_id' => User::first()->id,
            'name' => ['uk' => 'Редакційна стаття'],
            'preview_text' => ['uk' => 'Опис.'],
            'slug' => 'redaktsiina-stattia',
            'hero_image_path' => 'blog/test.webp',
        ]);

        $this->retire(['slug' => 'redaktsiina-stattia', 'url' => url('/blog/redaktsiina-stattia')])
            ->assertNotFound();

        $this->get('/blog/redaktsiina-stattia')->assertOk();
    }

    public function test_a_target_off_the_site_on_itself_or_on_a_retired_article_is_refused(): void
    {
        $this->publishPair();
        $this->publish('uk', 'insha-stattia', 'Інша стаття', 'group-2');
        $this->retire([
            'deliveryId' => 'retire:other',
            'slug' => 'insha-stattia',
            'url' => url('/blog/insha-stattia'),
        ])->assertOk();

        foreach ([
            'https://example.com/doors' => 'інший домен',
            url('/blog/'.self::UK_SLUG).'/' => 'саму статтю',
            url('/blog/insha-stattia') => 'іншу прибрану статтю',
        ] as $target => $reason) {
            $this->retire(['redirectTo' => $target])
                ->assertUnprocessable()
                ->assertJsonPath('success', false)
                ->assertJson(fn ($json) => $json->where('message', fn ($message) => str_contains($message, $reason))->etc());
        }

        $this->get('/blog/'.self::UK_SLUG)->assertOk();
    }

    public function test_a_target_that_does_not_open_is_refused(): void
    {
        $this->publishPair();
        $this->targetStatus = 404;

        $this->retire(['redirectTo' => url('/product-category/missing')])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'redirectTo не відкривається як жива сторінка: відповідає кодом 404.');

        $this->assertSame(0, BlogArticleRetirement::count());
    }

    public function test_a_missing_redirect_field_is_not_taken_for_a_410(): void
    {
        $this->publishPair();
        $payload = $this->payload();
        unset($payload['redirectTo']);

        $this->withHeader('Authorization', 'Bearer test-serp-secret')
            ->postJson(route('api.serp-agent.articles.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertSame(0, BlogArticleRetirement::count());
    }

    public function test_a_new_publication_brings_the_version_back(): void
    {
        $this->publishPair();
        $this->retire()->assertOk();

        $this->publish('uk', self::UK_SLUG, 'Двері Amega: огляд бренду', 'group-1')->assertOk();

        $this->get('/blog/'.self::UK_SLUG)->assertOk();
        $this->get(route('blog.main.page'))->assertSee('Двері Amega: огляд бренду');
        $this->assertNotNull(BlogArticleRetirement::first()->restored_at);
        Bus::assertDispatchedSync(RegenerateSitemapJob::class, 2);

        // The same retirement delivered late does not take it off again.
        $this->retire()->assertOk()->assertJsonPath('status', 'already');
        $this->get('/blog/'.self::UK_SLUG)->assertOk();
    }

    public function test_an_article_is_found_by_its_address_when_the_slug_does_not_match(): void
    {
        $this->publishPair();

        $this->retire([
            'language' => 'ru',
            'slug' => self::UK_SLUG,
            'url' => url('/ru/blog/'.self::RU_SLUG),
        ])->assertOk()->assertJsonPath('url', url('/ru/blog/'.self::RU_SLUG));

        $this->get('/blog/'.self::UK_SLUG)->assertOk();
        $this->get('/ru/blog/'.self::RU_SLUG)->assertStatus(301);
    }

    public function test_a_live_article_sends_its_trailing_slash_address_to_its_own_url(): void
    {
        $this->publishPair();

        $this->getRaw('/blog/'.self::UK_SLUG.'/')
            ->assertStatus(301)
            ->assertRedirect(url('/blog/'.self::UK_SLUG));
    }

    /** The test client trims a trailing slash, so this request skips it. */
    private function getRaw(string $uri): TestResponse
    {
        $request = Request::create(rtrim(url('/'), '/').$uri);

        return TestResponse::fromBaseResponse($this->app->make(Kernel::class)->handle($request));
    }

    private function publishPair(): void
    {
        $this->publish('uk', self::UK_SLUG, 'Двері Amega: огляд бренду', 'group-1')->assertCreated();
        $this->publish('ru', self::RU_SLUG, 'Двери Amega: обзор бренда', 'group-1')->assertOk();
    }

    private function publish(string $locale, string $slug, string $title, string $group)
    {
        return $this->withHeader('Authorization', 'Bearer test-serp-secret')
            ->postJson(route('api.serp-agent.articles.store'), [
                'translationGroupId' => $group,
                'locale' => $locale,
                'title' => $title,
                'slug' => $slug,
                'content' => '<h2>Огляд</h2><p>Текст статті.</p>',
            ]);
    }

    private function retire(array $overrides = [])
    {
        return $this->withHeader('Authorization', 'Bearer test-serp-secret')
            ->postJson(route('api.serp-agent.articles.store'), $this->payload($overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'event' => 'article.retire',
            'deliveryId' => 'retire:cmtfxwd9e1zcl38s2oyi4y6y0',
            'slug' => self::UK_SLUG,
            'language' => 'uk',
            'url' => url('/blog/'.self::UK_SLUG),
            'redirectTo' => url('/product-category/interior-doors'),
            'test' => false,
        ], $overrides);
    }
}
