<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_articles', function (Blueprint $table) {
            $table->json('slugs')->nullable()->after('slug');
        });

        Schema::create('blog_article_slug_redirects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_article_id')->constrained('blog_articles')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('slug');
            $table->timestamps();

            $table->unique(['locale', 'slug']);
            $table->index(['blog_article_id', 'locale']);
        });

        DB::table('blog_articles')
            ->orderBy('id')
            ->each(function (object $article): void {
                $names = $this->decodeJsonObject($article->name ?? null);
                $currentSlug = (string) ($article->slug ?? '');
                $localizedSlugs = [];

                // Existing URLs are already indexed. Do not rewrite either
                // storefront during the migration, even where both languages
                // historically shared the same slug. Editors can change one
                // later and the redirect table will preserve the old URL.
                if ($this->hasText($names['uk'] ?? null) && $currentSlug !== '') {
                    $localizedSlugs['uk'] = $currentSlug;
                }

                if ($this->hasText($names['ru'] ?? null) && $currentSlug !== '') {
                    $localizedSlugs['ru'] = $currentSlug;
                }

                DB::table('blog_articles')
                    ->where('id', $article->id)
                    ->update([
                        'slugs' => json_encode(
                            $localizedSlugs,
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
                        ),
                    ]);
            }, 100);
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_article_slug_redirects');

        Schema::table('blog_articles', function (Blueprint $table) {
            $table->dropColumn('slugs');
        });
    }

    /** @return array<string, mixed> */
    private function decodeJsonObject(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function hasText(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
};
