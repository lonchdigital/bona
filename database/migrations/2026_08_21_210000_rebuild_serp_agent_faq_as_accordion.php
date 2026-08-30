<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Articles delivered before the FAQ block became an accordion carry the flat
 * markup in their stored body. This rewrites it into the markup the site
 * accordion understands, so already published articles look the same as the
 * ones that arrive from now on.
 *
 * Safe to run more than once: a body without the old markup is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('blog_article_blocks')->get() as $row) {
            $content = json_decode($row->content, true);

            if (! is_array($content)) {
                continue;
            }

            $changed = false;

            foreach ($content as $locale => $html) {
                if (! is_string($html)) {
                    continue;
                }

                $rebuilt = $this->rebuildFaq($html);

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

    private function rebuildFaq(string $html): string
    {
        if (! str_contains($html, 'article-faq__item') || ! class_exists(DOMDocument::class)) {
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

        $items = iterator_to_array($xpath->query(
            '//div[contains(concat(" ", normalize-space(@class), " "), " article-faq__item ")]'
        ));

        if (! $items) {
            return $html;
        }

        $isFirst = true;

        foreach ($items as $item) {
            $question = $xpath->query($this->byClass('article-faq__question'), $item)->item(0);
            $answer = $xpath->query($this->byClass('article-faq__answer'), $item)->item(0);

            if (! $question || ! $answer) {
                continue;
            }

            $wrapper = $document->createElement('div');
            $wrapper->setAttribute('class', 'accordion-item-wrapper');

            $heading = $document->createElement('h3');
            $heading->setAttribute('class', 'accordion'.($isFirst ? ' active' : ''));
            $heading->setAttribute('role', 'button');
            $heading->setAttribute('tabindex', '0');

            $questionText = $document->createElement('span');
            $questionText->setAttribute('class', 'question');
            $questionText->appendChild($document->createTextNode($question->textContent));
            $heading->appendChild($questionText);

            $panel = $document->createElement('div');
            $panel->setAttribute('class', 'art-panel');

            // The first answer starts open, matching what the builder emits.
            if ($isFirst) {
                $panel->setAttribute('style', 'max-height: 2000px;');
            }

            $panelData = $document->createElement('div');
            $panelData->setAttribute('class', 'panel-data');

            foreach (iterator_to_array($answer->childNodes) as $child) {
                $panelData->appendChild($child);
            }

            $panel->appendChild($panelData);

            $wrapper->appendChild($heading);
            $wrapper->appendChild($panel);

            $item->parentNode->replaceChild($wrapper, $item);

            $isFirst = false;
        }

        $result = '';

        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function byClass(string $class): string
    {
        return './/*[contains(concat(" ", normalize-space(@class), " "), " '.$class.' ")]';
    }
};
