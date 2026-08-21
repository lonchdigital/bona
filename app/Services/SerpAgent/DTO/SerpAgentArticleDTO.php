<?php

namespace App\Services\SerpAgent\DTO;

use App\Services\Base\DTO\BaseDTO;

class SerpAgentArticleDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $externalId,
        public readonly string $locale,
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
    ) { }

    /**
     * The heading used for the article itself. Serp Agent sends both "title"
     * (SEO title) and "h1" (on-page heading); the visible one wins.
     */
    public function heading(): ?string
    {
        return $this->h1 ?: $this->title;
    }

    /**
     * A payload that carries neither a heading nor a body is treated as the
     * connectivity check behind the panel's "Save & Test" button.
     */
    public function isConnectivityCheck(): bool
    {
        return $this->heading() === null && trim((string) $this->content) === '';
    }
}
