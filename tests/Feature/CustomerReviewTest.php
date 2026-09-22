<?php

namespace Tests\Feature;

use App\DataClasses\ProductReviewStatusesDataClass;
use App\Models\CustomerReview;
use App\Models\Role;
use App\Models\User;
use App\Services\HomePage\HomePageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_review_is_stored_for_moderation_with_private_contact_data(): void
    {
        $this->postJson(route('store.customer-review.submit'), $this->validPayload())
            ->assertCreated()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('customer_reviews', [
            'author_name' => 'Марина Коваль',
            'phone' => '+38 (067) 123 45 67',
            'email' => 'marina@example.com',
            'rating' => 5,
            'status_id' => ProductReviewStatusesDataClass::STATUS_PENDING,
        ]);
    }

    public function test_homepage_review_requires_a_complete_ukrainian_phone_and_rejects_honeypot_spam(): void
    {
        $this->postJson(route('store.customer-review.submit'), [
            ...$this->validPayload(),
            'phone' => '+38 (067) 123 45',
        ])->assertUnprocessable()->assertJsonValidationErrors('phone');

        $this->postJson(route('store.customer-review.submit'), [
            ...$this->validPayload(),
            'website' => 'https://spam.example',
        ])->assertUnprocessable()->assertJsonValidationErrors('website');

        $this->assertDatabaseCount('customer_reviews', 0);
    }

    public function test_only_approved_customer_reviews_join_the_homepage_slider(): void
    {
        $pending = CustomerReview::create($this->reviewAttributes('Очікує Модерації'));
        $approved = CustomerReview::create([
            ...$this->reviewAttributes('Опублікований Клієнт'),
            'status_id' => ProductReviewStatusesDataClass::STATUS_APPROVED,
            'published_at' => now(),
        ]);

        $reviews = app(HomePageService::class)->getStorefrontTestimonials();

        $this->assertTrue($reviews->contains('id', $approved->id));
        $this->assertFalse($reviews->contains(fn ($review) => $review instanceof CustomerReview && $review->id === $pending->id));
        $this->assertSame('Опублікований Клієнт', $reviews->first()->name);
    }

    public function test_admin_can_approve_a_customer_review_and_see_contact_details(): void
    {
        $review = CustomerReview::create($this->reviewAttributes('Ірина Бондар'));
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.customer-review.approve', $review))
            ->assertOk();

        $review->refresh();
        $this->assertSame(ProductReviewStatusesDataClass::STATUS_APPROVED, $review->status_id);
        $this->assertNotNull($review->published_at);

        $this->actingAs($admin)
            ->get(route('admin.customer-review.list.page'))
            ->assertOk()
            ->assertSee('Ірина Бондар')
            ->assertSee('+38 (067) 123 45 67');
    }

    public function test_admin_can_add_and_edit_a_google_review_in_the_unified_section(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.customer-review.create'), [
                'source' => CustomerReview::SOURCE_GOOGLE,
                'source_url' => 'https://g.co/kgs/example',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/avatar-example',
                'author_name' => 'Олена Клієнтка',
                'author_name_translations' => [
                    'uk' => 'Олена Клієнтка',
                    'ru' => 'Елена Клиентка',
                ],
                'rating' => 5,
                'review' => 'Дуже задоволена якістю дверей і роботою команди.',
                'review_translations' => [
                    'uk' => 'Дуже задоволена якістю дверей і роботою команди.',
                    'ru' => 'Очень довольна качеством дверей и работой команды.',
                ],
                'status_id' => ProductReviewStatusesDataClass::STATUS_APPROVED,
                'reviewed_at' => '2026-09-20',
                'source_published_label' => '3 дні тому',
                'locale' => 'uk',
            ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'success' => true,
                    'redirect_to' => route('admin.customer-review.list.page'),
                ],
            ]);

        $review = CustomerReview::query()->sole();
        $this->assertSame(CustomerReview::SOURCE_GOOGLE, $review->source);
        $this->assertSame('https://lh3.googleusercontent.com/avatar-example', $review->author_avatar_url);
        $this->assertSame('3 дні тому', $review->source_published_label);
        $this->assertNull($review->phone);
        $this->assertNotNull($review->published_at);
        $this->assertSame('Елена Клиентка', tap($review, fn () => app()->setLocale('ru'))->name);
        $this->assertSame('Очень довольна качеством дверей и работой команды.', $review->display_review);

        app()->setLocale('uk');
        $this->actingAs($admin)
            ->get(route('admin.customer-review.list.page'))
            ->assertOk()
            ->assertSee('Олена Клієнтка')
            ->assertSee('Google')
            ->assertSee('https://g.co/kgs/example');

        $this->actingAs($admin)
            ->post(route('admin.customer-review.edit', $review), [
                'source' => CustomerReview::SOURCE_GOOGLE,
                'source_url' => 'https://g.co/kgs/example',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/avatar-example',
                'author_name' => 'Олена Клієнтка',
                'rating' => 4,
                'review' => 'Оновлений чесний текст Google-відгуку для перевірки.',
                'status_id' => ProductReviewStatusesDataClass::STATUS_APPROVED,
                'reviewed_at' => '2026-09-20',
                'source_published_label' => '3 дні тому',
                'locale' => 'uk',
            ])
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertSame(4, $review->refresh()->rating);
    }

    public function test_home_review_modal_uses_shared_flow_in_both_languages(): void
    {
        foreach (['uk', 'ru'] as $locale) {
            app()->setLocale($locale);
            $html = view('components.store.home-reviews', [
                'testimonials' => collect(),
                'section' => [],
            ])->render();

            $this->assertStringContainsString('data-lead-modal-open="dialog-home-review"', $html);
            $this->assertStringContainsString('data-lead-form="review"', $html);
            $this->assertStringContainsString('class="js-ua-phone"', $html);
        }
    }

    private function validPayload(): array
    {
        return [
            'first_name' => 'Марина',
            'last_name' => 'Коваль',
            'phone' => '+38 (067) 123 45 67',
            'email' => 'marina@example.com',
            'rating' => 5,
            'review' => 'Дуже уважна консультація, вчасна доставка та акуратний монтаж дверей.',
            'agree' => '1',
            'website' => '',
        ];
    }

    private function reviewAttributes(string $name): array
    {
        return [
            'author_name' => $name,
            'phone' => '+38 (067) 123 45 67',
            'email' => 'client@example.com',
            'rating' => 5,
            'review' => 'Детальний чесний відгук клієнта про консультацію, доставку та монтаж.',
            'status_id' => ProductReviewStatusesDataClass::STATUS_PENDING,
            'locale' => 'uk',
            'ip_address' => '127.0.0.1',
        ];
    }

    private function admin(): User
    {
        DB::table('roles')->insertOrIgnore([
            'id' => Role::ADMIN_ROLE_ID,
            'role' => 'Admin',
            'role_slug' => 'admin',
        ]);

        $admin = User::factory()->create();
        $admin->update(['role_id' => Role::ADMIN_ROLE_ID]);

        return $admin;
    }
}
