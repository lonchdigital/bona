<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PLACE_CID = '0x25f83b8e967c0353';

    public function up(): void
    {
        Schema::table('customer_reviews', function (Blueprint $table) {
            $table->string('author_avatar_url', 2048)->nullable()->after('author_name');
            $table->string('source_published_label', 80)->nullable()->after('reviewed_at');
        });

        // A clean test database has no legacy homepage reviews. Production has
        // four records migrated by the preceding unification migration.
        if (! DB::table('customer_reviews')->where('source', 'google')->exists()) {
            return;
        }

        foreach ($this->reviews() as $review) {
            $existingId = $this->findExistingReviewId($review);
            $payload = [
                'source' => 'google',
                'source_url' => $this->reviewUrl($review['review_id']),
                'external_id' => 'google-review:'.$review['review_id'],
                'author_name' => $review['author_name'],
                'author_avatar_url' => $review['author_avatar_url'],
                'author_name_translations' => null,
                'phone' => null,
                'email' => null,
                'rating' => 5,
                'review' => $review['review'],
                'review_translations' => json_encode(
                    ['uk' => $review['review']],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                ),
                'status_id' => 2,
                'published_at' => $review['sort_date'].' 12:00:00',
                'reviewed_at' => $review['reviewed_at'],
                'source_published_label' => $review['source_published_label'],
                'locale' => 'uk',
                'ip_address' => null,
                'updated_at' => now(),
            ];

            if ($existingId) {
                DB::table('customer_reviews')->where('id', $existingId)->update($payload);

                continue;
            }

            DB::table('customer_reviews')->insert([
                ...$payload,
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $reviews = collect($this->reviews());

        DB::table('customer_reviews')
            ->whereIn('external_id', $reviews
                ->whereNull('legacy_external_id')
                ->map(fn (array $review) => 'google-review:'.$review['review_id'])
                ->all())
            ->delete();

        foreach ($reviews->whereNotNull('legacy_external_id') as $review) {
            DB::table('customer_reviews')
                ->where('external_id', 'google-review:'.$review['review_id'])
                ->update([
                    'external_id' => $review['legacy_external_id'],
                    'source_url' => $review['legacy_source_url'],
                ]);
        }

        Schema::table('customer_reviews', function (Blueprint $table) {
            $table->dropColumn(['author_avatar_url', 'source_published_label']);
        });
    }

    private function findExistingReviewId(array $review): ?int
    {
        $googleExternalId = 'google-review:'.$review['review_id'];
        $existingId = DB::table('customer_reviews')
            ->where('external_id', $googleExternalId)
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        if ($review['legacy_source_url']) {
            $existingId = DB::table('customer_reviews')
                ->where('source_url', $review['legacy_source_url'])
                ->value('id');

            if ($existingId) {
                return (int) $existingId;
            }
        }

        $existingId = DB::table('customer_reviews')
            ->where('source', 'google')
            ->where('review', 'like', '%'.$review['match'].'%')
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        if ($review['legacy_external_id']) {
            $existingId = DB::table('customer_reviews')
                ->where('external_id', $review['legacy_external_id'])
                ->value('id');
        }

        return $existingId ? (int) $existingId : null;
    }

    private function reviewUrl(string $reviewId): string
    {
        return 'https://www.google.com/maps/reviews/data=!4m8!14m7!1m6!2m5!1s'
            .$reviewId
            .'!2m1!1s0x0:'.self::PLACE_CID
            .'!3m1!1s2?hl=uk';
    }

    private function reviews(): array
    {
        return [
            [
                'review_id' => 'ChZDSUhNMG9nS0VJQ0FnSURab2JYMVVnEAE',
                'legacy_external_id' => 'legacy-home-testimonial:2',
                'legacy_source_url' => 'https://g.co/kgs/vfL93dZ',
                'match' => 'величезний вибір фурнітури',
                'author_name' => 'Alena Toshiba',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/a-/ALV-UjVSedrzcV-MwxwShkVg6xkm_vdpMUlnvIx2XYkUw05bXnT9TJ7E=w72-h72-p-rp-mo-ba12-br100',
                'review' => 'Якісні сучасні двері , величезний вибір фурнітури, індивідуальний підхід по виконанню особистих забаганок ) зручні умови оплати ! Раджу, вони найкращі !',
                'sort_date' => '2023-10-09',
                'reviewed_at' => '2023-10-09',
                'source_published_label' => null,
            ],
            [
                'review_id' => 'ChdDSUhNMG9nS0VJQ0FnSUM1dEstQTV3RRAB',
                'legacy_external_id' => 'legacy-home-testimonial:3',
                'legacy_source_url' => 'https://g.co/kgs/5KsXGyX',
                'match' => 'Все на найвищому рівні',
                'author_name' => 'Богдан Осадчук',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/a/ACg8ocLnCOtUfLIfCI9Ac3EaKVFCbmaGeGJKB8Zjlx_D68-P8r2teg=w72-h72-p-rp-mo-br100',
                'review' => 'Все на найвищому рівні! Місце, яке точно можу порадити! Великий вибір, гарна якість, прекрасний персонал - все розказали, показали, допомогли у виборі. Велике дякую магазину дверей Bona!',
                'sort_date' => '2023-11-15',
                'reviewed_at' => '2023-11-15',
                'source_published_label' => null,
            ],
            [
                'review_id' => 'ChZDSUhNMG9nS0VJQ0FnSUM1dzdDd0R3EAE',
                'legacy_external_id' => 'legacy-home-testimonial:4',
                'legacy_source_url' => 'https://g.co/kgs/N9WhsWs',
                'match' => 'Дякую, все сподоб',
                'author_name' => 'Maria Boyko',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/a-/ALV-UjULzhTpPxwmTOImTWB3KLFjqsUrmaWLZLEnZ84MTl5u0jMvGySY=w72-h72-p-rp-mo-br100',
                'review' => 'Дякую, все сподобалось ! Дуже привітний персонал, все на вищому рівні зробили!',
                'sort_date' => '2023-11-20',
                'reviewed_at' => '2023-11-20',
                'source_published_label' => null,
            ],
            [
                'review_id' => 'ChdDSUhNMG9nS0VJQ0FnSUNENXBmYmpRRRAB',
                'legacy_external_id' => 'legacy-home-testimonial:1',
                'legacy_source_url' => 'https://g.co/kgs/zYAGTXT',
                'match' => 'Bona❤️',
                'author_name' => 'Марина Знаевская',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/a/ACg8ocLstXhBeE6zfPdGbJ-g3WzEW1nN1ZVPkVwMJ9TBRuwKrMUcKQ=w72-h72-p-rp-mo-br100',
                'review' => 'Варто сказати, що при виборі дверей не тільки двері мають значення) поки шукала підрядника майже зійшла з розуму, хто ставить двері прихованого монтажу, зрозуміє про що я. Проте в результаті Bona❤️ і це любов, бо все зрозуміло, швидко і по суті! Дуже дякую за поради і допомогу! Радіємо результату ☺️',
                'sort_date' => '2024-04-03',
                'reviewed_at' => '2024-04-03',
                'source_published_label' => null,
            ],
            [
                'review_id' => 'ChZDSUhNMG9nS0VJQ0FnTUNZeXIyVlZ3EAE',
                'legacy_external_id' => null,
                'legacy_source_url' => null,
                'match' => 'Чудові двері, гарний сервіс',
                'author_name' => 'Марина Усатенко',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/a/ACg8ocJStnoKsrU0P4I6xi4NZLO74Xp8KUb2iSxsr1C_3SXOR1YJQA=w72-h72-p-rp-mo-br100',
                'review' => 'Чудові двері, гарний сервіс',
                'sort_date' => '2025-09-23',
                'reviewed_at' => null,
                'source_published_label' => 'рік тому',
            ],
            [
                'review_id' => 'Ci9DQUlRQUNvZENodHljRjlvT20wNU5qbEpabTVGZG5oVVpUVkZOMHhmWnkxdlkwRRAB',
                'legacy_external_id' => null,
                'legacy_source_url' => null,
                'match' => 'До дрібниць все запитують',
                'author_name' => 'Eclair',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/a-/ALV-UjVM_qUrQAZlqCFSyAhPCST30pCM3j0xBH9qS8skrU0yj7tRl-8=w72-h72-p-rp-mo-ba12-br100',
                'review' => 'Професійні продавці. До дрібниць все запитують і двері гарні від danapris',
                'sort_date' => '2026-06-23',
                'reviewed_at' => null,
                'source_published_label' => '3 місяці тому',
            ],
            [
                'review_id' => 'Ci9DQUlRQUNvZENodHljRjlvT2poSWVtTTNYMlV0U1RReGVXWlJUMjVEV0VOTE9XYxAB',
                'legacy_external_id' => null,
                'legacy_source_url' => null,
                'match' => 'Від першої розмови',
                'author_name' => 'Ann Popovichenko',
                'author_avatar_url' => 'https://lh3.googleusercontent.com/a/ACg8ocK3AcH54MPGj5DcAvo0tWgh591Rh5UJcVO-Q6IcZ-bbfpIc2w=w72-h72-p-rp-mo-br100',
                'review' => 'Сподобалось все! Від першої розмови до фінального «До побачення» після встановлення. Кваліфіковані менеджери, приємне спілкування, асортимент і якість на високому рівні. Раджу!',
                'sort_date' => '2026-09-09',
                'reviewed_at' => null,
                'source_published_label' => '2 тижні тому',
            ],
        ];
    }
};
