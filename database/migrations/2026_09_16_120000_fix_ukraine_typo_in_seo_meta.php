<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Titles and descriptions entered through the admin were saved with
 * "Украіні" (a plain "і") instead of "Україні". Only that misspelling is
 * replaced; every other character of the stored value is kept as is.
 */
return new class extends Migration
{
    private const TYPOS = [
        'Украіні' => 'Україні',
        'Украіна' => 'Україна',
        'Украіни' => 'України',
    ];

    private const COLUMNS = [
        'products' => ['meta_title', 'meta_description', 'meta_keywords'],
        'product_types' => ['meta_title', 'meta_description', 'meta_keywords', 'meta_product_title', 'meta_product_description'],
        'categories' => ['meta_title', 'meta_description', 'meta_keywords'],
        'brands' => ['meta_title', 'meta_description', 'meta_keywords'],
        'filter_groups' => ['meta_title', 'meta_description', 'meta_keywords', 'title_tag'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn($table, $column)));
            if ($columns === []) {
                continue;
            }

            DB::table($table)->select(['id', ...$columns])->orderBy('id')->chunkById(200, function ($rows) use ($table, $columns) {
                foreach ($rows as $row) {
                    $changes = [];

                    foreach ($columns as $column) {
                        $fixed = $this->fix($row->{$column});
                        if ($fixed !== $row->{$column}) {
                            $changes[$column] = $fixed;
                        }
                    }

                    if ($changes !== []) {
                        DB::table($table)->where('id', $row->id)->update($changes);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Restoring a misspelling is never wanted.
    }

    private function fix(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $changed = false;
            array_walk_recursive($decoded, function (&$item) use (&$changed) {
                if (is_string($item)) {
                    $fixed = strtr($item, self::TYPOS);
                    $changed = $changed || $fixed !== $item;
                    $item = $fixed;
                }
            });

            if (! $changed) {
                return $value;
            }

            $flags = str_contains($value, '\\u') ? 0 : JSON_UNESCAPED_UNICODE;

            return json_encode($decoded, $flags | JSON_UNESCAPED_SLASHES);
        }

        return strtr($value, self::TYPOS);
    }
};
