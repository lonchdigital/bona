<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<int, array<string, string>> */
    private array $documents = [
        5 => [
            'uk' => 'legal/uk/privacy.html',
            'ru' => 'legal/ru/privacy.html',
        ],
        6 => [
            'uk' => 'legal/uk/public-offer.html',
            'ru' => 'legal/ru/public-offer.html',
        ],
        7 => [
            'uk' => 'legal/uk/exchange-return.html',
            'ru' => 'legal/ru/exchange-return.html',
        ],
    ];

    public function up(): void
    {
        foreach ($this->documents as $typeId => $translations) {
            $pageId = DB::table('static_pages')
                ->where('type_id', $typeId)
                ->orderBy('id')
                ->value('id');

            if (! $pageId) {
                continue;
            }

            foreach ($translations as $language => $relativePath) {
                DB::table('static_page_contents')
                    ->where('static_page_id', $pageId)
                    ->where('language', $language)
                    ->update([
                        'content' => $this->content($relativePath),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // A rollback must not remove legal details or overwrite content that
        // may have subsequently been edited by the business in the admin panel.
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
