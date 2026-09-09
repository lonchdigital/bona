<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TRANSLATED_ARTICLE_FIELDS = [
        'name',
        'preview_text',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * Older SerpAgent deliveries copied one language into both translation
     * slots. Repair only exact duplicates whose language can be identified by
     * alphabet-specific characters; ambiguous or manually translated content
     * is deliberately left untouched.
     */
    public function up(): void
    {
        DB::table('blog_articles')
            ->where('external_source', 'serp-agent')
            ->orderBy('id')
            ->each(function (object $article): void {
                $name = $this->decodeJsonObject($article->name ?? null);

                if (! $this->hasExactMirroredValue($name)) {
                    return;
                }

                $duplicateText = [];

                foreach (self::TRANSLATED_ARTICLE_FIELDS as $field) {
                    $translations = $this->decodeJsonObject($article->{$field} ?? null);

                    if ($this->hasExactMirroredValue($translations)) {
                        $duplicateText[] = (string) $translations['uk'];
                    }
                }

                $textBlocks = DB::table('blog_article_blocks')
                    ->where('blog_article_id', $article->id)
                    ->where('type_id', 1)
                    ->get(['id', 'content']);

                foreach ($textBlocks as $block) {
                    $content = $this->decodeJsonObject($block->content);

                    if ($this->hasExactMirroredValue($content)) {
                        $duplicateText[] = (string) $content['uk'];
                    }
                }

                $sourceLocale = $this->detectLocale(implode("\n", $duplicateText));

                if ($sourceLocale === null) {
                    return;
                }

                $mirroredLocale = $sourceLocale === 'uk' ? 'ru' : 'uk';
                $articleUpdates = [];

                foreach (self::TRANSLATED_ARTICLE_FIELDS as $field) {
                    $translations = $this->decodeJsonObject($article->{$field} ?? null);

                    if (! $this->hasExactMirroredValue($translations)) {
                        continue;
                    }

                    unset($translations[$mirroredLocale]);
                    $articleUpdates[$field] = $this->encodeJson($translations);
                }

                if ($articleUpdates !== []) {
                    DB::table('blog_articles')->where('id', $article->id)->update($articleUpdates);
                }

                foreach ($textBlocks as $block) {
                    $content = $this->decodeJsonObject($block->content);

                    if (! $this->hasExactMirroredValue($content)) {
                        continue;
                    }

                    unset($content[$mirroredLocale]);

                    DB::table('blog_article_blocks')
                        ->where('id', $block->id)
                        ->update(['content' => $this->encodeJson($content)]);
                }
            }, 100);
    }

    public function down(): void
    {
        // Removed translations were generated duplicates, not real content.
        // Recreating them on rollback would restore the production bug.
    }

    /** @return array<string, mixed> */
    private function decodeJsonObject(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @param array<string, mixed> $translations */
    private function hasExactMirroredValue(array $translations): bool
    {
        if (! isset($translations['uk'], $translations['ru'])
            || ! is_string($translations['uk'])
            || ! is_string($translations['ru'])) {
            return false;
        }

        $uk = trim($translations['uk']);
        $ru = trim($translations['ru']);

        return $uk !== '' && $uk === $ru;
    }

    private function detectLocale(string $html): ?string
    {
        $text = mb_strtolower(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $ukrainianCharacters = preg_match_all('/[іїєґ]/u', $text) ?: 0;
        $russianCharacters = preg_match_all('/[ыэёъ]/u', $text) ?: 0;

        if ($ukrainianCharacters === $russianCharacters) {
            return null;
        }

        return $ukrainianCharacters > $russianCharacters ? 'uk' : 'ru';
    }

    /** @param array<string, mixed> $value */
    private function encodeJson(array $value): string
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }
};
