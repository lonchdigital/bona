<?php

namespace Tests\Feature;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Models\BlogArticle;
use App\Models\BlogArticleBlock;
use App\Services\BlogArticle\BlogArticleContentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class BlogArticleContentBatchThreeTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_importer_adds_third_russian_blog_batch_and_preserves_ukrainian_copy(): void
    {
        $estet = $this->article('estet-doors-oglyad-brendu', 'Огляд Estet Doors');
        $porta = $this->article(
            'dveri-verona-porta-nova-ohliad-kolektsii-ta-dyzainu',
            'Огляд Verona Porta Nova',
        );

        $importer = app(BlogArticleContentImporter::class);
        $path = database_path('content/blog/2026_09_26_blog_ru_batch_03.json');

        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(['articles' => 2, 'missing' => []], $first);
        $this->assertSame(['articles' => 0, 'missing' => []], $second);

        foreach ([$estet, $porta] as $article) {
            $article->refresh();
            $this->assertNotSame('', $article->getTranslation('name', 'ru', false));
            $this->assertNotSame($article->slugForLocale('uk'), $article->slugForLocale('ru'));
            $this->assertLessThanOrEqual(65, mb_strlen($article->getTranslation('meta_title', 'ru', false)));
            $this->assertGreaterThanOrEqual(130, mb_strlen($article->getTranslation('meta_description', 'ru', false)));
            $this->assertLessThanOrEqual(165, mb_strlen($article->getTranslation('meta_description', 'ru', false)));

            $text = $article->blocks()
                ->where('type_id', BlogArticleBlockTypesDataClass::TYPE_TEXT)
                ->firstOrFail();
            $this->assertSame('<p>Оригінальний український текст.</p>', $text->content['uk']);
            $this->assertNotSame('<p>Оригінальний український текст.</p>', $text->content['ru']);
            $this->assertGreaterThanOrEqual(800, $this->wordCount($text->content['ru']));

            $faq = $article->blocks()
                ->where('type_id', BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS)
                ->firstOrFail();
            $this->assertCount(5, $faq->content['questions']);
            foreach ($faq->content['questions'] as $question) {
                $this->assertNotEmpty(data_get($question, 'question.ru'));
                $this->assertNotEmpty(data_get($question, 'answer.ru'));
            }
        }
    }

    public function test_third_batch_renders_localized_metadata_body_and_faq_schema(): void
    {
        $estet = $this->article('estet-doors-oglyad-brendu', 'Огляд Estet Doors');
        $porta = $this->article(
            'dveri-verona-porta-nova-ohliad-kolektsii-ta-dyzainu',
            'Огляд Verona Porta Nova',
        );

        app(BlogArticleContentImporter::class)->importFile(
            database_path('content/blog/2026_09_26_blog_ru_batch_03.json'),
        );

        $this->get('/ru/blog/'.$estet->fresh()->slugForLocale('ru'))
            ->assertOk()
            ->assertSee('Двери Estet Doors: обзор бренда и модельного ряда')
            ->assertSee('Серии Grand и МК в каталоге Bona Doors')
            ->assertSee('Где производят двери Estet Doors?')
            ->assertSee('Двери Estet Doors: обзор и выбор | Bona Doors')
            ->assertSee('hreflang="uk-UA"', false)
            ->assertSee('hreflang="ru-UA"', false)
            ->assertSee('"@type":"FAQPage"', false);

        $this->get('/ru/blog/'.$porta->fresh()->slugForLocale('ru'))
            ->assertOk()
            ->assertSee('Двери PORTA NOVA и декор Verona: обзор коллекции')
            ->assertSee('PORTA NOVA и Walnut Verona 2: правильные названия')
            ->assertSee('PORTA NOVA и Verona являются одним брендом?')
            ->assertSee('"@type":"FAQPage"', false);

        app()->setLocale('uk');
        $this->get('/blog/estet-doors-oglyad-brendu')
            ->assertOk()
            ->assertSee('Оригінальний український текст.')
            ->assertDontSee('Серии Grand и МК в каталоге Bona Doors');
    }

    private function article(string $slug, string $name): BlogArticle
    {
        $article = BlogArticle::query()->create([
            'creator_id' => $this->author()->id,
            'name' => ['uk' => $name],
            'slug' => $slug,
            'slugs' => ['uk' => $slug],
            'preview_text' => ['uk' => 'Український анонс.'],
            'hero_image_path' => 'blog/test.webp',
            'meta_title' => ['uk' => $name.' | Bona Doors'],
            'meta_description' => ['uk' => 'Український опис статті для тесту.'],
            'meta_keywords' => ['uk' => 'двері'],
        ]);

        BlogArticleBlock::query()->create([
            'blog_article_id' => $article->id,
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_TEXT,
            'content' => [
                'uk' => '<p>Оригінальний український текст.</p>',
                'ru' => '<p>Оригінальний український текст.</p>',
            ],
        ]);

        return $article;
    }

    private function wordCount(string $html): int
    {
        return count(preg_split(
            '/\s+/u',
            trim(html_entity_decode(strip_tags($html))),
            -1,
            PREG_SPLIT_NO_EMPTY,
        ));
    }
}
