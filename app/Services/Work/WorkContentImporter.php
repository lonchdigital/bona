<?php

namespace App\Services\Work;

use App\Models\User;
use App\Models\Work;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

/**
 * Imports verified project stories into the same Work records that managers
 * edit in the admin. Existing manager copy and images stay untouched unless a
 * content entry explicitly opts in to replacement.
 */
class WorkContentImporter
{
    private const LOCALES = ['uk', 'ru'];

    private const CONTENT_FIELDS = [
        'name',
        'intro',
        'description',
        'client_quote',
        'service_title',
        'service_description',
        'price_note',
    ];

    private const META_FIELDS = [
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    private const SCALAR_CONTENT_FIELDS = [
        'location',
        'doors_count',
        'duration',
        'client_name',
        'price_from',
        'price_currency',
    ];

    /**
     * @return array{works: int, created: int, restored_images: int}
     */
    public function importFile(string $path): array
    {
        $entries = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($entries) || ! array_is_list($entries)) {
            throw new InvalidArgumentException("{$path} must contain a list of works.");
        }

        $creatorId = User::query()->orderBy('id')->value('id');
        if ($creatorId === null) {
            throw new RuntimeException('A user is required before work content can be imported.');
        }

        $contentDirectory = realpath(dirname($path));
        if ($contentDirectory === false) {
            throw new InvalidArgumentException("Content directory does not exist for {$path}.");
        }

        $updated = 0;
        $created = 0;
        $restoredImages = 0;

        foreach ($entries as $entry) {
            $slug = trim((string) ($entry['slug'] ?? ''));
            if ($slug === '') {
                throw new InvalidArgumentException('Every work entry must have a slug.');
            }

            DB::transaction(function () use (
                $entry,
                $slug,
                $creatorId,
                $contentDirectory,
                &$updated,
                &$created,
                &$restoredImages,
            ): void {
                $work = Work::query()->firstOrNew(['slug' => $slug]);
                $isNew = ! $work->exists;
                if ($isNew) {
                    $work->creator_id = $creatorId;
                }

                $replaceContent = ($entry['replace_content'] ?? false) === true;
                $replaceMeta = ($entry['replace_meta'] ?? false) === true;
                $replacePublishing = ($entry['replace_publishing'] ?? false) === true;

                $this->fillTranslations($work, $entry, self::CONTENT_FIELDS, $isNew || $replaceContent);
                $this->fillTranslations($work, $entry, self::META_FIELDS, $isNew || $replaceMeta);
                $this->fillScalars($work, $entry, $isNew || $replaceContent);

                if ($isNew || $replacePublishing) {
                    $work->is_published = (bool) ($entry['is_published'] ?? true);
                    $work->sort_order = max(0, (int) ($entry['sort_order'] ?? 0));
                }

                $imageChanged = $this->syncImage($work, $entry, $contentDirectory, $isNew);
                $recordChanged = $isNew || $work->isDirty();

                if ($recordChanged) {
                    $work->save();
                }

                if ($recordChanged || $imageChanged) {
                    $updated++;
                }
                if ($isNew) {
                    $created++;
                }
                if ($imageChanged && ! $recordChanged) {
                    $restoredImages++;
                }
            });
        }

        return [
            'works' => $updated,
            'created' => $created,
            'restored_images' => $restoredImages,
        ];
    }

    /** @param list<string> $fields */
    private function fillTranslations(Work $work, array $entry, array $fields, bool $replace): void
    {
        foreach ($fields as $field) {
            if (! array_key_exists($field, $entry)) {
                continue;
            }

            foreach (self::LOCALES as $locale) {
                $incoming = trim((string) data_get($entry, "{$field}.{$locale}", ''));
                $stored = $work->exists
                    ? trim((string) $work->getTranslation($field, $locale, false))
                    : '';

                if ($incoming !== '' && ($replace || $stored === '') && $incoming !== $stored) {
                    $work->setTranslation($field, $locale, $incoming);
                }
            }
        }
    }

    private function fillScalars(Work $work, array $entry, bool $replace): void
    {
        foreach (self::SCALAR_CONTENT_FIELDS as $field) {
            if (! array_key_exists($field, $entry)) {
                continue;
            }

            $incoming = $entry[$field];
            $stored = $work->getAttribute($field);
            $storedIsBlank = $stored === null || (is_string($stored) && trim($stored) === '');

            if (($replace || $storedIsBlank) && $incoming !== $stored) {
                $work->setAttribute($field, $incoming);
            }
        }
    }

    private function syncImage(Work $work, array $entry, string $contentDirectory, bool $isNew): bool
    {
        $relativePath = trim((string) ($entry['image'] ?? ''));
        if ($relativePath === '') {
            if ($isNew) {
                throw new InvalidArgumentException("Work {$work->slug} requires an image.");
            }

            return false;
        }

        $sourcePath = realpath($contentDirectory.DIRECTORY_SEPARATOR.$relativePath);
        $allowedPrefix = rtrim($contentDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if ($sourcePath === false || ! str_starts_with($sourcePath, $allowedPrefix) || ! is_file($sourcePath)) {
            throw new InvalidArgumentException("Image {$relativePath} is not a file inside {$contentDirectory}.");
        }

        $extension = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new InvalidArgumentException("Unsupported work image extension: {$extension}.");
        }

        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $destination = 'work-images/content-'.$work->slug.'.'.$extension;
        $replaceImage = ($entry['replace_image'] ?? false) === true;
        $storedPath = trim((string) $work->image_path);
        $maySetPath = $isNew || $storedPath === '' || $replaceImage;

        if (! $maySetPath && $storedPath !== $destination) {
            return false;
        }

        $disk = Storage::disk(config('app.images_disk_default'));
        $needsCopy = ! $disk->exists($destination)
            || hash_file('sha256', $sourcePath) !== hash('sha256', (string) $disk->get($destination));

        if ($needsCopy && ! $disk->put($destination, (string) file_get_contents($sourcePath))) {
            throw new RuntimeException("Could not store work image {$destination}.");
        }

        if ($maySetPath && $storedPath !== $destination) {
            $work->image_path = $destination;
        }

        return $needsCopy;
    }
}
