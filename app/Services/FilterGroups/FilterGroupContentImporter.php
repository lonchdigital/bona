<?php

namespace App\Services\FilterGroups;

use App\Models\Color;
use App\Models\Faqs;
use App\Models\FilterGroup;
use App\Models\ProductField;
use App\Models\ProductFieldOption;
use App\Models\ProductType;
use App\Models\SeoText;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FilterGroupContentImporter
{
    private const LOCALES = ['uk', 'ru'];

    public function __construct(
        private readonly FilterGroupService $filterGroupService,
    ) {}

    /** @return array{groups: int, missing: list<string>} */
    public function importFile(string $path): array
    {
        $entries = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($entries)) {
            throw new InvalidArgumentException("{$path} must contain a list of filter groups.");
        }

        $updated = 0;
        $missing = [];

        foreach ($entries as $entry) {
            $slug = trim((string) ($entry['slug'] ?? ''));
            $productType = ProductType::query()
                ->where('slug', (string) ($entry['product_type_slug'] ?? ''))
                ->first();

            if ($slug === '' || $productType === null) {
                $missing[] = $slug !== '' ? $slug : '(missing slug)';

                continue;
            }

            $filters = $this->resolveFilters($productType, $entry['filter'] ?? []);
            if ($filters === null) {
                $missing[] = $slug;

                continue;
            }

            $existing = FilterGroup::query()->where('slug', $slug)->first();
            $creator = $existing?->creator ?? User::query()->orderBy('id')->first();
            if ($creator === null) {
                $missing[] = $slug;

                continue;
            }

            $changed = DB::transaction(function () use ($entry, $slug, $productType, $filters, $existing, $creator): bool {
                $filterGroup = $existing ?? new FilterGroup;
                $isNew = ! $filterGroup->exists;
                $replaceMeta = ($entry['replace_meta'] ?? false) === true;
                $replaceFilters = ($entry['replace_filters'] ?? false) === true;

                if ($isNew) {
                    $filterGroup->user_id = $creator->id;
                    $filterGroup->slug = $slug;
                }

                $filterGroup->product_type_id = $productType->id;

                foreach (['name', 'title_tag', 'meta_title', 'meta_description', 'meta_keywords'] as $field) {
                    $value = $this->translations($entry[$field] ?? []);
                    $stored = $filterGroup->exists ? $filterGroup->getTranslations($field) : [];

                    if (($isNew || $replaceMeta || $this->translationsAreBlank($stored))
                        && $this->translations($stored) !== $value) {
                        $filterGroup->setTranslations($field, $value);
                    }
                }

                if (($isNew || $replaceFilters || blank($filterGroup->filters))
                    && $filterGroup->filters != $filters) {
                    $filterGroup->filters = $filters;
                }

                $recordChanged = $isNew || $filterGroup->isDirty();
                $filterGroup->save();

                $editorialChanged = $this->syncSeoText($filterGroup, $entry);
                $faqChanged = $this->syncFaqs($filterGroup, $entry);

                if (($editorialChanged || $faqChanged) && ! $recordChanged) {
                    $filterGroup->touch();
                }

                return $recordChanged || $editorialChanged || $faqChanged;
            });

            if ($changed) {
                $updated++;
            }
        }

        return ['groups' => $updated, 'missing' => $missing];
    }

    /** @return array<string, mixed>|null */
    private function resolveFilters(ProductType $productType, array $filter): ?array
    {
        $customFields = null;
        $colorIds = null;

        if (isset($filter['custom_field'])) {
            $definition = $filter['custom_field'];
            $field = ProductField::query()
                ->where('slug', (string) ($definition['field_slug'] ?? ''))
                ->whereHas('types', fn ($query) => $query->where('product_types.id', $productType->id))
                ->first();
            $option = $field
                ? ProductFieldOption::query()
                    ->where('product_field_id', $field->id)
                    ->where('slug', (string) ($definition['option_slug'] ?? ''))
                    ->first()
                : null;

            if ($field === null || $option === null) {
                return null;
            }

            $customFields = [[
                'id' => $field->id,
                'value' => [$option->id],
            ]];
        }

        if (isset($filter['color_slug'])) {
            $color = Color::query()->where('slug', (string) $filter['color_slug'])->first();
            if ($color === null) {
                return null;
            }

            $colorIds = [$color->id];
        }

        if ($customFields === null && $colorIds === null) {
            return null;
        }

        return [
            'price_from' => null,
            'price_to' => null,
            'country_ids' => null,
            'custom_fields' => $customFields,
            'color_ids' => $colorIds,
            'brand_ids' => null,
            'length_from' => null,
            'length_to' => null,
            'length_options' => null,
            'width_from' => null,
            'width_to' => null,
            'width_options' => null,
            'height_from' => null,
            'height_to' => null,
            'height_options' => null,
        ];
    }

    private function syncSeoText(FilterGroup $filterGroup, array $entry): bool
    {
        $replace = ($entry['replace_content'] ?? false) === true;
        $titles = $this->translations($entry['seo_title'] ?? []);
        $contents = $this->translations($entry['seo_text'] ?? []);
        $addenda = $this->translations($entry['seo_text_addendum'] ?? []);
        $changed = false;

        foreach (self::LOCALES as $locale) {
            $content = $contents[$locale].$addenda[$locale];
            $row = SeoText::query()->firstOrNew([
                'page_type' => $this->filterGroupService->contentPageType($filterGroup),
                'language' => $locale,
            ]);

            if (! $row->exists || $replace || blank(strip_tags((string) $row->content))) {
                $row->title = $titles[$locale];
                $row->content = $content;
            }

            if (! $row->exists || $row->isDirty()) {
                $row->save();
                $changed = true;
            }
        }

        return $changed;
    }

    private function syncFaqs(FilterGroup $filterGroup, array $entry): bool
    {
        $desired = collect($entry['faqs'] ?? [])->map(fn (array $faq): array => [
            'question' => $this->translations($faq['question'] ?? []),
            'answer' => $this->translations($faq['answer'] ?? []),
        ])->values()->all();
        $existing = Faqs::query()
            ->where('page_type', $this->filterGroupService->contentPageType($filterGroup))
            ->orderBy('id')
            ->get();
        $existingPayload = $existing->map(fn (Faqs $faq): array => [
            'question' => $this->translations($faq->getTranslations('question')),
            'answer' => $this->translations($faq->getTranslations('answer')),
        ])->values()->all();
        $replace = ($entry['replace_faqs'] ?? false) === true;

        if ($desired === [] || ((! $replace && $existing->isNotEmpty()) || $desired === $existingPayload)) {
            return false;
        }

        $existing->each->delete();

        foreach ($desired as $faq) {
            Faqs::query()->create([
                'page_type' => $this->filterGroupService->contentPageType($filterGroup),
                'question' => $faq['question'],
                'answer' => $faq['answer'],
            ]);
        }

        return true;
    }

    /** @return array{uk: string, ru: string} */
    private function translations(mixed $value): array
    {
        return collect(self::LOCALES)
            ->mapWithKeys(fn (string $locale): array => [
                $locale => trim((string) data_get($value, $locale, '')),
            ])
            ->all();
    }

    private function translationsAreBlank(array $translations): bool
    {
        return collect(self::LOCALES)->every(
            fn (string $locale): bool => blank(trim((string) ($translations[$locale] ?? ''))),
        );
    }
}
