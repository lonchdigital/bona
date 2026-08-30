<?php

namespace App\Services\SerpAgent\DTO;

use App\Services\Base\DTO\BaseDTO;

class SerpAgentArticleDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $externalId,
        public readonly string $locale,
        /** Ties the language versions of one article together. */
        public readonly ?string $translationGroupId,
        /** True when this delivery only refreshes the language links. */
        public readonly bool $isTranslationsUpdate,
        public readonly ?string $title,
        public readonly ?string $h1,
        public readonly ?string $slug,
        public readonly ?string $content,
        public readonly ?string $excerpt,
        public readonly ?string $metaTitle,
        public readonly ?string $metaDescription,
        public readonly ?string $metaKeywords,
        public readonly ?string $imageUrl,
        public readonly ?string $imageAlt,
        /** @var array<int, array{question: string, answer: string}> */
        public readonly array $faq,
        /** @var array<int, array{title: string, url: string}> */
        public readonly array $relatedArticles,
        /** @var array<int, array{title: string, url: string}> */
        public readonly array $recommendedResources,
    ) {}

    /**
     * The heading used for the article itself. Serp Agent sends both "title"
     * (SEO title) and "h1" (on-page heading); the visible one wins.
     */
    public function heading(): ?string
    {
        return $this->h1 ?: $this->title;
    }

    /**
     * A payload that carries neither a heading nor a body is a bare
     * connectivity check.
     */
    public function isConnectivityCheck(): bool
    {
        return $this->heading() === null && trim((string) $this->content) === '';
    }

    /**
     * The panel's "Save & Test" button sends a complete demo article instead of
     * a ping, pointing at an image URL that is not actually served. It is
     * recognised by its fixed slug so the test can succeed without a throwaway
     * article appearing on the live blog.
     *
     * @param  array<int, string>  $testSlugs
     */
    public function isTestDelivery(array $testSlugs): bool
    {
        if ($this->slug === null) {
            return false;
        }

        $slug = strtolower(trim($this->slug));

        foreach ($testSlugs as $testSlug) {
            if ($slug === strtolower(trim((string) $testSlug))) {
                return true;
            }
        }

        return false;
    }
}
