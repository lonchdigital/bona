<?php

namespace App\Services\BlogArticle;

use App\DataClasses\BlogArticleBlockTypesDataClass;
use App\Models\BlogArticle;
use App\Models\BlogArticleBlock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BlogArticleContentImporter
{
    /** @return array{articles: int, missing: list<string>} */
    public function importFile(string $path): array
    {
        $entries = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($entries) || ! array_is_list($entries)) {
            throw new InvalidArgumentException("{$path} must contain a list of blog articles.");
        }

        $updated = 0;
        $missing = [];

        foreach ($entries as $entry) {
            $ukSlug = trim((string) ($entry['uk_slug'] ?? ''));
            $article = BlogArticle::query()
                ->where('slug', $ukSlug)
                ->orWhere('slugs->uk', $ukSlug)
                ->first();

            if ($ukSlug === '' || $article === null) {
                $missing[] = $ukSlug !== '' ? $ukSlug : '(missing uk_slug)';

                continue;
            }

            if (DB::transaction(fn (): bool => $this->syncArticle($article, $entry['ru'] ?? []))) {
                $updated++;
            }
        }

        return ['articles' => $updated, 'missing' => $missing];
    }

    private function syncArticle(BlogArticle $article, array $content): bool
    {
        if ($content === []) {
            return false;
        }

        $changed = $this->syncArticleFields($article, $content);
        $changed = $this->syncTextBlock($article, $content) || $changed;
        $changed = $this->syncFaqBlock($article, $content) || $changed;

        if ($changed) {
            DB::table('blog_articles')->where('id', $article->id)->update(['updated_at' => now()]);
        }

        return $changed;
    }

    private function syncArticleFields(BlogArticle $article, array $content): bool
    {
        $changed = false;
        $slug = trim((string) ($content['slug'] ?? ''));
        $slugs = $article->localizedSlugs();

        if ($slug !== '' && blank($slugs['ru'] ?? null)) {
            $conflict = BlogArticle::query()
                ->where('id', '<>', $article->id)
                ->where(function ($query) use ($slug): void {
                    $query->where('slug', $slug)->orWhere('slugs->ru', $slug);
                })
                ->exists();

            if ($conflict) {
                throw new InvalidArgumentException("Russian blog slug is already used: {$slug}");
            }

            $article->setTranslation('slugs', 'ru', $slug);
            $changed = true;
        }

        foreach (['name', 'preview_text', 'meta_title', 'meta_description', 'meta_keywords'] as $field) {
            $value = trim((string) ($content[$field] ?? ''));
            $stored = trim((string) $article->getTranslation($field, 'ru', false));

            if ($value !== '' && $stored === '') {
                $article->setTranslation($field, 'ru', $value);
                $changed = true;
            }
        }

        if ($changed) {
            $article->saveQuietly();
        }

        return $changed;
    }

    private function syncTextBlock(BlogArticle $article, array $content): bool
    {
        $html = $this->html($content['content'] ?? '');
        if ($html === '') {
            return false;
        }

        $block = $article->blocks()
            ->where('type_id', BlogArticleBlockTypesDataClass::TYPE_TEXT)
            ->first();
        if ($block === null) {
            throw new InvalidArgumentException("Article {$article->slug} has no text block.");
        }

        $blockContent = $block->content;
        if (filled(strip_tags((string) ($blockContent['ru'] ?? '')))) {
            return false;
        }

        $blockContent['ru'] = $html;
        $block->update(['content' => $blockContent]);

        return true;
    }

    private function syncFaqBlock(BlogArticle $article, array $content): bool
    {
        $faqs = collect($content['faqs'] ?? [])
            ->map(fn (array $faq): array => [
                'question' => trim((string) ($faq['question'] ?? '')),
                'answer' => trim((string) ($faq['answer'] ?? '')),
            ])
            ->filter(fn (array $faq): bool => $faq['question'] !== '' && $faq['answer'] !== '')
            ->values();
        if ($faqs->isEmpty()) {
            return false;
        }

        $block = $article->blocks()
            ->where('type_id', BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS)
            ->first();
        $blockContent = $block?->content ?? ['questions' => []];
        $questions = collect($blockContent['questions'] ?? [])->values();
        $changed = false;

        foreach ($faqs as $index => $faq) {
            $question = $questions->get($index, [
                'question' => ['uk' => ''],
                'answer' => ['uk' => ''],
            ]);

            foreach (['question', 'answer'] as $field) {
                if (blank(data_get($question, "{$field}.ru"))) {
                    data_set($question, "{$field}.ru", $faq[$field]);
                    $changed = true;
                }
            }

            $questions->put($index, $question);
        }

        if (! $changed) {
            return false;
        }

        $blockContent['questions'] = $questions->all();
        if ($block) {
            $block->update(['content' => $blockContent]);
        } else {
            BlogArticleBlock::query()->create([
                'blog_article_id' => $article->id,
                'type_id' => BlogArticleBlockTypesDataClass::TYPE_QUESTIONS_AND_ANSWERS,
                'content' => $blockContent,
            ]);
        }

        return true;
    }

    private function html(mixed $content): string
    {
        if (is_array($content)) {
            return trim(implode('', array_map('strval', $content)));
        }

        return trim((string) $content);
    }
}
