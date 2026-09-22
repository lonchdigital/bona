<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_reviews', function (Blueprint $table) {
            $table->string('phone', 19)->nullable()->change();
            $table->string('source', 20)->default('website')->after('id');
            $table->string('source_url', 2048)->nullable()->after('source');
            $table->string('external_id', 190)->nullable()->unique()->after('source_url');
            $table->date('reviewed_at')->nullable()->after('published_at');
            $table->json('author_name_translations')->nullable()->after('author_name');
            $table->json('review_translations')->nullable()->after('review');
        });

        if (! Schema::hasTable('home_page_testimonials')) {
            $this->renameReviewSectionKicker(
                ['uk' => 'Google Maps', 'ru' => 'Google Maps'],
                ['uk' => 'Досвід клієнтів', 'ru' => 'Опыт клиентов'],
            );

            return;
        }

        DB::table('home_page_testimonials')
            ->orderBy('id')
            ->each(function (object $testimonial): void {
                $names = $this->translations($testimonial->name ?? null);
                $reviews = $this->translations($testimonial->review ?? null);
                $authorName = $this->preferredTranslation($names);
                $review = $this->preferredTranslation($reviews);

                DB::table('customer_reviews')->insertOrIgnore([
                    'source' => 'google',
                    'source_url' => $testimonial->url ?: null,
                    'external_id' => 'legacy-home-testimonial:'.$testimonial->id,
                    'author_name' => mb_substr($authorName, 0, 121),
                    'author_name_translations' => $names ? json_encode($names, JSON_UNESCAPED_UNICODE) : null,
                    'phone' => null,
                    'email' => null,
                    'rating' => max(1, min(5, (int) ($testimonial->rating ?: 5))),
                    'review' => $review,
                    'review_translations' => $reviews ? json_encode($reviews, JSON_UNESCAPED_UNICODE) : null,
                    'status_id' => 2,
                    'published_at' => $testimonial->date ?: ($testimonial->created_at ?: now()),
                    'reviewed_at' => $testimonial->date ?: null,
                    'locale' => 'uk',
                    'ip_address' => null,
                    'created_at' => $testimonial->created_at ?: now(),
                    'updated_at' => $testimonial->updated_at ?: now(),
                ]);
            });

        Schema::drop('home_page_testimonials');
        $this->renameReviewSectionKicker(
            ['uk' => 'Google Maps', 'ru' => 'Google Maps'],
            ['uk' => 'Досвід клієнтів', 'ru' => 'Опыт клиентов'],
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('home_page_testimonials')) {
            Schema::create('home_page_testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('testimonial_image_path')->default('');
                $table->json('name');
                $table->json('review');
                $table->tinyInteger('rating')->nullable();
                $table->date('date')->nullable();
                $table->string('url')->nullable();
                $table->timestamps();
            });
        }

        DB::table('customer_reviews')
            ->where('external_id', 'like', 'legacy-home-testimonial:%')
            ->orderBy('id')
            ->each(function (object $review): void {
                DB::table('home_page_testimonials')->insert([
                    'testimonial_image_path' => '',
                    'name' => $review->author_name_translations
                        ?: json_encode(['uk' => $review->author_name, 'ru' => $review->author_name], JSON_UNESCAPED_UNICODE),
                    'review' => $review->review_translations
                        ?: json_encode(['uk' => $review->review, 'ru' => $review->review], JSON_UNESCAPED_UNICODE),
                    'rating' => $review->rating,
                    'date' => $review->reviewed_at,
                    'url' => $review->source_url,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
                ]);
            });

        DB::table('customer_reviews')
            ->where('external_id', 'like', 'legacy-home-testimonial:%')
            ->delete();
        DB::table('customer_reviews')->whereNull('phone')->update(['phone' => '']);
        $this->renameReviewSectionKicker(
            ['uk' => 'Досвід клієнтів', 'ru' => 'Опыт клиентов'],
            ['uk' => 'Google Maps', 'ru' => 'Google Maps'],
        );

        Schema::table('customer_reviews', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->dropColumn([
                'source',
                'source_url',
                'external_id',
                'reviewed_at',
                'author_name_translations',
                'review_translations',
            ]);
            $table->string('phone', 19)->nullable(false)->change();
        });
    }

    private function translations(mixed $value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (! is_array($decoded)) {
            $text = trim((string) $value);

            return $text === '' ? [] : ['uk' => $text];
        }

        return collect($decoded)
            ->map(fn (mixed $translation) => trim((string) $translation))
            ->filter()
            ->all();
    }

    private function preferredTranslation(array $translations): string
    {
        return (string) ($translations['uk'] ?? $translations['ru'] ?? collect($translations)->first() ?? '');
    }

    private function renameReviewSectionKicker(array $from, array $to): void
    {
        if (! Schema::hasTable('home_page_configs') || ! Schema::hasColumn('home_page_configs', 'content_sections')) {
            return;
        }

        DB::table('home_page_configs')
            ->select(['id', 'content_sections'])
            ->orderBy('id')
            ->each(function (object $config) use ($from, $to): void {
                $sections = json_decode((string) $config->content_sections, true);

                if (! is_array($sections) || ($sections['reviews']['kicker'] ?? null) !== $from) {
                    return;
                }

                $sections['reviews']['kicker'] = $to;

                DB::table('home_page_configs')
                    ->where('id', $config->id)
                    ->update(['content_sections' => json_encode($sections, JSON_UNESCAPED_UNICODE)]);
            });
    }
};
