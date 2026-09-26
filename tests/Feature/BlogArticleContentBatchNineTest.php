<?php

namespace Tests\Feature;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Models\BlogArticle;
use App\Models\BlogArticleBlock;
use App\Services\BlogArticle\BlogArticleContentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class BlogArticleContentBatchNineTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_importer_adds_ninth_russian_blog_batch_and_preserves_ukrainian_copy(): void
    {
        $quattro = $this->article('kolekciya-bona-quattro-inter-yer', 'Колекція Bona Quattro');
        $guide = $this->article('vhidni-dveri-gid', 'Гід з вибору вхідних дверей');

        $importer = app(BlogArticleContentImporter::class);
        $path = database_path('content/blog/2026_09_26_blog_ru_batch_09.json');

        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(['articles' => 2, 'missing' => []], $first);
        $this->assertSame(['articles' => 0, 'missing' => []], $second);

        foreach ([$quattro, $guide] as $article) {
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

    public function test_ninth_batch_renders_localized_metadata_body_and_faq_schema(): void
    {
        $quattro = $this->article('kolekciya-bona-quattro-inter-yer', 'Колекція Bona Quattro');
        $guide = $this->article('vhidni-dveri-gid', 'Гід з вибору вхідних дверей');

        app(BlogArticleContentImporter::class)->importFile(
            database_path('content/blog/2026_09_26_blog_ru_batch_09.json'),
        );

        $this->get('/ru/blog/'.$quattro->fresh()->slugForLocale('ru'))
            ->assertOk()
            ->assertSee('Ручка DnD Quattro 02: комплектация и покрытия')
            ->assertSee('Три комплектации: какую выбрать')
            ->assertSee('Quattro 02 является коллекцией дверей Bona?')
            ->assertSee('Ручка DnD Quattro 02: как выбрать | Bona Doors')
            ->assertSee('hreflang="uk-UA"', false)
            ->assertSee('hreflang="ru-UA"', false)
            ->assertSee('"@type":"FAQPage"', false);

        $this->get('/ru/blog/'.$guide->fresh()->slugForLocale('ru'))
            ->assertOk()
            ->assertSee('Как выбрать входную дверь для квартиры или дома')
            ->assertSee('Как читать характеристики конструкции')
            ->assertSee('Чем входная дверь для квартиры отличается от уличной?')
            ->assertSee('"@type":"FAQPage"', false);

        app()->setLocale('uk');
        $this->get('/blog/kolekciya-bona-quattro-inter-yer')
            ->assertOk()
            ->assertSee('Оригінальний український текст.')
            ->assertDontSee('Три комплектации: какую выбрать');
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
