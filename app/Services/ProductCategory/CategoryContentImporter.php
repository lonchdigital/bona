<?php

namespace App\Services\ProductCategory;

use App\Models\Category;
use App\Models\SeoText;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Imports prepared category copy into the same records that managers edit in
 * the admin. Existing copy is preserved unless an entry explicitly opts in to
 * replacement, so rerunning a release cannot erase later manual improvements.
 */
class CategoryContentImporter
{
    private const LOCALES = ['uk', 'ru'];

    /**
     * @return array{categories: int, missing: list<string>}
     */
    public function importFile(string $path): array
    {
        $entries = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($entries)) {
            throw new InvalidArgumentException("{$path} must contain a list of categories.");
        }

        $updated = 0;
        $missing = [];

        foreach ($entries as $entry) {
            $slug = (string) ($entry['slug'] ?? '');
            $category = Category::query()->where('slug', $slug)->first();
            if ($category === null) {
                $missing[] = $slug;

                continue;
            }

            DB::transaction(function () use ($category, $entry, &$updated) {
                $changed = $this->applyMeta($category, $entry);
                $changed = $this->applySeoText($category, $entry) || $changed;

                if ($changed) {
                    $category->timestamps = false;
                    $category->updated_at = now();
                    $category->saveQuietly();
                    $updated++;
                }
            });
        }

        return ['categories' => $updated, 'missing' => $missing];
    }

    private function applyMeta(Category $category, array $entry): bool
    {
        $changed = false;
        $replace = ($entry['replace_meta'] ?? false) === true;

        foreach (['meta_title', 'meta_description'] as $field) {
            foreach (self::LOCALES as $locale) {
                $value = trim((string) data_get($entry, "{$field}.{$locale}", ''));
                $stored = (string) $category->getTranslation($field, $locale, false);

                if ($value !== '' && ($replace || blank($stored)) && $stored !== $value) {
                    $category->setTranslation($field, $locale, $value);
                    $changed = true;
                }
            }
        }

        return $changed;
    }

    private function applySeoText(Category $category, array $entry): bool
    {
        $changed = false;
        $replace = ($entry['replace_content'] ?? false) === true;

        foreach (self::LOCALES as $locale) {
            $title = trim((string) data_get($entry, "seo_title.{$locale}", ''));
            $content = trim((string) data_get($entry, "seo_text.{$locale}", ''));
            if ($title === '' && $content === '') {
                continue;
            }

            $seoText = SeoText::query()->firstOrNew([
                'page_type' => $category->slug,
                'language' => $locale,
            ]);
            $values = [];

            if ($title !== '' && ($replace || blank($seoText->title)) && (string) $seoText->title !== $title) {
                $values['title'] = $title;
            }
            if ($content !== '' && ($replace || blank(strip_tags((string) $seoText->content))) && (string) $seoText->content !== $content) {
                $values['content'] = $content;
            }

            if ($values !== []) {
                $seoText->fill($values)->save();
                $changed = true;
            }
        }

        return $changed;
    }
}
