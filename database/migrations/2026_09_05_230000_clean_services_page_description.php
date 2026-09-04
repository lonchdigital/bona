<?php

use App\Models\ServicesConfig;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const OLD_DESCRIPTIONS = [
        'uk' => 'Замовити послуги компанії Bona-Doors в Одесі ✅ Гарантія якості ✅ Гарантія якості',
        'ru' => 'Заказать услуги компании Bona-Doors в Одессе ✅ Гарантия качества',
    ];

    private const NEW_DESCRIPTIONS = [
        'uk' => 'Замовити послуги компанії Bona Doors в Одесі. Гарантія якості.',
        'ru' => 'Заказать услуги компании Bona Doors в Одессе. Гарантия качества.',
    ];

    public function up(): void
    {
        $this->replaceTranslations(self::OLD_DESCRIPTIONS, self::NEW_DESCRIPTIONS);
    }

    public function down(): void
    {
        $this->replaceTranslations(self::NEW_DESCRIPTIONS, self::OLD_DESCRIPTIONS);
    }

    /**
     * Replace only the known legacy copy, preserving anything edited in admin.
     *
     * @param  array<string, string>  $expected
     * @param  array<string, string>  $replacement
     */
    private function replaceTranslations(array $expected, array $replacement): void
    {
        $config = ServicesConfig::first();

        if (! $config) {
            return;
        }

        $translations = $config->getTranslations('meta_description');
        $changed = false;

        foreach ($replacement as $locale => $value) {
            if (trim((string) ($translations[$locale] ?? '')) !== $expected[$locale]) {
                continue;
            }

            $translations[$locale] = $value;
            $changed = true;
        }

        if ($changed) {
            $config->setAttribute('meta_description', $translations);
            $config->save();
        }
    }
};
