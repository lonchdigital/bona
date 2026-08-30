<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The accordion headings were first published carrying role="button", which a
 * heading element is not allowed to declare and the W3C validator rejects.
 * The attribute is stripped from bodies already in the database; new articles
 * no longer carry it.
 *
 * Safe to run more than once.
 */
return new class extends Migration
{
    private const REPLACEMENTS = [
        '<h3 class="accordion active" role="button" tabindex="0">' => '<h3 class="accordion active" tabindex="0">',
        '<h3 class="accordion" role="button" tabindex="0">' => '<h3 class="accordion" tabindex="0">',
    ];

    public function up(): void
    {
        foreach (DB::table('blog_article_blocks')->get() as $row) {
            $content = json_decode($row->content, true);

            if (! is_array($content)) {
                continue;
            }

            $changed = false;

            foreach ($content as $locale => $html) {
                if (! is_string($html) || ! str_contains($html, 'role="button"')) {
                    continue;
                }

                $rebuilt = str_replace(
                    array_keys(self::REPLACEMENTS),
                    array_values(self::REPLACEMENTS),
                    $html
                );

                if ($rebuilt !== $html) {
                    $content[$locale] = $rebuilt;
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('blog_article_blocks')
                    ->where('id', $row->id)
                    ->update(['content' => json_encode($content, JSON_UNESCAPED_UNICODE)]);
            }
        }
    }

    /**
     * Content only, nothing structural to undo.
     */
    public function down(): void {}
};
