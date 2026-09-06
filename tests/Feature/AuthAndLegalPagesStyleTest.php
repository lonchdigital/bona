<?php

namespace Tests\Feature;

use App\DataClasses\StaticPageTypesDataClass;
use App\Models\StaticPage;
use App\Models\StaticPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndLegalPagesStyleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_public_auth_flow_uses_the_current_storefront_shell(): void
    {
        $pages = [
            route('auth.sign-in.page'),
            route('auth.sign-up.page'),
            route('auth.forgot-password.page'),
            route('auth.confirm-email-resend.page'),
            route('password.reset', ['token' => 'test-token', 'email' => 'customer@example.com']),
        ];

        foreach ($pages as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('bona-auth-section__layout', false)
                ->assertSee('bona-auth-card', false)
                ->assertSee('name="robots" content="noindex', false)
                ->assertDontSee('pages.store.partials.page_header', false);
        }

        $this->get(route('auth.sign-in.page'))
            ->assertSee('action="'.route('auth.sign-in').'"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee(parse_url(route('auth.sign-up.page'), PHP_URL_PATH), false);

        $this->get(route('auth.confirm-email-resend.page'))
            ->assertSee('action="'.route('auth.confirm-email-resend').'"', false);
    }

    public function test_each_legal_page_keeps_admin_content_and_uses_the_editorial_design(): void
    {
        foreach (StaticPageTypesDataClass::get() as $type) {
            $page = StaticPage::create(['type_id' => $type['id']]);
            StaticPageContent::create([
                'static_page_id' => $page->id,
                'language' => 'uk',
                'meta_title' => 'SEO '.$type['name'],
                'meta_description' => 'Короткий опис документа',
                'meta_keywords' => 'двері, документ',
                'content' => '<h1>Розділ документа</h1><p data-legal-fixture>Текст з адмінки</p><table><tr><th>Умова</th></tr><tr><td>Значення</td></tr></table>',
            ]);

            $response = $this->get(route('store.static-page.page', ['staticPageSlug' => $type['slug']]));

            $response->assertOk()
                ->assertSee('bona-legal-document__layout', false)
                ->assertSee('bona-legal-document__content', false)
                ->assertSee('data-legal-fixture', false)
                ->assertSee('<h2>Розділ документа</h2>', false)
                ->assertSee('application/ld+json', false)
                ->assertSee('<title>SEO '.$type['name'].'</title>', false)
                ->assertDontSee('art-common-page-section', false);
        }
    }

    public function test_a_known_legal_route_without_admin_content_has_a_safe_empty_state(): void
    {
        $this->get(route('store.static-page.page', [
            'staticPageSlug' => 'dogovir-publichnoyi-oferti',
        ]))
            ->assertOk()
            ->assertSee(trans('base.legal_page_empty'));
    }
}
