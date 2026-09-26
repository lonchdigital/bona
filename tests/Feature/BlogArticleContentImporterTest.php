<?php

namespace Tests\Feature;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Models\BlogArticle;
use App\Models\BlogArticleBlock;
use App\Services\BlogArticle\BlogArticleContentImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class BlogArticleContentImporterTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_importer_adds_complete_russian_versions_without_overwriting_ukrainian_copy(): void
    {
        $guide = $this->article('mizhkimnatni-dveri-gid', 'Гід із вибору дверей');
        $danapris = $this->article('dveri-danapris-oglyad-brendu', 'Огляд Danapris');

        BlogArticleBlock::query()->create([
            'blog_article_id' => $guide->id,
            'type_id' => BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS,
            'content' => ['questions' => collect(range(1, 3))->map(fn (int $index): array => [
                'question' => ['uk' => "Питання {$index}?", 'ru' => "Питання {$index}?"],
                'answer' => ['uk' => "Відповідь {$index}.", 'ru' => "Відповідь {$index}."],
            ])->all()],
        ]);

        $importer = app(BlogArticleContentImporter::class);
        $path = database_path('content/blog/2026_09_26_blog_ru_batch_01.json');

        $first = $importer->importFile($path);
        $second = $importer->importFile($path);

        $this->assertSame(['articles' => 2, 'missing' => []], $first);
        $this->assertSame(['articles' => 0, 'missing' => []], $second);

        foreach ([$guide, $danapris] as $article) {
            $article->refresh();
            $this->assertNotSame('', $article->getTranslation('name', 'ru', false));
            $this->assertNotSame('', $article->slugForLocale('ru'));
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

        $this->assertSame('Питання 1?', data_get(
            $guide->blocks()
                ->where('type_id', BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS)
                ->firstOrFail()
                ->content,
            'questions.0.question.uk',
        ));
    }

    public function test_imported_russian_articles_render_with_localized_metadata_body_and_faq_schema(): void
    {
        $guide = $this->article('mizhkimnatni-dveri-gid', 'Гід із вибору дверей');
        $danapris = $this->article('dveri-danapris-oglyad-brendu', 'Огляд Danapris');

        app(BlogArticleContentImporter::class)->importFile(
            database_path('content/blog/2026_09_26_blog_ru_batch_01.json'),
        );

        $this->get('/ru/blog/'.$guide->fresh()->slugForLocale('ru'))
            ->assertOk()
            ->assertSee('Как выбрать межкомнатные двери: практическое руководство')
            ->assertSee('Почему профессиональный замер нужен до заказа')
            ->assertSee('Какие данные нужны перед заказом межкомнатных дверей?')
            ->assertSee('Как выбрать межкомнатные двери: гид | Bona Doors')
            ->assertSee('hreflang="uk-UA"', false)
            ->assertSee('hreflang="ru-UA"', false)
            ->assertSee('"@type":"FAQPage"', false);

        $this->get('/ru/blog/'.$danapris->fresh()->slugForLocale('ru'))
            ->assertOk()
            ->assertSee('Двери Danapris: обзор бренда, коллекций и технологий')
            ->assertSee('Что известно о производстве Danapris')
            ->assertSee('Где производят двери Danapris?')
            ->assertSee('"@type":"FAQPage"', false);

        app()->setLocale('uk');
        $this->get('/blog/mizhkimnatni-dveri-gid')
            ->assertOk()
            ->assertSee('Оригінальний український текст.')
            ->assertDontSee('Почему профессиональный замер нужен до заказа');
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
