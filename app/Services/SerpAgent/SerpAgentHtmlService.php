<?php

namespace App\Services\SerpAgent;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;

/**
 * Article bodies arrive as raw HTML and the blog template prints them with
 * {!! !!}. Everything that reaches the database therefore goes through this
 * allow-list first, and the extra payload parts (FAQ, link lists) are built
 * here as well, because the article template renders only text, image and
 * video blocks.
 */
class SerpAgentHtmlService
{
    private const FORBIDDEN_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'applet', 'form',
        'input', 'textarea', 'select', 'button', 'link', 'meta', 'base',
        'svg', 'math', 'frame', 'frameset', 'noscript',
    ];

    private const ALLOWED_TAGS = [
        'p' => ['class'],
        'div' => ['class'],
        'section' => ['class'],
        'article' => ['class'],
        'span' => ['class'],
        'br' => [],
        'hr' => [],
        'h1' => ['class', 'id'],
        'h2' => ['class', 'id'],
        'h3' => ['class', 'id'],
        'h4' => ['class', 'id'],
        'h5' => ['class', 'id'],
        'h6' => ['class', 'id'],
        'ul' => ['class'],
        'ol' => ['class', 'start'],
        'li' => ['class'],
        'dl' => ['class'],
        'dt' => ['class'],
        'dd' => ['class'],
        'strong' => ['class'],
        'b' => ['class'],
        'em' => ['class'],
        'i' => ['class'],
        'u' => ['class'],
        's' => ['class'],
        'small' => ['class'],
        'sup' => ['class'],
        'sub' => ['class'],
        'mark' => ['class'],
        'code' => ['class'],
        'pre' => ['class'],
        'blockquote' => ['class', 'cite'],
        'a' => ['href', 'title', 'target', 'rel', 'class'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading', 'class'],
        'figure' => ['class'],
        'figcaption' => ['class'],
        'table' => ['class'],
        'thead' => ['class'],
        'tbody' => ['class'],
        'tfoot' => ['class'],
        'tr' => ['class'],
        'th' => ['class', 'colspan', 'rowspan'],
        'td' => ['class', 'colspan', 'rowspan'],
    ];

    private const BLOCK_TAGS_PATTERN = '#</?(?:p|div|section|article|br|hr|h[1-6]|ul|ol|li|dl|dt|dd|table|thead|tbody|tfoot|tr|th|td|blockquote|pre|figure|figcaption)\b[^>]*>#i';

    private const HEADINGS = [
        'faq' => [
            'uk' => 'Часті запитання',
            'ru' => 'Частые вопросы',
            'en' => 'Frequently asked questions',
        ],
        'related' => [
            'uk' => 'Читайте також',
            'ru' => 'Читайте также',
            'en' => 'Read also',
        ],
        'resources' => [
            'uk' => 'Корисні матеріали',
            'ru' => 'Полезные материалы',
            'en' => 'Useful resources',
        ],
    ];

    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        if (!class_exists(DOMDocument::class)) {
            return $this->sanitizeWithoutDom($html);
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousUseErrors = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"?><div data-serp-agent-root="1">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        if (!$loaded) {
            return $this->sanitizeWithoutDom($html);
        }

        $root = (new DOMXPath($document))->query('//div[@data-serp-agent-root]')->item(0);

        if (!$root instanceof DOMElement) {
            return $this->sanitizeWithoutDom($html);
        }

        $this->cleanChildren($root);
        $this->wrapTables($document, $root);

        $sanitized = '';

        foreach ($root->childNodes as $child) {
            $sanitized .= $document->saveHTML($child);
        }

        return trim($sanitized);
    }

    /**
     * Rendered with the same accordion markup the FAQ block on the home page
     * uses, so it inherits the site styling and the global click handler in
     * public/assets/js/main.js without a line of new behaviour.
     *
     * The question stays a real <h3> rather than a button label: it is a
     * heading search engines should see, and .accordion only needs the class,
     * not a particular tag. It carries tabindex but no role: heading elements
     * are not allowed to declare role="button", and the heading is what both
     * search engines and screen readers should hear.
     */
    /**
     * Serp Agent sends the FAQ twice: written into the article body as a
     * heading followed by question headings, and again as a structured list in
     * the payload. Appending the list on top of the body printed every
     * question twice.
     *
     * So the body wins: the FAQ already written into it is turned into the
     * accordion in place, and the caller then knows not to append the list.
     *
     * @return array{0: string, 1: bool} the html, and whether a FAQ was found
     */
    public function convertInlineFaq(string $html, string $locale): array
    {
        $html = trim($html);

        if ($html === '' || !class_exists(DOMDocument::class)) {
            return [$html, false];
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousUseErrors = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"?><div data-faq-root="1">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        if (!$loaded) {
            return [$html, false];
        }

        $xpath = new DOMXPath($document);
        $root = $xpath->query('//div[@data-faq-root]')->item(0);

        if (!$root instanceof DOMElement) {
            return [$html, false];
        }

        $heading = null;

        foreach ($xpath->query('//h2') as $candidate) {
            if ($this->isFaqHeading($candidate->textContent)) {
                $heading = $candidate;

                break;
            }
        }

        if (!$heading instanceof DOMElement) {
            return [$html, false];
        }

        // Walk what follows the heading, grouping each question with
        // everything that belongs to it, and stop at the next h2.
        $items = [];
        $index = -1;

        for ($node = $heading->nextSibling; $node !== null; $node = $node->nextSibling) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);

            if ($tag === 'h2') {
                break;
            }

            if ($tag === 'h3') {
                $items[] = ['question' => $node, 'answer' => []];
                $index++;

                continue;
            }

            if ($index >= 0) {
                $items[$index]['answer'][] = $node;
            }
        }

        if (!$items) {
            return [$html, false];
        }

        $section = $document->createElement('section');
        $section->setAttribute('class', 'article-faq');

        $heading->parentNode->replaceChild($section, $heading);
        $section->appendChild($heading);

        $isFirst = true;

        foreach ($items as $item) {
            $wrapper = $document->createElement('div');
            $wrapper->setAttribute('class', 'accordion-item-wrapper');

            $trigger = $document->createElement('h3');
            $trigger->setAttribute('class', 'accordion' . ($isFirst ? ' active' : ''));
            $trigger->setAttribute('tabindex', '0');

            $questionText = $document->createElement('span');
            $questionText->setAttribute('class', 'question');
            $questionText->appendChild($document->createTextNode(trim($item['question']->textContent)));
            $trigger->appendChild($questionText);

            $panel = $document->createElement('div');
            $panel->setAttribute('class', 'art-panel');

            if ($isFirst) {
                $panel->setAttribute('style', 'max-height: 2000px;');
            }

            $panelData = $document->createElement('div');
            $panelData->setAttribute('class', 'panel-data');

            // appendChild moves the node, which also lifts it out of the body.
            foreach ($item['answer'] as $answerNode) {
                $panelData->appendChild($answerNode);
            }

            $panel->appendChild($panelData);

            $wrapper->appendChild($trigger);
            $wrapper->appendChild($panel);
            $section->appendChild($wrapper);

            $item['question']->parentNode?->removeChild($item['question']);

            $isFirst = false;
        }

        $result = '';

        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return [trim($result), true];
    }

    /**
     * Strips a FAQ written into the body as running text.
     *
     * convertInlineFaq only recognises questions written as headings. Serp
     * Agent also sends them as paragraphs — "П: question / В: answer" — which
     * cannot be turned into the accordion, so the article ended up carrying
     * the questions twice: once as flat text and once as the real accordion
     * built from the structured payload.
     *
     * The payload is the better copy of the two, so the flat one goes: the
     * heading and everything under it up to the next h2.
     */
    public function removeInlineFaq(string $html, string $locale): string
    {
        $html = trim($html);

        if ($html === '' || !class_exists(DOMDocument::class)) {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousUseErrors = libxml_use_internal_errors(true);

        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"?><div data-faq-root="1">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        if (!$loaded) {
            return $html;
        }

        $xpath = new DOMXPath($document);
        $root = $xpath->query('//div[@data-faq-root]')->item(0);

        if (!$root instanceof DOMElement) {
            return $html;
        }

        $heading = null;

        foreach ($xpath->query('//h2') as $candidate) {
            if ($this->isFaqHeading($candidate->textContent)) {
                $heading = $candidate;

                break;
            }
        }

        if (!$heading instanceof DOMElement) {
            return $html;
        }

        // Collect first: removing as we walk would cut the chain we follow.
        $doomed = [$heading];

        for ($node = $heading->nextSibling; $node !== null; $node = $node->nextSibling) {
            if ($node instanceof DOMElement && strtolower($node->tagName) === 'h2') {
                break;
            }

            $doomed[] = $node;
        }

        foreach ($doomed as $node) {
            $node->parentNode?->removeChild($node);
        }

        $result = '';

        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function isFaqHeading(string $text): bool
    {
        $text = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));

        foreach (self::HEADINGS['faq'] as $heading) {
            if ($text === mb_strtolower($heading)) {
                return true;
            }
        }

        return false;
    }

    public function buildFaqSection(array $faq, string $locale): string
    {
        if (!$faq) {
            return '';
        }

        $items = '';
        $isFirst = true;

        foreach ($faq as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = $this->sanitizeFragment((string) ($item['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            // The first entry starts open. The inline max-height is what the
            // site's own handler toggles, so the two stay in step, and the
            // answer is visible even if the script never runs.
            $openClass = $isFirst ? ' active' : '';
            $openStyle = $isFirst ? ' style="max-height: 2000px;"' : '';
            $isFirst = false;

            $items .= '<div class="accordion-item-wrapper">'
                . '<h3 class="accordion' . $openClass . '" tabindex="0">'
                . '<span class="question">' . e($question) . '</span>'
                . '</h3>'
                . '<div class="art-panel"' . $openStyle . '>'
                . '<div class="panel-data">' . $answer . '</div>'
                . '</div>'
                . '</div>';
        }

        if ($items === '') {
            return '';
        }

        return '<section class="article-faq">'
            . '<h2>' . e($this->heading('faq', $locale)) . '</h2>'
            . $items
            . '</section>';
    }

    /**
     * @param array<int, array{title?: string, url?: string}> $links
     */
    public function buildLinksSection(array $links, string $type, string $locale): string
    {
        if (!$links) {
            return '';
        }

        $items = '';

        foreach ($links as $link) {
            $title = trim((string) ($link['title'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));

            if ($title === '' || !$this->isSafeUrl($url)) {
                continue;
            }

            $url = $this->normalizeInternalUrl($url);

            $items .= '<li><a href="' . e($url) . '">' . e($title) . '</a></li>';
        }

        if ($items === '') {
            return '';
        }

        return '<section class="article-' . e($type) . '">'
            . '<h2>' . e($this->heading($type, $locale)) . '</h2>'
            . '<ul>' . $items . '</ul>'
            . '</section>';
    }

    public function toPlainText(?string $html): string
    {
        // Block level tags have to become whitespace, otherwise the last word
        // of a heading and the first word of the next paragraph glue together.
        $text = preg_replace(self::BLOCK_TAGS_PATTERN, ' ', (string) $html);
        $text = strip_tags((string) $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim((string) $text);
    }

    /**
     * Wraps a bare string in a paragraph, sanitizes anything that already
     * carries markup.
     */
    private function sanitizeFragment(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (!preg_match('/<[a-z!\/]/i', $value)) {
            return '<p>' . e($value) . '</p>';
        }

        return $this->sanitize($value);
    }

    private function heading(string $type, string $locale): string
    {
        $headings = self::HEADINGS[$type] ?? [];

        return $headings[$locale] ?? $headings['uk'] ?? '';
    }

    /**
     * A wide table would drag the whole article sideways on a phone, so each
     * one is given a wrapper the stylesheet can scroll on its own.
     */
    private function wrapTables(DOMDocument $document, DOMElement $root): void
    {
        $xpath = new DOMXPath($document);

        foreach (iterator_to_array($xpath->query('.//table', $root)) as $table) {
            $parent = $table->parentNode;

            if (!$parent instanceof DOMNode) {
                continue;
            }

            if ($parent instanceof DOMElement
                && str_contains($parent->getAttribute('class'), 'article-table')) {
                continue;
            }

            $wrapper = $document->createElement('div');
            $wrapper->setAttribute('class', 'article-table');

            $parent->replaceChild($wrapper, $table);
            $wrapper->appendChild($table);
        }
    }

    private function cleanChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMText) {
                continue;
            }

            if ($child instanceof DOMElement) {
                $this->cleanElement($child);
                continue;
            }

            // Comments, CDATA and processing instructions carry no content.
            $node->removeChild($child);
        }
    }

    private function cleanElement(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        if (in_array($tag, self::FORBIDDEN_TAGS, true)) {
            $element->parentNode?->removeChild($element);

            return;
        }

        // The article template already prints the article name as the page H1.
        if ($tag === 'h1' && config('serp-agent.demote_h1')) {
            $element = $this->renameElement($element, 'h2');
            $tag = 'h2';
        }

        if (!array_key_exists($tag, self::ALLOWED_TAGS)) {
            $this->cleanChildren($element);
            $this->unwrapElement($element);

            return;
        }

        $this->cleanAttributes($element, self::ALLOWED_TAGS[$tag]);
        $this->cleanChildren($element);
    }

    private function cleanAttributes(DOMElement $element, array $allowedAttributes): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);

            if (!in_array($name, $allowedAttributes, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if (in_array($name, ['href', 'src'], true)) {
                if (!$this->isSafeUrl($attribute->nodeValue)) {
                    $element->removeAttribute($attribute->nodeName);

                    continue;
                }

                if ($name === 'href') {
                    $element->setAttribute('href', $this->normalizeInternalUrl($attribute->nodeValue));
                }
            }
        }

        if (strtolower($element->tagName) === 'a' && strtolower((string) $element->getAttribute('target')) === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function renameElement(DOMElement $element, string $newTag): DOMElement
    {
        $newElement = $element->ownerDocument->createElement($newTag);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $newElement->setAttribute($attribute->nodeName, $attribute->nodeValue);
        }

        while ($element->firstChild) {
            $newElement->appendChild($element->firstChild);
        }

        $element->parentNode?->replaceChild($newElement, $element);

        return $newElement;
    }

    private function unwrapElement(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (!$parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    /**
     * Articles moved from /blog/article/{slug} to /blog/{slug}. Serp Agent
     * learned the old shape from the site and keeps emitting it in its link
     * lists, so those links are rewritten instead of bouncing every visitor
     * through a redirect. Only our own links are touched.
     */
    private function normalizeInternalUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return $url;
        }

        $parts = parse_url($url);

        if (!is_array($parts) || !isset($parts['path'])) {
            return $url;
        }

        if (isset($parts['host'])) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            if ($appHost === null || strcasecmp($parts['host'], $appHost) !== 0) {
                return $url;
            }
        }

        $path = preg_replace('#^((?:/(?:ru|uk))?)/blog/article/#i', '$1/blog/', $parts['path'], 1);

        if ($path === $parts['path']) {
            return $url;
        }

        $rebuilt = '';

        if (isset($parts['scheme'], $parts['host'])) {
            $rebuilt .= $parts['scheme'] . '://' . $parts['host'];

            if (isset($parts['port'])) {
                $rebuilt .= ':' . $parts['port'];
            }
        }

        $rebuilt .= $path;

        if (isset($parts['query'])) {
            $rebuilt .= '?' . $parts['query'];
        }

        if (isset($parts['fragment'])) {
            $rebuilt .= '#' . $parts['fragment'];
        }

        return $rebuilt;
    }

    private function isSafeUrl(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '') {
            // A relative path such as "blog/article/slug".
            return !str_contains($url, ':');
        }

        return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
    }

    /**
     * Fallback for the unlikely case that ext-dom is unavailable.
     */
    private function sanitizeWithoutDom(string $html): string
    {
        $html = preg_replace('#<(' . implode('|', self::FORBIDDEN_TAGS) . ')\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(' . implode('|', self::FORBIDDEN_TAGS) . ')\b[^>]*/?>#i', '', (string) $html);
        $html = strip_tags((string) $html, '<' . implode('><', array_keys(self::ALLOWED_TAGS)) . '>');
        $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', (string) $html);
        $html = preg_replace('/(href|src)\s*=\s*("|\')\s*(javascript|vbscript|data):[^"\']*\2/i', '', (string) $html);

        return trim((string) $html);
    }
}
