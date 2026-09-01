<?php

namespace Tests\Feature;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\Models\HomePageConfig;
use App\Models\ProductField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_complete_indexing_social_and_semantic_metadata(): void
    {
        $this->createHomePageConfig([
            'meta_title' => [
                'uk' => 'Двері Bona в Одесі',
                'ru' => 'Двери Bona в Одессе',
            ],
            'meta_description' => [
                'uk' => 'Український опис головної сторінки.',
                'ru' => 'Русское описание главной страницы.',
            ],
            'meta_keywords' => [
                'uk' => 'двері, Одеса',
                'ru' => 'двери, Одесса',
            ],
            // Legacy free-form tags must not be able to duplicate the
            // application-owned canonical, robots or social metadata.
            'meta_tags' => '<meta property="og:title" content="Legacy title">',
        ]);

        $response = $this->get(route('store.home'));
        $origin = rtrim(config('app.url'), '/');

        $response->assertOk()
            ->assertSee('<html lang="uk">', false)
            ->assertSee('<main id="main-content">', false)
            ->assertSee('<title>Двері Bona в Одесі</title>', false)
            ->assertSee('<meta name="description" content="Український опис головної сторінки.">', false)
            ->assertSee('<link rel="canonical" href="'.$origin.'">', false)
            ->assertSee('<link rel="alternate" hreflang="uk-UA" href="'.$origin.'">', false)
            ->assertSee('<link rel="alternate" hreflang="ru-UA" href="'.$origin.'/ru">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="'.$origin.'">', false)
            ->assertSee('<meta property="og:locale" content="uk_UA">', false)
            ->assertSee('<meta property="og:site_name" content="Bona Doors">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('max-image-preview:large', false)
            ->assertDontSee('maximum-scale=1', false)
            ->assertDontSee('Legacy title', false);

        $schema = $this->schemaDocuments($response->getContent());
        $homeGraph = collect($schema)->first(fn (array $document) => isset($document['@graph'])
            && collect($document['@graph'])->contains(fn (array $node) => ($node['@type'] ?? null) === 'WebSite'));

        $this->assertNotNull($homeGraph);
        $this->assertSame('https://schema.org', $homeGraph['@context']);
        $this->assertTrue(collect($homeGraph['@graph'])->contains(
            fn (array $node) => ($node['@type'] ?? null) === 'WebPage'
                && ($node['inLanguage'] ?? null) === 'uk-UA'
        ));

        $organizationGraph = collect($schema)->first(fn (array $document) => isset($document['@graph'])
            && collect($document['@graph'])->contains(fn (array $node) => ($node['@type'] ?? null) === 'Organization'));

        $this->assertNotNull($organizationGraph);
        $this->assertCount(2, collect($organizationGraph['@graph'])->where('@type', 'HomeGoodsStore'));
    }

    public function test_russian_homepage_has_reciprocal_hreflang_and_its_own_webpage_schema(): void
    {
        $this->createHomePageConfig([
            'meta_title' => ['uk' => 'Українська', 'ru' => 'Русская'],
            'meta_description' => ['uk' => 'Опис', 'ru' => 'Описание'],
        ]);

        $response = $this->get(route('localized.store.home', ['lang' => 'ru']));
        $origin = rtrim(config('app.url'), '/');

        $response->assertOk()
            ->assertSee('<html lang="ru">', false)
            ->assertSee('bona-site-header--overlay', false)
            ->assertSee('<link rel="canonical" href="'.$origin.'/ru">', false)
            ->assertSee('<link rel="alternate" hreflang="uk-UA" href="'.$origin.'">', false)
            ->assertSee('<link rel="alternate" hreflang="ru-UA" href="'.$origin.'/ru">', false)
            ->assertDontSee($origin.'/ru/ru', false)
            ->assertSee('<meta property="og:locale" content="ru_UA">', false);

        $schema = $this->schemaDocuments($response->getContent());
        $this->assertTrue(collect($schema)->contains(function (array $document) {
            return isset($document['@graph']) && collect($document['@graph'])->contains(
                fn (array $node) => ($node['@type'] ?? null) === 'WebPage'
                    && ($node['url'] ?? null) === rtrim(config('app.url'), '/').'/ru'
                    && ($node['inLanguage'] ?? null) === 'ru-UA'
            );
        }));
    }

    public function test_new_asset_build_invalidates_cached_homepage_html(): void
    {
        $this->ensureAssetManifest();

        $config = $this->createHomePageConfig([
            'meta_title' => ['uk' => 'Кешована головна', 'ru' => 'Кешированная главная'],
            'meta_description' => ['uk' => 'Опис', 'ru' => 'Описание'],
        ]);
        $config->timestamps = false;
        $config->updated_at = '2020-01-01 00:00:00';
        $config->save();

        $response = $this->withHeader('If-Modified-Since', 'Wed, 01 Jan 2020 00:00:00 GMT')
            ->get(route('store.home'));

        $response->assertOk();
        $this->assertGreaterThan(
            strtotime('2020-01-01 00:00:00 UTC'),
            strtotime((string) $response->headers->get('Last-Modified')),
        );
    }

    public function test_sitemap_and_robots_are_crawlable_documents_with_correct_content_types(): void
    {
        $sitemap = $this->get('/sitemap.xml');
        $origin = rtrim(config('app.url'), '/');

        $sitemap->assertOk();
        $this->assertStringStartsWith('application/xml', (string) $sitemap->headers->get('Content-Type'));
        $this->assertStringContainsString('public', (string) $sitemap->headers->get('Cache-Control'));
        $sitemap->assertHeaderMissing('Set-Cookie');
        $sitemap->assertSee('<loc>'.$origin.'</loc>', false)
            ->assertSee('hreflang="uk-UA" href="'.$origin.'"', false)
            ->assertSee('hreflang="ru-UA" href="'.$origin.'/ru"', false)
            ->assertSee('hreflang="x-default" href="'.$origin.'"', false);

        $robots = $this->get('/robots.txt');

        $robots->assertOk();
        $this->assertStringStartsWith('text/plain', (string) $robots->headers->get('Content-Type'));
        $this->assertStringContainsString('public', (string) $robots->headers->get('Cache-Control'));
        $robots->assertHeaderMissing('Set-Cookie');
        $robots->assertSee('User-agent: *')
            ->assertSee('Sitemap: '.$origin.'/sitemap.xml');

        $llms = $this->get('/llms.txt');

        $llms->assertOk()->assertHeaderMissing('Set-Cookie');
        $this->assertStringStartsWith('text/plain', (string) $llms->headers->get('Content-Type'));
        $this->assertStringContainsString('public', (string) $llms->headers->get('Cache-Control'));
    }

    private function createHomePageConfig(array $attributes): HomePageConfig
    {
        $author = User::factory()->create();
        $field = ProductField::query()->create([
            'creator_id' => $author->id,
            'field_name' => ['uk' => 'SEO', 'ru' => 'SEO'],
            'slug' => 'seo-test-field-'.uniqid(),
            'field_type_id' => ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION,
        ]);

        return HomePageConfig::query()->create([
            'slider_title' => ['uk' => '', 'ru' => ''],
            'slider_logo_image_path' => '',
            'product_field_id' => $field->id,
            'product_types' => '[]',
            ...$attributes,
        ]);
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

    /**
     * The middleware dates the page by the build manifest, so the test has to
     * make sure one exists. A checkout that has not run `npm run build` has no
     * public/build at all, and the page then answers 304 quite correctly --
     * which failed this test on CI while it passed on any machine that had
     * built the assets at some point.
     */
    private function ensureAssetManifest(): void
    {
        $path = public_path('build/manifest.json');

        if (file_exists($path)) {
            return;
        }

        $directory = dirname($path);
        $directoryWasMissing = ! is_dir($directory);

        if ($directoryWasMissing) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, '{}');

        // Leave the working tree as it was found.
        $this->beforeApplicationDestroyed(function () use ($path, $directory, $directoryWasMissing) {
            @unlink($path);

            if ($directoryWasMissing) {
                @rmdir($directory);
            }
        });
    }
}
