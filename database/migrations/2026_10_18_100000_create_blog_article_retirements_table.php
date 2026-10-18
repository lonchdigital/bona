<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One language version taken off the site by Serp Agent. The article
        // itself is kept, so a later publication can bring the version back.
        Schema::create('blog_article_retirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_article_id')->constrained('blog_articles')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('slug');
            // Null answers the old address with 410 instead of a redirect.
            $table->string('redirect_to', 2048)->nullable();
            // A repeated delivery is recognised by its id, even after a restore.
            $table->string('delivery_id')->nullable()->unique();
            $table->timestamp('retired_at');
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();

            $table->index(['blog_article_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_article_retirements');
    }
};
