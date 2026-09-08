<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->rewriteUrls(true);
    }

    public function down(): void
    {
        $this->rewriteUrls(false);
    }

    private function rewriteUrls(bool $toFilteredCatalog): void
    {
        if (! Schema::hasTable('catalog_menu_configurations') || ! Schema::hasTable('colors')) {
            return;
        }

        $colors = DB::table('colors')->select(['id', 'slug'])->get();
        $colorsById = $colors->keyBy(fn ($color) => (string) $color->id);
        $colorsBySlug = $colors->keyBy(fn ($color) => mb_strtolower((string) $color->slug));

        DB::table('catalog_menu_configurations')
            ->join('product_types', 'product_types.id', '=', 'catalog_menu_configurations.product_type_id')
            ->select([
                'catalog_menu_configurations.id',
                'catalog_menu_configurations.columns',
                'product_types.slug as product_type_slug',
            ])
            ->orderBy('catalog_menu_configurations.id')
            ->get()
            ->each(function ($configuration) use ($toFilteredCatalog, $colorsById, $colorsBySlug): void {
                $columns = json_decode($configuration->columns ?? '[]', true);

                if (! is_array($columns)) {
                    return;
                }

                $changed = false;

                foreach ($columns as &$column) {
                    if (! isset($column['items']) || ! is_array($column['items'])) {
                        continue;
                    }

                    foreach ($column['items'] as &$item) {
                        foreach (['uk', 'ru'] as $locale) {
                            $currentUrl = (string) ($item['url'][$locale] ?? '');
                            $rewrittenUrl = $toFilteredCatalog
                                ? $this->toFilteredCatalogUrl(
                                    $currentUrl,
                                    (string) $configuration->product_type_slug,
                                    $locale,
                                    $colorsById,
                                    $colorsBySlug,
                                )
                                : $this->toLegacyColorUrl(
                                    $currentUrl,
                                    (string) $configuration->product_type_slug,
                                    $locale,
                                    $colorsBySlug,
                                );

                            if ($rewrittenUrl !== $currentUrl) {
                                $item['url'][$locale] = $rewrittenUrl;
                                $changed = true;
                            }
                        }
                    }
                    unset($item);
                }
                unset($column);

                if ($changed) {
                    DB::table('catalog_menu_configurations')
                        ->where('id', $configuration->id)
                        ->update([
                            'columns' => json_encode($columns, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    private function toFilteredCatalogUrl(
        string $url,
        string $productTypeSlug,
        string $locale,
        $colorsById,
        $colorsBySlug,
    ): string {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! preg_match(
            '#^/(?:ru/)?product-category/(?:(?<product_type>[a-z0-9-]+)/)?color/(?<color>[a-z0-9-]+)/?$#i',
            $path,
            $matches,
        )) {
            return $url;
        }

        $linkedProductType = $matches['product_type'] ?? null;

        if ($linkedProductType && mb_strtolower($linkedProductType) !== mb_strtolower($productTypeSlug)) {
            return $url;
        }

        $identifier = (string) $matches['color'];
        $color = ctype_digit($identifier)
            ? $colorsById->get($identifier)
            : $colorsBySlug->get(mb_strtolower($identifier));

        if (! $color) {
            return $url;
        }

        return $this->appendQuery(
            ($locale === 'ru' ? '/ru' : '')
                .'/product-category/'.$productTypeSlug.'/filter/color='.$color->slug,
            $url,
        );
    }

    private function toLegacyColorUrl(
        string $url,
        string $productTypeSlug,
        string $locale,
        $colorsBySlug,
    ): string {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! preg_match(
            '#^/(?:ru/)?product-category/(?<product_type>[a-z0-9-]+)/filter/color=(?<color>[a-z0-9-]+)/?$#i',
            $path,
            $matches,
        )) {
            return $url;
        }

        if (mb_strtolower($matches['product_type']) !== mb_strtolower($productTypeSlug)) {
            return $url;
        }

        $color = $colorsBySlug->get(mb_strtolower((string) $matches['color']));

        if (! $color) {
            return $url;
        }

        return $this->appendQuery(
            ($locale === 'ru' ? '/ru' : '')
                .'/product-category/'.$productTypeSlug.'/color/'.$color->id,
            $url,
        );
    }

    private function appendQuery(string $target, string $source): string
    {
        $query = parse_url($source, PHP_URL_QUERY);

        return is_string($query) && $query !== '' ? $target.'?'.$query : $target;
    }
};
