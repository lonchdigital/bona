<?php

namespace App\Http\Requests\Api\SerpAgent;

use App\Http\Requests\BaseRequest;
use App\Services\SerpAgent\DTO\SerpAgentArticleDTO;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSerpAgentArticleRequest extends BaseRequest
{
    public function authorize(): bool
    {
        // Authentication is done by the VerifySerpAgentWebhook middleware.
        return true;
    }

    /**
     * Serp Agent documents a camelCase payload, but the panel is not the only
     * possible sender and field names differ between its CMS presets. Every
     * supported spelling is folded into one canonical set of keys here, so the
     * rules below and the DTO only ever deal with a single shape.
     */
    protected function prepareForValidation(): void
    {
        [$imageUrl, $imageAlt] = $this->extractImage();

        $this->merge([
            'external_id' => $this->stringOfAny(['externalId', 'external_id', 'articleId', 'article_id', 'id']),
            'locale' => $this->normalizeLocale($this->stringOfAny(['locale', 'language', 'lang'])),
            'translation_group_id' => $this->stringOfAny([
                'translationGroupId', 'translation_group_id', 'translationGroup', 'groupId',
            ]),
            // A repeat delivery that only refreshes the language links.
            'serp_event' => $this->stringOfAny(['event', 'eventType', 'event_type', 'type']),
            'title' => $this->stringOfAny(['title', 'name']),
            'h1' => $this->stringOfAny(['h1', 'heading']),
            'slug' => $this->stringOfAny(['slug', 'permalink']),
            'content' => $this->stringOfAny(['content', 'html', 'body', 'contentHtml', 'content_html']),
            'excerpt' => $this->stringOfAny(['excerpt', 'summary', 'previewText', 'preview_text', 'description']),
            'meta_title' => $this->stringOfAny(['metaTitle', 'meta_title', 'seoTitle', 'seo_title']),
            'meta_description' => $this->stringOfAny(['metaDescription', 'meta_description', 'seoDescription', 'seo_description']),
            'meta_keywords' => $this->keywordsToString($this->firstOfAny(['metaKeywords', 'meta_keywords', 'keywords', 'tags'])),
            'image_url' => $imageUrl,
            'image_alt' => $imageAlt,
            'faq' => $this->normalizePairs(
                $this->firstOfAny(['faq', 'faqs', 'questions']),
                ['question', 'q', 'title'],
                ['answer', 'a', 'text', 'content']
            ),
            'related_articles' => $this->normalizePairs(
                $this->firstOfAny(['relatedArticles', 'related_articles', 'related']),
                ['title', 'name', 'label', 'anchor'],
                ['url', 'link', 'href']
            ),
            'recommended_resources' => $this->normalizePairs(
                $this->firstOfAny(['recommendedResources', 'recommended_resources', 'resources']),
                ['title', 'name', 'label', 'anchor'],
                ['url', 'link', 'href']
            ),
        ]);
    }

    public function baseRules(): array
    {
        return [
            'external_id' => ['nullable', 'string', 'max:191'],
            'translation_group_id' => ['nullable', 'string', 'max:191'],
            'serp_event' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'in:'.implode(',', $this->availableLanguages)],
            'title' => ['nullable', 'string', 'max:255'],
            'h1' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:191'],
            'content' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'string', 'url', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'max:255'],

            'faq' => ['array'],
            'faq.*.question' => ['required', 'string'],
            'faq.*.answer' => ['required', 'string'],

            'related_articles' => ['array'],
            'related_articles.*.question' => ['required', 'string'],
            'related_articles.*.answer' => ['required', 'string'],

            'recommended_resources' => ['array'],
            'recommended_resources.*.question' => ['required', 'string'],
            'recommended_resources.*.answer' => ['required', 'string'],
        ];
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    public function toDTO(): SerpAgentArticleDTO
    {
        return new SerpAgentArticleDTO(
            externalId: $this->input('external_id'),
            locale: $this->input('locale') ?: (string) config('serp-agent.default_locale'),
            translationGroupId: $this->input('translation_group_id'),
            isTranslationsUpdate: strtolower((string) $this->input('serp_event')) === 'translations_updated',
            title: $this->input('title'),
            h1: $this->input('h1'),
            slug: $this->input('slug'),
            content: $this->input('content'),
            excerpt: $this->input('excerpt'),
            metaTitle: $this->input('meta_title'),
            metaDescription: $this->input('meta_description'),
            metaKeywords: $this->input('meta_keywords'),
            imageUrl: $this->input('image_url'),
            imageAlt: $this->input('image_alt'),
            faq: $this->pairsToList($this->input('faq'), 'question', 'answer'),
            relatedArticles: $this->pairsToList($this->input('related_articles'), 'title', 'url'),
            recommendedResources: $this->pairsToList($this->input('recommended_resources'), 'title', 'url'),
        );
    }

    /**
     * The endpoint always answers with JSON, never with a redirect to a form.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'The received payload is invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }

    private function firstOfAny(array $keys): mixed
    {
        foreach ($keys as $key) {
            $value = $this->input($key);

            if ($value !== null && $value !== '' && $value !== []) {
                return $value;
            }
        }

        return null;
    }

    private function stringOfAny(array $keys): ?string
    {
        $value = $this->firstOfAny($keys);

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * @return array{0: ?string, 1: ?string} [url, alt]
     */
    private function extractImage(): array
    {
        $image = $this->firstOfAny([
            'image', 'imageUrl', 'image_url', 'featuredImage', 'featured_image',
            'heroImage', 'hero_image', 'coverImage', 'cover_image', 'thumbnail',
        ]);

        if (is_string($image)) {
            return [trim($image) ?: null, null];
        }

        if (is_array($image)) {
            $url = $image['url'] ?? $image['src'] ?? $image['link'] ?? null;
            $alt = $image['alt'] ?? $image['title'] ?? $image['caption'] ?? null;

            return [
                is_string($url) && trim($url) !== '' ? trim($url) : null,
                is_string($alt) && trim($alt) !== '' ? trim($alt) : null,
            ];
        }

        return [null, null];
    }

    private function keywordsToString(mixed $value): ?string
    {
        if (is_string($value)) {
            return trim($value) ?: null;
        }

        if (is_array($value)) {
            $keywords = [];

            foreach ($value as $keyword) {
                if (is_string($keyword) && trim($keyword) !== '') {
                    $keywords[] = trim($keyword);
                } elseif (is_array($keyword)) {
                    $name = $keyword['name'] ?? $keyword['title'] ?? $keyword['value'] ?? null;

                    if (is_string($name) && trim($name) !== '') {
                        $keywords[] = trim($name);
                    }
                }
            }

            return $keywords ? implode(', ', $keywords) : null;
        }

        return null;
    }

    /**
     * Reduces a list of objects to a uniform [{question, answer}] shape,
     * silently dropping entries that carry no usable pair.
     */
    private function normalizePairs(mixed $items, array $firstKeys, array $secondKeys): array
    {
        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $first = $this->pluckString($item, $firstKeys);
            $second = $this->pluckString($item, $secondKeys);

            if ($first === null || $second === null) {
                continue;
            }

            $normalized[] = ['question' => $first, 'answer' => $second];
        }

        return $normalized;
    }

    private function pluckString(array $item, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($item[$key]) && is_string($item[$key]) && trim($item[$key]) !== '') {
                return trim($item[$key]);
            }
        }

        return null;
    }

    /**
     * Turns the internal {question, answer} pairs back into the names the DTO
     * uses for that particular list.
     */
    private function pairsToList(mixed $pairs, string $firstKey, string $secondKey): array
    {
        if (! is_array($pairs)) {
            return [];
        }

        $list = [];

        foreach ($pairs as $pair) {
            if (! is_array($pair) || ! isset($pair['question'], $pair['answer'])) {
                continue;
            }

            $list[] = [
                $firstKey => $pair['question'],
                $secondKey => $pair['answer'],
            ];
        }

        return $list;
    }

    private function normalizeLocale(?string $locale): ?string
    {
        if ($locale === null) {
            return null;
        }

        $locale = strtolower(str_replace('_', '-', trim($locale)));
        $locale = explode('-', $locale)[0];

        // The site stores Ukrainian under "uk", Serp Agent may send "ua".
        if ($locale === 'ua') {
            $locale = 'uk';
        }

        return in_array($locale, $this->availableLanguages, true) ? $locale : null;
    }
}
