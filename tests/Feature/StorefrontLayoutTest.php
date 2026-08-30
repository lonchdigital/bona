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
            ->assertSee('data-home-hero', false);
    }

    public function test_internal_page_renders_the_solid_header_without_seed_data(): void
    {
        $this->get(route('store.services'))
            ->assertOk()
            ->assertSee('bona-site-header--solid', false);
    }
}
