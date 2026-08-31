<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductGalleryTest extends TestCase
{
    public function test_product_gallery_passes_dom_slides_to_swiper(): void
    {
        $script = file_get_contents(resource_path('js/store/pages/store.product.page/swiper.js'));

        $this->assertStringContainsString('appendSlide(this.cloneNode(true))', $script);
        $this->assertStringNotContainsString('appendSlide($(this).clone())', $script);
    }

    public function test_localized_product_routes_load_the_same_gallery_script(): void
    {
        $script = file_get_contents(resource_path('js/store/app.js'));

        $this->assertStringContainsString("page.startsWith('localized.')", $script);
        $this->assertStringContainsString("page.slice('localized.'.length)", $script);
        $this->assertStringContainsString("pages['./pages/' + pageToLoad + '.js']", $script);
    }
}
