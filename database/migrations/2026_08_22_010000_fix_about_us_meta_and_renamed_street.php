<?php

use App\Models\AboutUsConfig;
use App\Models\ContactConfig;
use Illuminate\Database\Migrations\Migration;

/**
 * Two content fixes the SEO audit asked for.
 *
 * The About us page carried an auto generated description that read
 * "Інформація про Про компанію Bona-doors", doubling the preposition, and the
 * second showroom still sat on Толбухіна, a street since renamed to Георгія
 * Липського. The site, the Google Business Profile and the maps have to agree
 * on the current name.
 *
 * Both are replaced only where the old value is still there, so anything
 * rewritten by hand in the admin panel survives.
 */
return new class extends Migration
{
    private const OLD_DESCRIPTIONS = [
        'uk' => 'Інформація про Про компанію Bona-doors',
        'ru' => 'Информация о О компании Bona-doors',
    ];

    private const NEW_DESCRIPTIONS = [
        'uk' => 'Bona — салон дверей в Одесі з 2013 року. Міжкімнатні, вхідні та приховані двері, власний замір, доставка й монтаж. Два шоуруми: ТЦ «Гіпермаркет Дверей» і ТЦ «МегаДім».',
        'ru' => 'Bona — салон дверей в Одессе с 2013 года. Межкомнатные, входные и скрытые двери, собственный замер, доставка и монтаж. Два шоурума: ТЦ «Гипермаркет Дверей» и ТЦ «МегаДом».',
    ];

    private const NEW_TITLES = [
        'uk' => 'Про салон дверей Bona в Одесі — з 2013 року, два шоуруми',
        'ru' => 'О салоне дверей Bona в Одессе — с 2013 года, два шоурума',
    ];

    private const OLD_ADDRESSES = [
        'uk' => 'ТЦ "МегаДім" (Толбухіна, 135)',
        'ru' => 'ТЦ "МегаДом" (Толбухина, 135)',
    ];

    private const NEW_ADDRESSES = [
        'uk' => 'ТЦ "МегаДім" (Георгія Липського, 135)',
        'ru' => 'ТЦ "МегаДом" (Георгия Липского, 135)',
    ];

    public function up(): void
    {
        $this->replaceTranslations(AboutUsConfig::first(), 'meta_description', self::OLD_DESCRIPTIONS, self::NEW_DESCRIPTIONS);
        $this->replaceTranslations(AboutUsConfig::first(), 'meta_title', null, self::NEW_TITLES);
        $this->replaceTranslations(ContactConfig::first(), 'address_two', self::OLD_ADDRESSES, self::NEW_ADDRESSES);
    }

    public function down(): void
    {
        $this->replaceTranslations(ContactConfig::first(), 'address_two', self::NEW_ADDRESSES, self::OLD_ADDRESSES);
        $this->replaceTranslations(AboutUsConfig::first(), 'meta_description', self::NEW_DESCRIPTIONS, self::OLD_DESCRIPTIONS);
    }

    /**
     * @param array<string, string>|null $expected null replaces whatever is there
     */
    private function replaceTranslations(?object $model, string $attribute, ?array $expected, array $values): void
    {
        if (!$model) {
            return;
        }

        $translations = $model->getTranslations($attribute);
        $changed = false;

        foreach ($values as $locale => $value) {
            $current = $translations[$locale] ?? null;

            if ($expected !== null && trim((string) $current) !== $expected[$locale]) {
                continue;
            }

            $translations[$locale] = $value;
            $changed = true;
        }

        if ($changed) {
            $model->setAttribute($attribute, $translations);
            $model->save();
        }
    }
};
