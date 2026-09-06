@if(! ($searchQuery ?? null))
    @push('structured_data')
        <script type="application/ld+json">{!! json_encode(
            app(App\Services\Seo\CatalogPageSchemaService::class)->build(
                (string) $catalogPageTitle,
                $catalogMetaDescription ?? null,
                (string) $catalogCanonicalUrl,
                $breadcrumbs ?? [],
                $productsPaginated,
            ),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
        ) !!}</script>
    @endpush
@endif
