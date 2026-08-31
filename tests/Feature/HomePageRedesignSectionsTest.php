<?php

namespace Tests\Feature;

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
}
