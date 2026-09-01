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
}
