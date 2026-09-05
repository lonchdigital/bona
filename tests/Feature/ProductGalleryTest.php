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

    public function test_reference_product_interactions_cover_gallery_tabs_installments_and_mobile_cart(): void
    {
        $pageScript = file_get_contents(resource_path('js/store/pages/store.product.page.js'));
        $referenceScript = file_get_contents(resource_path('js/store/pages/store.product.page/product-reference.js'));
        $cartScript = file_get_contents(resource_path('js/store/common/cart.js'));
        $referenceStyles = file_get_contents(resource_path('scss/storefront/_product-reference.scss'));
        $redesignStyles = file_get_contents(resource_path('scss/storefront/_redesign.scss'));
        $referenceView = file_get_contents(resource_path('views/pages/store/partials/product-reference.blade.php'));

        $this->assertStringContainsString("import('./store.product.page/product-reference')", $pageScript);
        $this->assertStringNotContainsString("import('./store.product.page/swiper')", $pageScript);
        $this->assertStringContainsString("querySelector('[data-product-gallery]')", $referenceScript);
        $this->assertStringContainsString("querySelectorAll('[data-product-tab]')", $referenceScript);
        $this->assertStringContainsString("querySelector('[data-installment-card]')", $referenceScript);
        $this->assertStringContainsString("querySelector('.product-kit-selections')", $referenceScript);
        $this->assertStringContainsString("className = 'kit-dialog__remove'", $referenceScript);
        $this->assertStringContainsString('draft.delete(choice.dataset.kitCategoryKey)', $referenceScript);
        $this->assertStringNotContainsString('data-kit-choice-clear', $referenceScript);
        $this->assertStringNotContainsString('data-kit-choice-clear', $referenceView);
        $this->assertStringContainsString('data-kit-selection-hint', $referenceView);
        $this->assertStringContainsString('const trigger = $(this)', $cartScript);
        $this->assertStringContainsString("trigger.data('product-slug') || trigger.attr('id')", $cartScript);
        $this->assertStringContainsString("trigger.attr('data-checkout-redirect')", $cartScript);
        $this->assertStringContainsString("replace(/\\s+/g, '')", $cartScript);
        $this->assertStringContainsString('.product-body .bona-product-page :where(section)', $referenceStyles);
        $this->assertStringNotContainsString(".product-body .product-kit-dialog {\n    display: none;", $referenceStyles);
        $this->assertStringContainsString('.art-heart-filled { display: none; }', $referenceStyles);
        $this->assertStringContainsString('.art-heart-outline { display: none; }', $referenceStyles);
        $this->assertStringContainsString('&::before,', $referenceStyles);
        $this->assertMatchesRegularExpression('/&__social-icon\s*\{.*?width:\s*16px;.*?height:\s*16px;/s', $redesignStyles);
    }
}
