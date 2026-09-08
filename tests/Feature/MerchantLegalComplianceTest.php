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

        $this->get(route('store.contacts'))
            ->assertOk()
            ->assertSee('Реквізити продавця')
            ->assertSee('Фізична особа — підприємець ГОНЧАР ОКСАНА СЕРГІЇВНА')
            ->assertSee('3258813661')
            ->assertSee('UA413052990000026005015020910')
            ->assertSee('Поповнення рахунку, ГОНЧАР ОКСАНА СЕРГІЇВНА');

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('ФОП ГОНЧАР ОКСАНА СЕРГІЇВНА')
            ->assertSee('РНОКПП/ЄДРПОУ: 3258813661')
            ->assertSee('data-cookie-settings', false);
    }

    public function test_organization_schema_exposes_legal_name_and_tax_identifier(): void
    {
        $schema = app(OrganizationSchemaService::class)->build();
        $organization = collect($schema['@graph'])
            ->firstWhere('@type', 'Organization');

        $this->assertSame(config('organization.merchant.legal_name'), $organization['legalName']);
        $this->assertSame(config('organization.merchant.tax_id'), $organization['taxID']);
    }

    public function test_deployment_publishes_complete_bilingual_legal_documents(): void
    {
        $expectedDocuments = [
            StaticPageTypesDataClass::PAGE_POLICY => ['Google Tag Manager', 'Meta Pixel', 'LiqPay', 'AI-асистент'],
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

            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $ukrainian->content);
            }
        }
    }

    public function test_optional_tracking_waits_for_an_explicit_cookie_choice(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/store-main.blade.php'));
        $component = file_get_contents(resource_path('views/components/store/cookie-consent.blade.php'));
        $script = file_get_contents(resource_path('js/store/common/cookie-consent.js'));

        $this->assertStringNotContainsString('googletagmanager.com/gtm.js', $layout);
        $this->assertStringNotContainsString('googletagmanager.com/ns.html', $layout);
        $this->assertStringContainsString('<x-store.cookie-consent />', $layout);
        $this->assertStringContainsString('data-cookie-consent-necessary', $component);
        $this->assertStringContainsString('data-cookie-consent-accept', $component);
        $this->assertStringContainsString('setGoogleConsent(NECESSARY)', $script);
        $this->assertStringContainsString('choice === ACCEPTED', $script);
        $this->assertStringContainsString('googletagmanager.com/gtm.js', $script);
    }
}
