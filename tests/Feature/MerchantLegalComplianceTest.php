<?php

namespace Tests\Feature;

use App\DataClasses\StaticPageTypesDataClass;
use App\Models\StaticPage;
use App\Models\StaticPageContent;
use App\Services\Seo\OrganizationSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantLegalComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_storefront_identifies_the_legal_seller(): void
    {
        $this->assertSame('Фізична особа — підприємець ГОНЧАР ОКСАНА СЕРГІЇВНА', config('organization.merchant.legal_name'));
        $this->assertSame('3258813661', config('organization.merchant.tax_id'));
        $this->assertSame('UA413052990000026005015020910', config('organization.merchant.iban'));
        $this->assertSame('03170, м. Київ, вул. Професора Ейхельмана, 21', config('organization.merchant.registered_address.formatted'));

        $this->get(route('store.contacts'))
            ->assertOk()
            ->assertSee('Реквізити продавця')
            ->assertSee('Фізична особа — підприємець ГОНЧАР ОКСАНА СЕРГІЇВНА')
            ->assertSee('3258813661')
            ->assertSee('UA413052990000026005015020910')
            ->assertSee('03170, м. Київ, вул. Професора Ейхельмана, 21')
            ->assertSee('Поповнення рахунку, ГОНЧАР ОКСАНА СЕРГІЇВНА');

        $this->get(route('store.home'))
            ->assertOk()
            ->assertDontSee('bona-footer__merchant', false)
            ->assertSee('Powered by Lonch')
            ->assertSee('data-google-analytics-id="G-0863474309"', false)
            ->assertSee('data-cookie-settings', false);
    }

    public function test_footer_omits_duplicate_seller_details_and_places_credit_before_payment_marks(): void
    {
        foreach (['uk', 'ru'] as $locale) {
            app()->setLocale($locale);
            $footer = view('components.store.site-footer', ['productTypes' => collect(), 'options' => []])->render();
            $this->assertStringNotContainsString('ГОНЧАР ОКСАНА СЕРГІЇВНА', $footer);
            $this->assertStringNotContainsString('3258813661', $footer);
            $this->assertStringNotContainsString('bona-footer__merchant', $footer);

            $document = new \DOMDocument;
            @$document->loadHTML('<?xml encoding="utf-8" ?>'.$footer);
            $columns = (new \DOMXPath($document))->query('//div[@class="bona-footer__bottom"]/*');
            $this->assertCount(4, $columns);
            $this->assertSame('p', $columns->item(0)->nodeName);
            $this->assertSame('bona-footer__legal', $columns->item(1)->getAttribute('class'));
            $credit = $columns->item(2);
            $this->assertSame('a', $credit->nodeName);
            $this->assertSame('Powered by Lonch', $credit->textContent);
            $this->assertSame('https://lonch.digital', $credit->getAttribute('href'));
            $this->assertSame('noopener noreferrer', $credit->getAttribute('rel'));
            $this->assertSame('bona-footer__payments', $columns->item(3)->getAttribute('class'));
            $this->assertStringContainsString(trans('base.exchange_and_return'), $columns->item(1)->textContent);
        }
    }

    public function test_organization_schema_exposes_legal_name_and_tax_identifier(): void
    {
        $schema = app(OrganizationSchemaService::class)->build();
        $organization = collect($schema['@graph'])
            ->firstWhere('@type', 'Organization');

        $this->assertSame(config('organization.merchant.legal_name'), $organization['legalName']);
        $this->assertSame(config('organization.merchant.tax_id'), $organization['taxID']);
        $this->assertSame('PostalAddress', $organization['address']['@type']);
        $this->assertSame('вул. Професора Ейхельмана, 21', $organization['address']['streetAddress']);
        $this->assertSame('Київ', $organization['address']['addressLocality']);
        $this->assertSame('03170', $organization['address']['postalCode']);
        $this->assertSame('UA', $organization['address']['addressCountry']);
    }

    public function test_deployment_publishes_complete_bilingual_legal_documents(): void
    {
        $expectedDocuments = [
            StaticPageTypesDataClass::PAGE_POLICY => ['Google Analytics 4', 'Meta Pixel', 'LiqPay', 'AI-асистент'],
            StaticPageTypesDataClass::PAGE_AGREEMENT => ['IBAN', 'LiqPay', 'monobank', 'ПриватБанку'],
            StaticPageTypesDataClass::EXCHANGE_AND_RETURN => ['14 днів', 'індивідуальним замовленням', 'оплати частинами'],
        ];

        foreach ($expectedDocuments as $typeId => $needles) {
            $page = StaticPage::query()->where('type_id', $typeId)->firstOrFail();
            $ukrainian = StaticPageContent::query()
                ->where('static_page_id', $page->id)
                ->where('language', 'uk')
                ->firstOrFail();
            $russian = StaticPageContent::query()
                ->where('static_page_id', $page->id)
                ->where('language', 'ru')
                ->firstOrFail();

            $this->assertStringContainsString('ГОНЧАР ОКСАНА СЕРГІЇВНА', $ukrainian->content);
            $this->assertStringContainsString('ГОНЧАР ОКСАНА СЕРГІЇВНА', $russian->content);
            $this->assertStringNotContainsString('ГОНЧАР ОКСАНА СЕРГЕЕВНА', $russian->content);
            $this->assertStringContainsString('03170, м. Київ, вул. Професора Ейхельмана, 21', $ukrainian->content);
            $this->assertStringContainsString('03170, м. Київ, вул. Професора Ейхельмана, 21', $russian->content);

            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $ukrainian->content);
            }
        }
    }

    public function test_analytics_uses_consent_mode_and_cookies_wait_for_an_explicit_choice(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/store-main.blade.php'));
        $component = file_get_contents(resource_path('views/components/store/cookie-consent.blade.php'));
        $script = file_get_contents(resource_path('js/store/common/cookie-consent.js'));
        $services = file_get_contents(config_path('services.php'));
        $environmentExample = file_get_contents(base_path('.env.example'));

        $this->assertSame('G-0863474309', config('services.google.analytics_id'));
        $this->assertStringNotContainsString('googletagmanager.com/gtm.js', $layout);
        $this->assertStringNotContainsString('googletagmanager.com/ns.html', $layout);
        $this->assertStringContainsString('<x-store.cookie-consent />', $layout);
        $this->assertStringContainsString('data-google-analytics-id', $component);
        $this->assertStringContainsString('data-cookie-consent-necessary', $component);
        $this->assertStringContainsString('data-cookie-consent-accept', $component);

        // Denied consent is declared before the tag loads; only "accept all"
        // grants storage, and a withdrawal removes existing GA cookies.
        $defaultAt = strpos($script, 'setDefaultGoogleConsent();');
        $loadAt = strpos($script, 'loadGoogleAnalytics(measurementId);');
        $this->assertNotFalse($defaultAt);
        $this->assertNotFalse($loadAt);
        $this->assertLessThan($loadAt, $defaultAt);
        $this->assertStringContainsString("window.gtag('consent', 'default'", $script);
        $this->assertStringContainsString("value === ACCEPTED ? 'granted' : 'denied'", $script);
        $this->assertStringContainsString('removeGoogleAnalyticsCookies()', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString("window.gtag('config', measurementId)", $script);
        $this->assertStringContainsString("window.gtag('event', eventName, parameters)", $script);
        $this->assertStringContainsString('без cookies', trans('base.cookie_text', [], 'uk'));
        $this->assertStringNotContainsString('GTM-P9KHGB8T', $component.$script);
        $this->assertStringContainsString('GOOGLE_ANALYTICS_ID', $services.$environmentExample);
        $this->assertStringNotContainsString('GOOGLE_TAG_MANAGER_ID', $services.$environmentExample);
    }
}
