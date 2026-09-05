<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\ApplicationConfigs\ApplicationConfigsEditRequest;
use App\Http\Requests\Admin\Contacts\ContactsEditRequest;
use App\Models\ApplicationConfig;
use App\Models\ContactConfig;
use App\Services\Application\ApplicationConfigService;
use App\Services\Contacts\ContactsPageService;
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
            ->assertSee('data-home-overlay-header', false)
            ->assertSee('bona-site-header--overlay', false)
            ->assertSee('data-reveal-on-scroll', false)
            ->assertSee('data-home-hero', false)
            ->assertDontSee('art-contact-form-section', false)
            ->assertDontSee('art-quote-carousel-home', false)
            ->assertDontSee('art-brands-owl-items', false);
    }

    public function test_homepage_interactive_controls_use_valid_aria_contracts(): void
    {
        $search = file_get_contents(resource_path('views/components/store/search.blade.php'));
        $searchScript = file_get_contents(resource_path('js/store/common/storefront-search.js'));
        $stylesheet = file_get_contents(resource_path('scss/storefront/_redesign.scss'));
        $hero = file_get_contents(resource_path('views/components/store/home-hero.blade.php'));
        $heroScript = file_get_contents(resource_path('js/store/common/home-hero.js'));

        $this->assertStringContainsString('role="combobox"', $search);
        $this->assertStringContainsString('data-search-clear', $search);
        $this->assertStringContainsString('base.storefront_search_clear', $search);
        $this->assertStringContainsString('M3 3 13 13M13 3 3 13', $search);
        $this->assertStringContainsString("clearButton.addEventListener('click', clearSearch)", $searchScript);
        $this->assertStringContainsString('&::-webkit-search-cancel-button,', $stylesheet);
        $this->assertStringContainsString('-webkit-appearance: none;', $stylesheet);
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
            ->assertSee('bona-site-header--solid', false)
            ->assertDontSee('data-home-overlay-header', false)
            ->assertDontSee('data-reveal-on-scroll', false);
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

    public function test_mobile_bottom_navigation_reveals_after_homepage_scroll_then_yields_to_open_overlays(): void
    {
        $source = file_get_contents(resource_path('js/store/common/mobile-bottom-navigation.js'));

        $this->assertStringContainsString("const MOBILE_BREAKPOINT = '(max-width: 960px)'", $source);
        $this->assertStringContainsString('const REVEAL_SCROLL_OFFSET = 32;', $source);
        $this->assertStringContainsString("navigation.hasAttribute('data-reveal-on-scroll')", $source);
        $this->assertStringContainsString('let hasBeenRevealed = !revealOnScroll;', $source);
        $this->assertStringContainsString("navigationEntry.type === 'navigate'", $source);
        $this->assertStringContainsString('let pageHasShown', $source);
        $this->assertStringContainsString('window.scrollY < REVEAL_SCROLL_OFFSET', $source);
        $this->assertStringContainsString('const shouldShow = mediaQuery.matches', $source);
        $this->assertStringContainsString('&& hasBeenRevealed', $source);
        $this->assertStringContainsString("document.body.classList.contains('bona-menu-open')", $source);
        $this->assertStringContainsString("document.body.classList.contains('bona-cart-drawer-open')", $source);
        $this->assertStringContainsString('new MutationObserver(setVisible)', $source);
        $this->assertStringContainsString('menuToggle.click()', $source);
        $this->assertStringContainsString('setVisible();', $source);
        $this->assertStringContainsString("window.addEventListener('scroll', handleScroll, { passive: true })", $source);
        $this->assertStringContainsString("window.removeEventListener('scroll', handleScroll)", $source);
    }

    public function test_mobile_home_header_is_kept_visible_on_a_fresh_navigation(): void
    {
        $headerScript = file_get_contents(resource_path('js/store/common/site-header.js'));
        $stylesheet = file_get_contents(resource_path('scss/storefront/_redesign.scss'));

        $this->assertStringContainsString("window.matchMedia('(max-width: 960px)').matches", $headerScript);
        $this->assertStringContainsString("header.hasAttribute('data-home-overlay-header')", $headerScript);
        $this->assertStringContainsString("navigationEntry.type === 'navigate'", $headerScript);
        $this->assertStringContainsString('resetUnexpectedOffset();', $headerScript);
        $this->assertStringContainsString("window.addEventListener('pageshow', resetUnexpectedOffset, { once: true })", $headerScript);
        $this->assertStringContainsString("window.scrollTo({ top: 0, left: 0, behavior: 'auto' })", $headerScript);
        $this->assertStringContainsString("&--overlay {\n            // Keep the header on its own compositing layer.", $stylesheet);
        $this->assertStringContainsString('transform: translateZ(0);', $stylesheet);
        $this->assertStringContainsString('-webkit-backface-visibility: hidden;', $stylesheet);
        $this->assertStringContainsString('backface-visibility: hidden;', $stylesheet);
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

    public function test_footer_renders_the_configured_tiktok_profile_with_balanced_glyphs(): void
    {
        $url = 'https://www.tiktok.com/@bonadoors';
        $footer = view('components.store.site-footer', [
            'options' => ['tiktok' => $url],
            'productTypes' => collect(),
        ])->render();
        $stylesheet = file_get_contents(resource_path('scss/storefront/_redesign.scss'));

        $this->assertStringContainsString('href="'.$url.'"', $footer);
        $this->assertStringContainsString('aria-label="TikTok"', $footer);
        $this->assertStringContainsString('bona-footer__social-icon--tiktok', $footer);
        $this->assertStringContainsString("width: 40px;\n            height: 40px;", $stylesheet);
        $this->assertStringContainsString("width: 24px;\n        height: 24px;", $stylesheet);
        $this->assertStringContainsString("&--tiktok { -webkit-mask-image: url('/assets/icons/i-tiktok.svg?v=24');", $stylesheet);
        $this->assertStringNotContainsString('-webkit-mask-size: 52px', $stylesheet);
        $this->assertStringContainsString('viewBox="14 13 21 21"', file_get_contents(public_path('assets/icons/i-instagram.svg')));
        $this->assertStringContainsString('viewBox="1.5 0 21 24"', file_get_contents(public_path('assets/icons/i-tiktok.svg')));
        $this->assertStringContainsString('viewBox="9 12 19 16"', file_get_contents(public_path('assets/icons/i-telegram.svg')));
        $this->assertStringContainsString('viewBox="13 13 14 14"', file_get_contents(public_path('assets/icons/i-viber.svg')));
        $this->assertStringContainsString('viewBox="21 16 7 15"', file_get_contents(public_path('assets/icons/i-facebook.svg')));
        $this->assertStringContainsString("&__addresses {\n        display: flex;\n        flex-direction: column;\n        gap: 44px;", $stylesheet);
        $this->assertFileExists(public_path('assets/icons/i-tiktok.svg'));
    }

    public function test_header_cart_is_an_accessible_click_opened_drawer(): void
    {
        $markup = file_get_contents(resource_path('views/components/cart-window.blade.php'));
        $cartScript = file_get_contents(resource_path('js/store/common/cart.js'));
        $legacyMenuScript = file_get_contents(resource_path('js/store/common/show-menu.js'));
        $storefrontStyles = file_get_contents(resource_path('scss/storefront/_redesign.scss'));
        $mobileNavigationStyles = file_get_contents(resource_path('scss/storefront/_mobile-bottom-navigation.scss'));
        $productTemplate = file_get_contents(resource_path('views/pages/store/product.blade.php'));
        $slidingDoorTemplate = file_get_contents(resource_path('views/pages/store/product-variety/rozsuvni-dveri-product.blade.php'));

        $this->assertStringContainsString('data-cart-drawer-open', $markup);
        $this->assertStringContainsString('id="bona-cart-drawer"', $markup);
        $this->assertStringContainsString('role="dialog"', $markup);
        $this->assertStringContainsString('aria-modal="true"', $markup);
        $this->assertStringContainsString("trigger.addEventListener('click'", $cartScript);
        $this->assertStringContainsString("event.key === 'Escape'", $cartScript);
        $this->assertStringContainsString("document.body.classList.add('bona-cart-drawer-open')", $cartScript);
        $this->assertStringContainsString('drawerBody.scrollTop = 0;', $cartScript);
        $this->assertStringContainsString('class="item-content"', $cartScript);
        $this->assertStringContainsString('drawProductsInCartWindowHTML(data);', $cartScript);
        $this->assertStringContainsString('openCartDrawer();', $cartScript);
        $this->assertStringNotContainsString('productAddedToCartButton', $cartScript);
        $this->assertStringNotContainsString('product-added-to-cart', $productTemplate);
        $this->assertStringNotContainsString('product-added-to-cart', $slidingDoorTemplate);
        $this->assertStringNotContainsString("$('.bona-header__actions .basket-basket-list .basket-link')", $legacyMenuScript);
        $this->assertStringContainsString('grid-template-columns: 64px minmax(0, 1fr) 44px;', $storefrontStyles);
        $this->assertStringContainsString("min-width: 0;\n            width: 100%;\n            height: auto;", $storefrontStyles);
        $this->assertStringContainsString(".custom-control-number--cart input[type='number']", $storefrontStyles);
        $this->assertStringContainsString('button.bona-cart-trigger .bona-cart-icon', $storefrontStyles);
        $this->assertStringContainsString('li.list-inline-item.basket-list button.bona-cart-trigger', $storefrontStyles);
        $this->assertStringContainsString('width: 21px !important;', $storefrontStyles);
        $this->assertStringContainsString('width: 20px !important;', $storefrontStyles);
        $this->assertStringContainsString('body.bona-cart-drawer-open .bona-mobile-bottom-nav', $mobileNavigationStyles);
    }

    public function test_storefront_styles_are_the_final_authoritative_cascade_layer(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/store-main.blade.php'));

        $legacyPosition = strpos($layout, "Vite::asset('resources/scss/theme-additional.scss')");
        $storefrontPosition = strpos($layout, "Vite::asset('resources/scss/storefront.scss')");

        $this->assertNotFalse($legacyPosition);
        $this->assertNotFalse($storefrontPosition);
        $this->assertLessThan($storefrontPosition, $legacyPosition);
        $this->assertStringContainsString('authoritative final layer', $layout);
    }

    public function test_wishlist_icons_have_safe_geometry_before_storefront_css_loads(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/store-main.blade.php'));
        $heart = file_get_contents(resource_path('views/components/wish-heart.blade.php'));

        $this->assertStringContainsString('id="bona-critical-icon-geometry"', $layout);
        $this->assertStringContainsString('.bona-header__actions svg,', $layout);
        $this->assertStringContainsString('.bona-mobile-bottom-nav__icon > svg', $layout);
        $this->assertStringContainsString('.art-heart-filled { display: none; }', $layout);
        $this->assertLessThan(
            strpos($layout, "Vite::asset('resources/scss/storefront.scss')"),
            strpos($layout, 'id="bona-critical-icon-geometry"'),
        );
        $this->assertSame(2, substr_count($heart, 'width="24" height="24"'));
    }

    public function test_tiktok_profile_is_prepopulated_and_saved_through_application_settings(): void
    {
        $url = 'https://www.tiktok.com/@bonadoors';

        $this->assertSame(
            $url,
            app(ApplicationConfigService::class)->getAllApplicationConfigOptions()['tiktok']
        );

        $request = ApplicationConfigsEditRequest::create('/', 'POST', ['tiktok' => $url]);
        $dto = $request->toDTO();

        $this->assertSame($url, $dto->tiktok);

        app(ApplicationConfigService::class)->editApplicationConfig($dto);

        $this->assertSame(
            $url,
            ApplicationConfig::query()->where('config_name', 'tiktok')->firstOrFail()->config_data
        );

        $adminView = file_get_contents(resource_path('views/pages/admin/application-config/edit.blade.php'));
        $adminForm = file_get_contents(resource_path('js/admin/forms/ApplicationConfigsPageEditForm.vue'));

        $this->assertStringContainsString(':tiktok="{{ json_encode($applicationConfig[\'tiktok\']) }}"', $adminView);
        $this->assertStringContainsString('name="tiktok"', $adminForm);
        $this->assertStringContainsString(':model-value="tiktok"', $adminForm);
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
            'working_hours_one' => ['uk' => 'Пн–Пт: 10:00–19:00', 'ru' => 'Пн–Пт: 10:00–19:00'],
            'iframe_address_one' => '<iframe src="about:blank"></iframe>',
            'city_two' => ['uk' => 'Одеса', 'ru' => 'Одесса'],
            'address_two' => [
                'uk' => 'ТЦ "МегаДім" (Георгія Липського, 135)',
                'ru' => 'ТЦ "МегаДом" (Георгия Липского, 135)',
            ],
            'phone_two' => ['uk' => '+380 (67) 953 44 42', 'ru' => '+380 (67) 953 44 42'],
            'email_two' => ['uk' => 'bona@example.test', 'ru' => 'bona@example.test'],
            'working_hours_two' => ['uk' => 'Сб: 10:00–16:00', 'ru' => 'Сб: 10:00–16:00'],
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
            ->assertSee('Пн–Пт: 10:00–19:00')
            ->assertSee('Сб: 10:00–16:00')
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

    public function test_footer_settings_are_grouped_and_link_to_their_single_sources_of_truth(): void
    {
        $adminView = file_get_contents(resource_path('views/pages/admin/application-config/edit.blade.php'));
        $adminForm = file_get_contents(resource_path('js/admin/forms/ApplicationConfigsPageEditForm.vue'));
        $contactsForm = file_get_contents(resource_path('js/admin/forms/ContactPageEditForm.vue'));

        $this->assertStringContainsString('initial-tab="{{ request(\'tab\') === \'footer\'', $adminView);
        $this->assertStringContainsString('application-settings-main-tab', $adminForm);
        $this->assertStringContainsString('application-settings-footer-tab', $adminForm);
        $this->assertStringContainsString('contactsRoute', $adminForm);
        $this->assertStringContainsString('menuSettingsRoute', $adminForm);
        $this->assertStringContainsString('name="working_hours_one"', $contactsForm);
        $this->assertStringContainsString('name="working_hours_two"', $contactsForm);
        $this->assertStringContainsString('name="working_hours_three"', $contactsForm);
    }

    public function test_contact_settings_save_the_working_hours_used_by_the_footer(): void
    {
        $request = ContactsEditRequest::create('/', 'POST', [
            'working_hours_one' => [
                'uk' => 'Щодня: 10:00–19:00',
                'ru' => 'Ежедневно: 10:00–19:00',
            ],
        ]);

        $dto = $request->toDTO();
        $this->assertSame('Щодня: 10:00–19:00', $dto->workingHoursOne['uk']);

        app(ContactsPageService::class)->editContactsPage($dto);

        $contacts = ContactConfig::query()->firstOrFail();
        $this->assertSame('Щодня: 10:00–19:00', $contacts->getTranslation('working_hours_one', 'uk'));
        $this->assertSame('Ежедневно: 10:00–19:00', $contacts->getTranslation('working_hours_one', 'ru'));
    }

    public function test_footer_always_reads_the_latest_contact_settings(): void
    {
        $contacts = ContactConfig::query()->create([
            'city_one' => ['uk' => 'Одеса', 'ru' => 'Одесса'],
            'address_one' => ['uk' => 'Краснова, 12А', 'ru' => 'Краснова, 12А'],
            'working_hours_one' => ['uk' => 'Старий графік', 'ru' => 'Старый график'],
        ]);

        // Render once first: this reproduces a storefront process that has
        // already served and retained the previous footer configuration.
        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Старий графік');

        $contacts->update([
            'working_hours_one' => ['uk' => 'Новий графік', 'ru' => 'Новый график'],
        ]);

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('Новий графік')
            ->assertDontSee('Старий графік');
    }
}
