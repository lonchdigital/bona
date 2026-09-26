<?php

use App\Services\BlogArticle\BlogArticleContentImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(BlogArticleContentImporter::class)->importFile(
            database_path('content/blog/2026_09_26_blog_ru_batch_02.json'),
        );
    }

    public function down(): void
    {
        // Content migrations intentionally do not remove editorial data.
    }
};
