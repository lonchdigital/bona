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

    private function rewriteUrls(bool $toManufacturerCatalog): void
    {
        if (! Schema::hasTable('catalog_menu_configurations')) {
            return;
        }

        DB::table('catalog_menu_configurations')
            ->join('product_types', 'product_types.id', '=', 'catalog_menu_configurations.product_type_id')
            ->select([
                'catalog_menu_configurations.id',
                'catalog_menu_configurations.columns',
                'product_types.slug as product_type_slug',
            ])
            ->orderBy('catalog_menu_configurations.id')
            ->get()
            ->each(function ($configuration) use ($toManufacturerCatalog) {
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
                            $rewrittenUrl = $toManufacturerCatalog
                                ? $this->toManufacturerCatalogUrl($currentUrl, $configuration->product_type_slug, $locale)
                                : $this->toLegacyBrandUrl($currentUrl, $locale);

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

    private function toManufacturerCatalogUrl(string $url, string $productTypeSlug, string $locale): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! preg_match('#^/(?:ru/)?brands/([a-z0-9-]+)/?$#i', $path, $matches)) {
            return $url;
        }

        return ($locale === 'ru' ? '/ru' : '')
            .'/product-category/'.$productTypeSlug.'/manufacturer/'.mb_strtolower($matches[1]);
    }

    private function toLegacyBrandUrl(string $url, string $locale): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! preg_match('#^/(?:ru/)?product-category/[^/]+/manufacturer/([a-z0-9-]+)/?$#i', $path, $matches)) {
            return $url;
        }

        return ($locale === 'ru' ? '/ru' : '').'/brands/'.mb_strtolower($matches[1]);
    }
};
