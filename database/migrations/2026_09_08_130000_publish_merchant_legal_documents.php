<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<int, array<string, array{file: string, title: string, description: string}>> */
    private array $documents = [
        5 => [
            'uk' => [
                'file' => 'legal/uk/privacy.html',
                'title' => 'Політика конфіденційності — Bona Doors',
                'description' => 'Як Bona Doors збирає, використовує, захищає та передає персональні дані, застосовує cookies, аналітику, оплату й онлайн-чат.',
            ],
            'ru' => [
                'file' => 'legal/ru/privacy.html',
                'title' => 'Политика конфиденциальности — Bona Doors',
                'description' => 'Как Bona Doors собирает, использует, защищает и передает персональные данные, применяет cookies, аналитику, оплату и онлайн-чат.',
            ],
        ],
        6 => [
            'uk' => [
                'file' => 'legal/uk/public-offer.html',
                'title' => 'Договір публічної оферти — Bona Doors',
                'description' => 'Умови замовлення, оплати, доставки, комплектації, гарантії та повернення товарів в інтернет-магазині Bona Doors.',
            ],
            'ru' => [
                'file' => 'legal/ru/public-offer.html',
                'title' => 'Договор публичной оферты — Bona Doors',
                'description' => 'Условия заказа, оплаты, доставки, комплектации, гарантии и возврата товаров в интернет-магазине Bona Doors.',
            ],
        ],
        7 => [
            'uk' => [
                'file' => 'legal/uk/exchange-return.html',
                'title' => 'Обмін та повернення — Bona Doors',
                'description' => 'Правила обміну, повернення та гарантійних звернень щодо дверей, комплектуючих і товарів за індивідуальним замовленням.',
            ],
            'ru' => [
                'file' => 'legal/ru/exchange-return.html',
                'title' => 'Обмен и возврат — Bona Doors',
                'description' => 'Правила обмена, возврата и гарантийных обращений по дверям, комплектующим и товарам по индивидуальному заказу.',
            ],
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->documents as $typeId => $translations) {
            $pageId = DB::table('static_pages')
                ->where('type_id', $typeId)
                ->orderBy('id')
                ->value('id');

            if (! $pageId) {
                $pageId = DB::table('static_pages')->insertGetId([
                    'type_id' => $typeId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($translations as $language => $document) {
                $values = [
                    'meta_title' => $document['title'],
                    'meta_description' => $document['description'],
                    'meta_keywords' => null,
                    'meta_tags' => null,
                    'content' => $this->content($document['file']),
                    'updated_at' => $now,
                ];

                $query = DB::table('static_page_contents')
                    ->where('static_page_id', $pageId)
                    ->where('language', $language);

                if ($query->exists()) {
                    $query->update($values);
                } else {
                    DB::table('static_page_contents')->insert([
                        'static_page_id' => $pageId,
                        'language' => $language,
                        'created_at' => $now,
                        ...$values,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Legal copy can be edited in the admin panel after deployment. A
        // rollback must never delete that business-owned content.
    }

    private function content(string $relativePath): string
    {
        $content = file_get_contents(resource_path($relativePath));

        if ($content === false) {
            throw new RuntimeException('Unable to read legal document: '.$relativePath);
        }

        return trim($content);
    }
};
