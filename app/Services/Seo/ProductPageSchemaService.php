<?php

namespace App\Services\Seo;

use App\DataClasses\ProductStatusDataClass;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Support\Str;

final class ProductPageSchemaService
{
    public function __construct(
        private readonly OrganizationSchemaService $organizationSchema,
    ) {}

    /**
     * Build one truthful Product entity for a product detail page.
     *
     * Google requires at least one of offers, review or aggregateRating. When
     * a product has neither a sale price nor an approved review, omitting the
     * Product entity is preferable to publishing an invalid or invented one.
     *
     * @param  array<int, string>  $images
     * @param  array<int, array<string, mixed>>  $additionalProperties
     * @param  iterable<int, mixed>  $reviews
     * @param  array{count: int, average: float|int, best: int, worst: int}|null  $ratingSummary
     */
    public function build(
        Product $product,
        Currency $currency,
        string $url,
        string $webPageId,
        array $images = [],
        ?string $description = null,
        array $additionalProperties = [],
        iterable $reviews = [],
        ?array $ratingSummary = null,
    ): ?array {
        $reviewNodes = collect($reviews)
            ->take(10)
            ->map(fn ($review) => array_filter([
                '@type' => 'Review',
                'author' => filled($review->author_name ?? null) ? [
                    '@type' => 'Person',
                    'name' => trim((string) $review->author_name),
                ] : null,
                'datePublished' => $review->publishedDate()?->toDateString(),
                'reviewRating' => is_numeric($review->rating ?? null) ? [
                    '@type' => 'Rating',
                    'ratingValue' => (int) $review->rating,
                    'bestRating' => 5,
                    'worstRating' => 1,
                ] : null,
                'reviewBody' => filled($review->review ?? null) ? trim((string) $review->review) : null,
            ], fn (mixed $value) => $value !== null && $value !== ''))
            ->filter(fn (array $review) => isset($review['author'], $review['reviewRating']))
            ->values()
            ->all();

        $aggregateRating = $this->aggregateRating($ratingSummary);
        $offer = $this->offer($product, $currency, $url);

        if ($offer === null && $aggregateRating === null && $reviewNodes === []) {
            return null;
        }

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            '@id' => $url.'#product',
            'mainEntityOfPage' => ['@id' => $webPageId],
            'name' => trim((string) $product->name),
            'url' => $url,
            'sku' => filled($product->sku) ? trim((string) $product->sku) : null,
            'image' => collect($images)->filter()->unique()->values()->all() ?: null,
            'description' => filled($description) ? Str::limit(trim((string) $description), 900) : null,
            'category' => filled($product->productType?->name) ? trim((string) $product->productType->name) : null,
            'color' => $this->primaryColor($product),
            'brand' => filled($product->brand?->name) ? [
                '@type' => 'Brand',
                'name' => trim((string) $product->brand->name),
            ] : null,
            'additionalProperty' => $additionalProperties ?: null,
            'aggregateRating' => $aggregateRating,
            'review' => $reviewNodes ?: null,
            'offers' => $offer,
        ], fn (mixed $value) => $value !== null && $value !== '' && $value !== []);
    }

    private function offer(Product $product, Currency $currency, string $url): ?array
    {
        if (! is_numeric($product->price) || (float) $product->price <= 0) {
            return null;
        }

        return array_filter([
            '@type' => 'Offer',
            'url' => $url,
            'price' => number_format((float) $product->price, 2, '.', ''),
            'priceCurrency' => $this->currencyCode($currency),
            'availability' => $this->availability($product->availability_status_id),
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => ['@id' => $this->organizationSchema->organizationId()],
            'hasMerchantReturnPolicy' => ['@id' => $this->organizationSchema->merchantReturnPolicyId()],
        ], fn (mixed $value) => $value !== null && $value !== '');
    }

    private function currencyCode(Currency $currency): string
    {
        $code = strtoupper(trim((string) $currency->code));

        return preg_match('/^[A-Z]{3}$/', $code) === 1 ? $code : 'UAH';
    }

    private function availability(?int $status): ?string
    {
        return match ($status) {
            ProductStatusDataClass::PRODUCT_STATUS_STOCK => 'https://schema.org/InStock',
            ProductStatusDataClass::PRODUCT_STATUS_ORDER => 'https://schema.org/BackOrder',
            ProductStatusDataClass::PRODUCT_STATUS_OUT_OF_STOCK => 'https://schema.org/OutOfStock',
            ProductStatusDataClass::PRODUCT_STATUS_OUT_ASK_MANAGER => 'https://schema.org/LimitedAvailability',
            default => null,
        };
    }

    private function primaryColor(Product $product): ?string
    {
        $name = trim((string) $product->colors->first()?->name);

        // Google Merchant Center accepts at most 40 characters per color.
        return $name !== '' && mb_strlen($name) <= 40 ? $name : null;
    }

    private function aggregateRating(?array $summary): ?array
    {
        if (! is_array($summary)
            || (int) ($summary['count'] ?? 0) < 1
            || ! is_numeric($summary['average'] ?? null)) {
            return null;
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => (float) $summary['average'],
            'reviewCount' => (int) $summary['count'],
            'bestRating' => (int) ($summary['best'] ?? 5),
            'worstRating' => (int) ($summary['worst'] ?? 1),
        ];
    }
}
