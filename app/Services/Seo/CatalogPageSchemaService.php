<?php

namespace App\Services\Seo;

use App\Helpers\MultiLangRoute;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

final class CatalogPageSchemaService
{
    public function __construct(
        private readonly OrganizationSchemaService $organizationSchema,
    ) {}

    /**
     * Describe the visible catalogue result set and its navigation path. The
     * list intentionally links to Product entities instead of duplicating
     * incomplete offers on a multi-product page; full merchant data lives on
     * each product detail page.
     *
     * @param  array<int, array{label: mixed, url?: mixed}>  $breadcrumbs
     */
    public function build(
        string $title,
        ?string $description,
        string $canonical,
        array $breadcrumbs,
        LengthAwarePaginator $products,
    ): array {
        $language = app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA';
        $pageId = $canonical.'#webpage';
        $breadcrumbId = $canonical.'#breadcrumb';
        $itemListId = $canonical.'#products';
        $description = trim((string) preg_replace(
            '/\s+/u',
            ' ',
            html_entity_decode(strip_tags((string) $description), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ));

        $breadcrumbItems = [[
            '@type' => 'ListItem',
            'position' => 1,
            'name' => trans('base.home'),
            'item' => url(MultiLangRoute::getMultiLangRoute('store.home')),
        ]];

        foreach (array_values($breadcrumbs) as $index => $breadcrumb) {
            $url = filled($breadcrumb['url'] ?? null)
                ? $this->absoluteUrl((string) $breadcrumb['url'])
                : $canonical;

            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 2,
                'name' => trim((string) ($breadcrumb['label'] ?? '')),
                'item' => $url,
            ];
        }

        $listItems = collect($products->items())
            ->values()
            ->map(function (Product $product, int $index) use ($products) {
                $productUrl = url(MultiLangRoute::getMultiLangRoute('store.product.page', [
                    'productSlug' => $product->slug,
                ]));
                $image = $product->main_image_url ?: $product->preview_image_url;

                return array_filter([
                    '@type' => 'ListItem',
                    'position' => ($products->firstItem() ?? 1) + $index,
                    'item' => array_filter([
                        '@type' => 'Product',
                        '@id' => $productUrl.'#product',
                        'url' => $productUrl,
                        'name' => (string) $product->name,
                        'image' => $image ? $this->absoluteUrl($image) : null,
                        'brand' => $product->brand ? [
                            '@type' => 'Brand',
                            'name' => (string) $product->brand->name,
                        ] : null,
                    ], fn (mixed $value) => $value !== null && $value !== ''),
                ], fn (mixed $value) => $value !== null && $value !== '');
            })
            ->all();

        $itemList = [
            '@type' => 'ItemList',
            '@id' => $itemListId,
            'name' => $title,
            'numberOfItems' => $products->total(),
            'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
            'itemListElement' => $listItems,
        ];

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'CollectionPage',
                    '@id' => $pageId,
                    'url' => $canonical,
                    'name' => $title,
                    'description' => $description ?: null,
                    'inLanguage' => $language,
                    'isPartOf' => ['@id' => $this->organizationSchema->websiteId()],
                    'about' => ['@id' => $this->organizationSchema->organizationId()],
                    'breadcrumb' => ['@id' => $breadcrumbId],
                    'mainEntity' => ['@id' => $itemListId],
                ], fn (mixed $value) => $value !== null && $value !== ''),
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $breadcrumbId,
                    'itemListElement' => $breadcrumbItems,
                ],
                $itemList,
            ],
        ];
    }

    private function absoluteUrl(string $value): string
    {
        return Str::startsWith($value, ['http://', 'https://']) ? $value : url($value);
    }
}
