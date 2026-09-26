<?php

use App\Models\Brand;
use App\Models\Color;
use App\Models\ProductAttributeOptions;
use App\Models\ProductCharacteristics;
use App\Models\ProductFieldOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** @var array<string, string> */
    private const REFERENCE_TRANSLATIONS = [
        'Білий пп' => 'Белый пп',
        'Сатин білий' => 'Сатин белый',
    ];

    /** @var array<string, string> */
    private const OPENING_TRANSLATIONS = [
        'ліве' => 'левое',
        'праве' => 'правое',
        'ліве внутрішнє (INSIDE)' => 'левое внутреннее (INSIDE)',
        'праве внутрішнє (INSIDE)' => 'правое внутреннее (INSIDE)',
    ];

    public function up(): void
    {
        $this->translateMatching(Color::query(), 'name', self::REFERENCE_TRANSLATIONS);

        $this->translateMatching(
            Brand::query()->where('slug', 'dveri-ukraina'),
            'name',
            ['Двері Україна' => 'Двери Украина'],
        );

        $this->translateMatching(ProductFieldOption::query(), 'name', self::OPENING_TRANSLATIONS);
        $this->translateMatching(ProductAttributeOptions::query(), 'name', self::OPENING_TRANSLATIONS);
        $this->translateMatching(
            ProductCharacteristics::query(),
            'name',
            ['Колір:' => 'Цвет:'],
        );
    }

    public function down(): void
    {
        // Keep reviewed translations: reverting them could overwrite later admin edits.
    }

    /**
     * Fill only a missing Russian value or one that still duplicates Ukrainian.
     * A translation already corrected in the admin panel is deliberately kept.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, string>  $translations
     */
    private function translateMatching(Builder $query, string $attribute, array $translations): void
    {
        $query
            ->whereIn($attribute.'->uk', array_keys($translations))
            ->get()
            ->each(function (Model $model) use ($attribute, $translations): void {
                if (! method_exists($model, 'getTranslations')) {
                    return;
                }

                $stored = $model->getTranslations($attribute);
                $ukrainian = trim((string) ($stored['uk'] ?? ''));
                $russian = trim((string) ($stored['ru'] ?? ''));
                $replacement = $translations[$ukrainian] ?? null;

                if ($replacement === null || ($russian !== '' && $russian !== $ukrainian)) {
                    return;
                }

                $stored['ru'] = $replacement;
                $model->setAttribute($attribute, $stored);
                $model->save();
            });
    }
};
