<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductCharacteristics;
use App\Models\ProductFaqs;
use App\Models\ProductText;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Applies prepared catalogue copy (database/content/products/*.json) to
 * existing products.
 *
 * It only fills gaps: a description is written when the stored one is empty
 * or a one-line stub (or the entry sets "replace_content": true), characteristics are added only under names the product
 * does not have yet, FAQ only when the product has none and meta only when it
 * is blank. Anything a manager has written in the admin is left untouched, so
 * the import is safe to run again.
 */
class ProductContentImporter
{
    private const LOCALES = ['uk', 'ru'];

    private const THIN_DESCRIPTION_WORDS = 40;

    /**
     * @return array{products: int, missing: list<string>}
     */
    public function importFile(string $path): array
    {
        $entries = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($entries)) {
            throw new InvalidArgumentException("{$path} must contain a list of products.");
        }

        $updated = 0;
        $missing = [];

        foreach ($entries as $entry) {
            $product = Product::query()->where('slug', $entry['slug'] ?? '')->first();
            if ($product === null) {
                $missing[] = (string) ($entry['slug'] ?? '');

                continue;
            }

            DB::transaction(function () use ($product, $entry, &$updated) {
                $changed = $this->applyTexts($product, $entry);
                $changed = $this->applyCharacteristics($product, $entry['characteristics'] ?? []) || $changed;
                $changed = $this->applyFaqs($product, $entry['faqs'] ?? []) || $changed;
                $changed = $this->applyMeta($product, $entry) || $changed;

                if ($changed) {
                    // Bypasses observers: a bulk import must not queue a
                    // sitemap rebuild per product, but Last-Modified must move.
                    DB::table('products')->where('id', $product->id)->update(['updated_at' => now()]);
                    $updated++;
                }
            });
        }

        return ['products' => $updated, 'missing' => $missing];
    }

    private function applyTexts(Product $product, array $entry): bool
    {
        $changed = false;

        foreach (self::LOCALES as $locale) {
            $content = trim((string) data_get($entry, "content.{$locale}", ''));
            $short = trim((string) data_get($entry, "short_content.{$locale}", ''));
            if ($content === '' && $short === '') {
                continue;
            }

            $text = ProductText::query()->firstOrNew(['product_id' => $product->id, 'language' => $locale]);
            $values = [];

            // "replace_content": true is for shared boilerplate that is long
            // enough to look written but identical across a whole brand.
            if ($content !== ''
                && ($this->isThin($text->content) || ($entry['replace_content'] ?? false) === true)
                && (string) $text->content !== $content
            ) {
                $values['content'] = $content;
            }
            if ($short !== '' && blank(strip_tags((string) $text->short_content))) {
                $values['short_content'] = $short;
            }

            if ($values !== []) {
                $text->fill($values)->save();
                $changed = true;
            }
        }

        return $changed;
    }

    private function applyCharacteristics(Product $product, array $characteristics): bool
    {
        $existingNames = ProductCharacteristics::query()
            ->where('product_id', $product->id)
            ->get()
            ->map(fn (ProductCharacteristics $row) => $this->normaliseName($row->getTranslation('name', 'uk', false)))
            ->filter()
            ->all();

        $changed = false;
        foreach ($characteristics as $characteristic) {
            $name = $this->normaliseName((string) data_get($characteristic, 'name.uk', ''));
            if ($name === '' || in_array($name, $existingNames, true)) {
                continue;
            }

            ProductCharacteristics::query()->create([
                'product_id' => $product->id,
                'name' => $this->localised($characteristic['name']),
                'value' => $this->localised($characteristic['value']),
            ]);
            $existingNames[] = $name;
            $changed = true;
        }

        return $changed;
    }

    private function applyFaqs(Product $product, array $faqs): bool
    {
        if ($faqs === [] || ProductFaqs::query()->where('product_id', $product->id)->exists()) {
            return false;
        }

        foreach ($faqs as $faq) {
            ProductFaqs::query()->create([
                'product_id' => $product->id,
                'question' => $this->localised($faq['question']),
                'answer' => $this->localised($faq['answer']),
            ]);
        }

        return true;
    }

    private function applyMeta(Product $product, array $entry): bool
    {
        $changed = false;

        foreach (['meta_title', 'meta_description'] as $field) {
            foreach (self::LOCALES as $locale) {
                $value = trim((string) data_get($entry, "{$field}.{$locale}", ''));
                if ($value !== '' && blank($product->getTranslation($field, $locale, false))) {
                    $product->setTranslation($field, $locale, $value);
                    $changed = true;
                }
            }
        }

        if ($changed) {
            $product->saveQuietly();
        }

        return $changed;
    }

    private function isThin(?string $html): bool
    {
        $words = preg_split('/\s+/u', trim(html_entity_decode(strip_tags((string) $html))), -1, PREG_SPLIT_NO_EMPTY);

        return count($words) < self::THIN_DESCRIPTION_WORDS;
    }

    private function normaliseName(?string $name): string
    {
        return mb_strtolower(trim((string) $name, " \t\n\r\0\x0B:"));
    }

    /**
     * @return array<string, string>
     */
    private function localised(mixed $value): array
    {
        return collect(self::LOCALES)
            ->mapWithKeys(fn (string $locale) => [$locale => trim((string) data_get($value, $locale, ''))])
            ->all();
    }
}
