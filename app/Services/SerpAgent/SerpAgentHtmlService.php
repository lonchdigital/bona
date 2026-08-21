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

        $sanitized = '';

        foreach ($root->childNodes as $child) {
            $sanitized .= $document->saveHTML($child);
        }

        return trim($sanitized);
    }

    public function buildFaqSection(array $faq, string $locale): string
    {
        if (!$faq) {
            return '';
        }

        $items = '';

        foreach ($faq as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = $this->sanitizeFragment((string) ($item['answer'] ?? ''));

            if ($question === '' || $answer === '') {
                continue;
            }

            $items .= '<div class="article-faq__item">'
                . '<h3 class="article-faq__question">' . e($question) . '</h3>'
                . '<div class="article-faq__answer">' . $answer . '</div>'
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

            if (in_array($name, ['href', 'src'], true) && !$this->isSafeUrl($attribute->nodeValue)) {
                $element->removeAttribute($attribute->nodeName);
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
