<?php

use App\Services\SerpAgent\SerpAgentHtmlService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Serp Agent writes the FAQ into the article body and sends the same questions
 * again as a list, which the integration used to append. Articles published
 * that way print every question twice.
 *
 * The appended block is dropped and the FAQ already inside the body is turned
 * into the accordion, leaving one FAQ where the author put it.
 *
 * Safe to run more than once.
 */
return new class extends Migration
{
    public function up(): void
    {
        $htmlService = app(SerpAgentHtmlService::class);

        foreach (DB::table('blog_article_blocks')->get() as $row) {
            $content = json_decode($row->content, true);

            if (! is_array($content)) {
                continue;
            }

            $changed = false;

            foreach ($content as $locale => $html) {
                if (! is_string($html) || ! str_contains($html, 'Часті запитання') && ! str_contains($html, 'Частые вопросы')) {
                    continue;
                }

                // Only bodies carrying both copies need touching.
                if (! str_contains($html, 'article-faq')) {
                    continue;
                }

                $stripped = $this->removeGeneratedFaqSections($html);
                [$rebuilt, $converted] = $htmlService->convertInlineFaq($stripped, is_string($locale) ? $locale : 'uk');

                if (! $converted) {
                    // No FAQ was written into the body, so the appended block
                    // was the only one there is. Leave the article alone.
                    continue;
                }

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

    private function removeGeneratedFaqSections(string $html): string
    {
        if (! class_exists(DOMDocument::class)) {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousUseErrors = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"?><div data-faq-root="1">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        if (! $loaded) {
            return $html;
        }

        $xpath = new DOMXPath($document);
        $root = $xpath->query('//div[@data-faq-root]')->item(0);

        if (! $root instanceof DOMElement) {
            return $html;
        }

        $sections = $xpath->query(
            '//section[contains(concat(" ", normalize-space(@class), " "), " article-faq ")]'
        );

        foreach (iterator_to_array($sections) as $section) {
            $section->parentNode?->removeChild($section);
        }

        $result = '';

        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }
};
