<?php

namespace App\Services\ServicesPage;

use App\Models\Faqs;
use App\Models\ServicesConfig;
use App\Models\ServicesPageSections;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ServicesContentImporter
{
    private const LOCALES = ['uk', 'ru'];

    /** @return array{page: bool, services: int, missing: list<string>} */
    public function importFile(string $path): array
    {
        $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($payload) || ! is_array($payload['page'] ?? null) || ! is_array($payload['services'] ?? null)) {
            throw new InvalidArgumentException("{$path} must contain page and services content.");
        }

        return DB::transaction(function () use ($payload): array {
            $pageChanged = $this->syncPage($payload['page']);
            $servicesChanged = 0;
            $missing = [];

            foreach ($payload['services'] as $entry) {
                $slug = trim((string) ($entry['slug'] ?? ''));
                $service = ServicesPageSections::query()->where('slug', $slug)->first();
                if ($slug === '' || $service === null) {
                    $missing[] = $slug !== '' ? $slug : '(missing slug)';

                    continue;
                }

                if ($this->syncService($service, $entry)) {
                    $servicesChanged++;
                }
            }

            return [
                'page' => $pageChanged,
                'services' => $servicesChanged,
                'missing' => $missing,
            ];
        });
    }

    private function syncPage(array $entry): bool
    {
        $config = ServicesConfig::query()->first();
        if ($config === null) {
            if (! ServicesPageSections::query()->exists()) {
                return false;
            }

            $config = new ServicesConfig;
        }

        $isNew = ! $config->exists;
        $replaceContent = ($entry['replace_content'] ?? false) === true;
        $replaceMeta = ($entry['replace_meta'] ?? false) === true;

        $this->fillTranslations($config, $entry, ['title', 'intro', 'content'], $isNew || $replaceContent);
        $this->fillTranslations(
            $config,
            $entry,
            ['meta_title', 'meta_description', 'meta_keywords'],
            $isNew || $replaceMeta,
        );

        $recordChanged = $isNew || $config->isDirty();
        $config->save();
        $faqChanged = $this->syncFaqs(ServicesConfig::CONTENT_PAGE_TYPE, $entry);

        if ($faqChanged && ! $recordChanged) {
            $config->touch();
        }

        return $recordChanged || $faqChanged;
    }

    private function syncService(ServicesPageSections $service, array $entry): bool
    {
        $this->fillTranslations(
            $service,
            $entry,
            ['title', 'description', 'intro', 'content', 'button_text'],
            ($entry['replace_content'] ?? false) === true,
        );
        $this->fillTranslations(
            $service,
            $entry,
            ['meta_title', 'meta_description', 'meta_keywords'],
            ($entry['replace_meta'] ?? false) === true,
        );

        $recordChanged = $service->isDirty();
        if ($recordChanged) {
            $service->save();
        }

        $faqChanged = $this->syncFaqs($service->editorialPageType(), $entry);
        if ($faqChanged && ! $recordChanged) {
            $service->touch();
        }

        return $recordChanged || $faqChanged;
    }

    /** @param list<string> $fields */
    private function fillTranslations(object $model, array $entry, array $fields, bool $replace): void
    {
        foreach ($fields as $field) {
            if (! array_key_exists($field, $entry)) {
                continue;
            }

            $incoming = $this->translations($entry[$field]);
            $stored = $model->exists ? $this->translations($model->getTranslations($field)) : ['uk' => '', 'ru' => ''];
            if (($replace || $this->translationsAreBlank($stored)) && $incoming !== $stored) {
                $model->setTranslations($field, $incoming);
            }
        }
    }

    private function syncFaqs(string $pageType, array $entry): bool
    {
        $desired = collect($entry['faqs'] ?? [])->map(fn (array $faq): array => [
            'question' => $this->translations($faq['question'] ?? []),
            'answer' => $this->translations($faq['answer'] ?? []),
        ])->values()->all();
        $existing = Faqs::query()->where('page_type', $pageType)->orderBy('id')->get();
        $existingPayload = $existing->map(fn (Faqs $faq): array => [
            'question' => $this->translations($faq->getTranslations('question')),
            'answer' => $this->translations($faq->getTranslations('answer')),
        ])->values()->all();

        if (($entry['replace_faqs'] ?? false) !== true || $desired === $existingPayload) {
            return false;
        }

        $existing->each->delete();
        foreach ($desired as $faq) {
            Faqs::query()->create([
                'page_type' => $pageType,
                'question' => $faq['question'],
                'answer' => $faq['answer'],
            ]);
        }

        return true;
    }

    /** @return array{uk: string, ru: string} */
    private function translations(mixed $value): array
    {
        return collect(self::LOCALES)->mapWithKeys(fn (string $locale): array => [
            $locale => trim((string) data_get($value, $locale, '')),
        ])->all();
    }

    /** @param array<string, string> $translations */
    private function translationsAreBlank(array $translations): bool
    {
        return collect(self::LOCALES)->every(
            fn (string $locale): bool => blank(trim((string) ($translations[$locale] ?? ''))),
        );
    }
}
