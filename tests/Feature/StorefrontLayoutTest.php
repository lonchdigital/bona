<?php

namespace Tests\Feature;

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
}
