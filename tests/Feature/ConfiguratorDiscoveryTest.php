<?php

namespace Tests\Feature;

use App\Helpers\MultiLangRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\Support\MakesShopData;
use Tests\TestCase;

class ConfiguratorDiscoveryTest extends TestCase
{
    use MakesShopData;
    use RefreshDatabase;

    public function test_all_navigation_entry_points_are_localized_and_topbar_order_is_correct(): void
    {
        $type = $this->productType();
        foreach (['uk', 'ru'] as $locale) {
            app()->setLocale($locale);
            $url = MultiLangRoute::getMultiLangRoute('store.door-configurator.page');
            $header = Blade::render('<x-store.site-header :product-types="$types" />', ['types' => collect([$type])]);
            $footer = Blade::render('<x-store.site-footer />');
            $promo = Blade::render('<x-store.home-configurator />');
            $xpath = $this->xpath($header);

            $topbarLinks = $xpath->query('//nav[contains(@class,"bona-topbar__nav")]/a');
            $this->assertSame(trans('base.our_works'), $topbarLinks->item(4)->textContent);
            $this->assertSame(trans('configurator.nav_label'), $topbarLinks->item(5)->textContent);
            $this->assertSame($url, $topbarLinks->item(5)->getAttribute('href'));
            $this->assertSame(trans('base.contacts'), $topbarLinks->item(6)->textContent);

            $menuLinks = $xpath->query('//a[contains(@class,"bona-configurator-menu-link")]');
            $this->assertCount(2, $menuLinks); // Desktop catalog and mobile catalog.
            foreach ($menuLinks as $link) {
                $this->assertSame($url, $link->getAttribute('href'));
            }
            $this->assertSame(0, $xpath->query('//*[@role="tablist"]/a')->count());
            $this->assertSame(1, $this->xpath($footer)->query('//nav[@aria-labelledby="footer-navigation-title"]//a[@href="'.$url.'"]')->count());
            $this->assertStringContainsString('href="'.$url.'"', $promo);
            $this->assertStringContainsString(trans('configurator.promo.heading'), $promo);
        }
    }

    public function test_footer_does_not_duplicate_an_existing_custom_configurator_link(): void
    {
        foreach (['uk', 'ru'] as $locale) {
            app()->setLocale($locale);
            $options = ['footerNavigation' => [[
                'label' => ['uk' => 'Наша примірка', 'ru' => 'Наша примерка'],
                'url' => ['uk' => '/door-configurator/', 'ru' => '/ru/door-configurator/'],
                'is_visible' => true, 'sort_order' => 0,
            ]]];
            $footer = Blade::render('<x-store.site-footer :options="$options" />', compact('options'));
            $links = $this->xpath($footer)->query('//nav[@aria-labelledby="footer-navigation-title"]//a[contains(@href,"door-configurator")]');
            $this->assertCount(1, $links);
            $this->assertStringContainsString($locale === 'uk' ? 'Наша примірка' : 'Наша примерка', $links->item(0)->textContent);
        }
    }

    public function test_catalog_guidance_opens_the_localized_configurator_instead_of_consultation(): void
    {
        $this->seedCurrency();
        $types = collect(['interior-doors', 'entrance-doors'])->map(function (string $slug) {
            $type = $this->productType(['slug' => $slug]);
            $this->makeProduct(['product_type_id' => $type->id]);

            return $type;
        });

        foreach (['uk', 'ru'] as $locale) {
            $prefix = $locale === 'uk' ? '' : 'localized.';
            $params = $locale === 'uk' ? [] : ['lang' => $locale];
            $target = route($prefix.'store.door-configurator.page', $params, false);
            $urls = [route($prefix.'store.all-products.page', $params)];
            foreach ($types as $type) {
                $catalogParams = [...$params, 'productTypeSlug' => $type->slug];
                $urls[] = route($prefix.'store.catalog.page', $catalogParams);
                $urls[] = route($prefix.'store.catalog.filter.page', [...$catalogParams, 'catalogFiltersString' => 'per_page=48']);
            }

            foreach ($urls as $url) {
                $response = $this->get($url)->assertOk();
                $xpath = $this->xpath($response->getContent());
                $guidance = $xpath->query('//div[@class="bona-catalog__guidance"]')->item(0);
                $this->assertNotNull($guidance);
                $this->assertStringContainsString(trans('base.catalog_guidance_text', [], $locale), $guidance->textContent);
                $links = $xpath->query('.//a', $guidance);
                $this->assertCount(1, $links);
                $link = $links->item(0);
                $this->assertSame($target, $link->getAttribute('href'));
                $this->assertStringContainsString(trans('base.catalog_guidance_action', [], $locale), $link->textContent);
                $this->assertFalse($link->hasAttribute('data-lead-modal-open'));
                $this->assertFalse($link->hasAttribute('data-toggle'));
            }
        }
    }

    private function xpath(string $html): \DOMXPath
    {
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.$html);

        return new \DOMXPath($document);
    }
}
