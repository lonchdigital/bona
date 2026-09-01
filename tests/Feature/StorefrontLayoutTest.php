<?php

namespace Tests\Feature;

use App\Models\ContactConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_the_new_header_and_hero_without_seed_data(): void
    {
        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('data-site-header', false)
            ->assertSee('bona-site-header--overlay', false)
            ->assertSee('data-home-hero', false)
            ->assertDontSee('art-contact-form-section', false)
            ->assertDontSee('art-quote-carousel-home', false)
            ->assertDontSee('art-brands-owl-items', false);
    }

    public function test_homepage_interactive_controls_use_valid_aria_contracts(): void
    {
        $search = file_get_contents(resource_path('views/components/store/search.blade.php'));
        $hero = file_get_contents(resource_path('views/components/store/home-hero.blade.php'));
        $heroScript = file_get_contents(resource_path('js/store/common/home-hero.js'));

        $this->assertStringContainsString('role="combobox"', $search);
        $this->assertStringContainsString('class="bona-hero__dots" role="group"', $hero);
        $this->assertStringContainsString('aria-current="{{ $loop->first', $hero);
        $this->assertStringNotContainsString('class="bona-hero__dots" role="tablist"', $hero);
        $this->assertStringContainsString("dot.setAttribute('aria-current', String(active))", $heroScript);
        $this->assertStringNotContainsString("dot.setAttribute('aria-selected'", $heroScript);
    }

    public function test_internal_page_renders_the_solid_header_without_seed_data(): void
    {
        $this->get(route('store.services'))
            ->assertOk()
            ->assertSee('bona-site-header--solid', false);
    }

    public function test_mobile_header_overrides_legacy_spacing_and_uses_readable_action_icons(): void
    {
        $stylesheet = file_get_contents(resource_path('scss/storefront/_redesign.scss'));

        $this->assertStringContainsString(".bona-header {\n    margin-top: 0;", $stylesheet);
        $this->assertStringContainsString('gap: 17px;', $stylesheet);
        $this->assertStringContainsString('svg { width: 23px; height: 23px; }', $stylesheet);
        $this->assertStringContainsString('.icon-cart::before { font-size: 23px; }', $stylesheet);
        $this->assertStringContainsString('.bona-header__actions { gap: 14px; }', $stylesheet);
    }

    public function test_mobile_bottom_navigation_uses_real_storefront_destinations_in_both_languages(): void
    {
        $this->get(route('store.services'))
            ->assertOk()
            ->assertSee('data-mobile-bottom-navigation', false)
            ->assertSee('data-mobile-bottom-categories', false)
            ->assertSee('Категорії')
            ->assertSee('Кошик')
            ->assertSee('Обране')
            ->assertSee('Порівняння')
            ->assertSee('Кабінет')
            ->assertSee('href="/shop"', false)
            ->assertSee('href="/cart"', false)
            ->assertSee('href="/compare"', false);

        $this->get(route('localized.store.services', ['lang' => 'ru']))
            ->assertOk()
            ->assertSee('Категории')
            ->assertSee('Корзина')
            ->assertSee('Избранное')
            ->assertSee('Сравнение')
            ->assertSee('Кабинет')
            ->assertSee('href="/ru/shop"', false)
            ->assertSee('href="/ru/cart"', false)
            ->assertSee('href="/ru/compare"', false);
    }

    public function test_mobile_bottom_navigation_has_directional_scroll_and_menu_contracts(): void
    {
        $source = file_get_contents(resource_path('js/store/common/mobile-bottom-navigation.js'));

        $this->assertStringContainsString("const MOBILE_BREAKPOINT = '(max-width: 960px)'", $source);
        $this->assertStringContainsString('const MIN_SCROLL_DELTA = 8', $source);
        $this->assertStringContainsString('setVisible(delta > 0)', $source);
        $this->assertStringContainsString("document.body.classList.contains('bona-menu-open')", $source);
        $this->assertStringContainsString('menuToggle.click()', $source);
        $this->assertStringContainsString("window.addEventListener('scroll', scheduleUpdate, { passive: true })", $source);
    }

    public function test_mobile_footer_places_navigation_and_categories_side_by_side(): void
    {
        $footer = file_get_contents(resource_path('views/components/store/site-footer.blade.php'));
        $stylesheet = file_get_contents(resource_path('scss/storefront/_redesign.scss'));

        $this->assertSame(2, substr_count($footer, 'class="bona-footer__nav"'));
        $this->assertStringContainsString('class="bona-footer__stores"', $footer);
        $this->assertStringContainsString(".bona-footer__grid {\n        grid-template-columns: repeat(2, minmax(0, 1fr));\n        gap: 42px 20px;", $stylesheet);
        $this->assertStringContainsString(".bona-footer__brand,\n    .bona-footer__stores { grid-column: 1 / -1; }", $stylesheet);
    }

    public function test_footer_aligns_store_addresses_and_links_them_to_maps_without_language_switcher(): void
    {
        $footer = file_get_contents(resource_path('views/components/store/site-footer.blade.php'));
        $stylesheet = file_get_contents(resource_path('scss/storefront/_redesign.scss'));

        $this->assertStringContainsString('StoreLocations::from($contacts)', $footer);
        $this->assertStringContainsString('class="bona-footer__address-link"', $footer);
        $this->assertStringContainsString("href=\"{{ \$store['map_url'] }}\"", $footer);
        $this->assertStringNotContainsString('bona-footer__languages', $footer);
        $this->assertStringNotContainsString('LocaleService::alternateLinks', $footer);
        $this->assertStringContainsString("&__stores {\n        min-width: 0;\n        padding-block: 0;", $stylesheet);
        $this->assertStringContainsString("padding-top: 0;\n        border-top: 0;", $stylesheet);
        $this->assertStringNotContainsString('&__languages {', $stylesheet);
    }

    public function test_contacts_page_uses_the_new_bilingual_layout_and_real_store_data(): void
    {
        ContactConfig::create([
            'city_one' => ['uk' => 'Одеса', 'ru' => 'Одесса'],
            'address_one' => [
                'uk' => 'ТЦ "Гіпермаркет Дверей" (Краснова, 12А)',
                'ru' => 'ТЦ "Гипермаркет Дверей" (Краснова, 12А)',
            ],
            'phone_one' => ['uk' => '+380 (67) 953 47 74', 'ru' => '+380 (67) 953 47 74'],
            'email_one' => ['uk' => 'bona@example.test', 'ru' => 'bona@example.test'],
            'iframe_address_one' => '<iframe src="about:blank"></iframe>',
            'city_two' => ['uk' => 'Одеса', 'ru' => 'Одесса'],
            'address_two' => [
                'uk' => 'ТЦ "МегаДім" (Георгія Липського, 135)',
                'ru' => 'ТЦ "МегаДом" (Георгия Липского, 135)',
            ],
            'phone_two' => ['uk' => '+380 (67) 953 44 42', 'ru' => '+380 (67) 953 44 42'],
            'email_two' => ['uk' => 'bona@example.test', 'ru' => 'bona@example.test'],
            'iframe_address_two' => '<iframe src="about:blank"></iframe>',
            'meta_title' => ['uk' => 'Контакти Bona Doors', 'ru' => 'Контакты Bona Doors'],
            'meta_description' => ['uk' => 'Салони в Одесі', 'ru' => 'Салоны в Одессе'],
            'meta_keywords' => ['uk' => '', 'ru' => ''],
            'meta_tags' => '',
        ]);

        $this->get(route('store.contacts'))
            ->assertOk()
            ->assertSee('class="bona-contact-page"', false)
            ->assertSee('Давайте поговоримо про ваші двері')
            ->assertSee('Побачте матеріали наживо')
            ->assertSee('data-lead-inline', false)
            ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=', false)
            ->assertSee('title="Карта розташування:', false)
            ->assertDontSee('art-contacts-line', false)
            ->assertDontSee('class="main-header"', false)
            ->assertDontSee('bona-footer__languages', false);

        $this->get(route('localized.store.contacts', ['lang' => 'ru']))
            ->assertOk()
            ->assertSee('Давайте поговорим о ваших дверях')
            ->assertSee('Посмотрите материалы вживую')
            ->assertSee('ТЦ &quot;Гипермаркет Дверей&quot;', false)
            ->assertSee('Заказать замер');

        $script = file_get_contents(resource_path('js/store/common/lead-modals.js'));
        $stylesheet = file_get_contents(resource_path('scss/storefront/_redesign.scss'));

        $this->assertStringContainsString("form.closest('[data-lead-modal], [data-lead-inline]')", $script);
        $this->assertStringContainsString('[data-lead-modal-thanks], [data-lead-inline-thanks]', $script);
        $this->assertStringContainsString('label.bona-lead-consent > input {', $stylesheet);
    }
}
