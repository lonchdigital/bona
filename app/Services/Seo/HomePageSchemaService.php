<?php

namespace App\Services\Seo;

use App\Helpers\MultiLangRoute;
use App\Helpers\PreviewImage;
use App\Models\HomePageConfig;
use App\Models\Product;
use Illuminate\Support\Collection;

final class HomePageSchemaService
{
    public function __construct(
        private readonly OrganizationSchemaService $organizationSchema,
    ) {}

    /**
     * Describe only entities that are genuinely present on the homepage.
     * This is deliberately one connected graph: the page belongs to the
     * website, the website is published by the business, and the visible
     * popular models are represented as an ordered list.
     */
    public function build(
        HomePageConfig $config,
        Collection $slides,
        Collection $popularProducts,
    ): array {
        $locale = app()->getLocale();
        $language = $locale === 'ru' ? 'ru-UA' : 'uk-UA';
        $canonical = $locale === 'ru' ? url('/ru') : url('/');
        $title = trim((string) $config->meta_title)
            ?: trans('base.home_meta_title');
        $description = trim((string) $config->meta_description)
            ?: trans('base.home_meta_description');
        $heroImage = PreviewImage::url(optional($slides->first())->slide_image_path);
        $webPageId = $canonical.'#webpage';
        $itemListId = $canonical.'#popular-models';

        $webPage = array_filter([
            '@type' => 'WebPage',
            '@id' => $webPageId,
            'url' => $canonical,
            'name' => $title,
            'description' => $description,
            'inLanguage' => $language,
            'isPartOf' => ['@id' => $this->organizationSchema->websiteId()],
            'about' => ['@id' => $this->organizationSchema->organizationId()],
            'primaryImageOfPage' => $heroImage ? [
                '@type' => 'ImageObject',
                '@id' => $canonical.'#primaryimage',
                'url' => $heroImage,
                'contentUrl' => $heroImage,
                'caption' => $title,
            ] : null,
            'dateModified' => $config->updated_at?->toAtomString(),
            'mainEntity' => $popularProducts->isNotEmpty() ? ['@id' => $itemListId] : null,
        ], fn (mixed $value) => $value !== null && $value !== '');

        $graph = [
            [
                '@type' => 'WebSite',
                '@id' => $this->organizationSchema->websiteId(),
                'url' => url('/'),
                'name' => (string) config('organization.name', 'Bona'),
                'alternateName' => 'Bona Doors',
                'inLanguage' => ['uk-UA', 'ru-UA'],
                'publisher' => ['@id' => $this->organizationSchema->organizationId()],
            ],
            $webPage,
        ];

        if ($popularProducts->isNotEmpty()) {
            $graph[] = [
                '@type' => 'ItemList',
                '@id' => $itemListId,
                'name' => trans('base.home_popular_title'),
                'numberOfItems' => $popularProducts->count(),
                'itemListElement' => $popularProducts
                    ->values()
                    ->map(fn (Product $product, int $index) => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'url' => MultiLangRoute::getMultiLangRoute('store.product.page', [
                            'productSlug' => $product->slug,
                        ]),
                        'name' => (string) $product->name,
                        'image' => url($product->main_image_url ?: $product->preview_image_url),
                    ])
                    ->all(),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }
}
