<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HomePageRedesignSectionsTest extends TestCase
{
    public function test_reviews_render_as_the_new_accessible_slider(): void
    {
        $testimonials = collect([
            (object) [
                'name' => 'Тестовий клієнт',
                'review' => 'Тестовий відгук про сервіс.',
                'rating' => 5,
                'date' => '2026-08-31',
                'url' => 'https://example.com/review',
            ],
        ]);

        $html = view('components.store.home-reviews', compact('testimonials'))->render();

        $this->assertStringContainsString('data-reviews-slider', $html);
        $this->assertStringContainsString('bona-review-card', $html);
        $this->assertStringContainsString('Тестовий відгук про сервіс.', $html);
        $this->assertStringNotContainsString('art-quote-carousel-home', $html);
    }

    public function test_blog_cards_keep_real_article_links_and_content(): void
    {
        $articles = collect([
            (object) [
                'slug' => 'test-article',
                'name' => 'Тестова стаття',
                'preview_text' => 'Короткий опис матеріалу.',
                'hero_image_url' => '/storage/test.webp',
                'created_at' => Carbon::parse('2026-08-31'),
            ],
        ]);

        $html = view('components.store.home-blog', compact('articles'))->render();

        $this->assertStringContainsString('bona-post-card', $html);
        $this->assertStringContainsString('test-article', $html);
        $this->assertStringContainsString('Короткий опис матеріалу.', $html);
    }

    public function test_faq_uses_details_and_keeps_valid_schema_data(): void
    {
        $faqs = collect([
            (object) [
                'question' => 'Тестове запитання?',
                'answer' => 'Тестова відповідь.',
            ],
        ]);

        $html = view('components.store.home-faq', compact('faqs'))->render();

        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('FAQPage', $html);
        $this->assertStringContainsString('Тестове запитання?', $html);
        $this->assertStringNotContainsString('accordion-faqs', $html);
    }

    public function test_popular_products_render_real_catalog_data_in_the_new_slider(): void
    {
        $product = new Product([
            'name' => ['uk' => 'Тестові двері', 'ru' => 'Тестовые двери'],
            'slug' => 'test-door',
            'price' => 12500,
            'main_image_path' => 'products/test.webp',
            'availability_status_id' => 2,
        ]);
        $product->setRelation('brand', new Brand(['name' => ['uk' => 'Bona', 'ru' => 'Bona']]));
        $product->setRelation('productType', new ProductType([
            'name' => ['uk' => 'Вхідні двері', 'ru' => 'Входные двери'],
        ]));
        $product->setRelation('colors', collect());

        $html = view('components.store.home-popular-products', [
            'products' => collect([$product]),
            'baseCurrency' => (object) ['name_short' => 'грн'],
            'section' => [
                'link_label' => ['uk' => 'Всі моделі', 'ru' => 'Все модели'],
            ],
        ])->render();

        $this->assertStringContainsString('data-popular-slider', $html);
        $this->assertStringContainsString('bona-product-card', $html);
        $this->assertStringContainsString('bona-product-card__actions', $html);
        $this->assertStringContainsString('data-product-compare', $html);
        $this->assertStringContainsString('aria-pressed="false"', $html);
        $this->assertStringContainsString('/product-category/interior-doors', $html);
        $this->assertStringContainsString('Тестові двері', $html);
        $this->assertStringContainsString('12 500', $html);
    }

    public function test_product_comparison_state_is_persisted_for_homepage_cards(): void
    {
        $source = file_get_contents(resource_path('js/store/common/product-comparison.js'));

        $this->assertStringContainsString('const STORAGE_KEY = \'bona-compared-products\'', $source);
        $this->assertStringContainsString('window.localStorage.setItem', $source);
        $this->assertStringContainsString('button.setAttribute(\'aria-pressed\'', $source);
        $this->assertStringContainsString('bona:comparison-change', $source);
    }

    public function test_popular_product_slider_keeps_cards_equal_and_hover_outline_visible(): void
    {
        $source = file_get_contents(resource_path('scss/storefront/_redesign.scss'));
        $catalogSource = file_get_contents(resource_path('scss/storefront/_catalog.scss'));

        $this->assertMatchesRegularExpression(
            '/&__slider\s*\{.*?padding:\s*28px 20px 36px;.*?margin:\s*-28px -20px -36px;.*?\.swiper-wrapper\s*\{.*?align-items:\s*stretch;.*?\.swiper-slide\s*\{\s*height:\s*auto;\s*\}/s',
            $source,
        );
        $this->assertStringContainsString('box-shadow: 0 12px 30px rgba(35, 32, 27, .075);', $source);
        $this->assertMatchesRegularExpression(
            '/&__more\s*\{.*?display:\s*inline-flex;.*?min-width:\s*24px;.*?min-height:\s*24px;.*?padding:\s*0;.*?line-height:\s*1;/s',
            $catalogSource,
        );
    }

    public function test_reference_sections_are_composed_in_the_expected_order(): void
    {
        $source = file_get_contents(resource_path('views/pages/store/home.blade.php'));
        $components = [
            'home-hero',
            'bona-categories',
            'home-style-selector',
            'home-popular-products',
            'home-numbers',
            'home-ideas',
            'home-steps',
            'home-works',
            'home-reviews',
            'home-instagram',
            'home-blog',
            'home-faq',
            'home-partners',
            'bona-seo',
        ];

        $positions = collect($components)->map(fn (string $component) => strpos($source, $component));

        $this->assertNotContains(false, $positions->all());
        $this->assertSame($positions->sort()->values()->all(), $positions->values()->all());
    }

    public function test_layout_uses_only_the_redesigned_footer_markup(): void
    {
        $source = file_get_contents(resource_path('views/layouts/store-main.blade.php'));

        $this->assertStringContainsString('x-store.site-footer', $source);
        $this->assertStringNotContainsString('art-site-footer', $source);
        $this->assertStringNotContainsString('footer-content-left', $source);
    }
}
